<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\ChargingStationSetting;
use App\Entity\Client;
use App\Entity\Intervention;
use App\Entity\Tarification;
use App\Form\ClientContractFormType;
use App\Form\ClientFormType;
use App\Form\ClientInterventionFormType;
use App\Form\InterventionFormType;
use App\Service\ClientContractService;
use App\Service\MailService;
use App\Service\PdfEditorService;
use App\Service\UniversignService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }

        $clients = $em->getRepository(Client::class)->findByUserOrTeam($user);

        // dd($clients);

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/ajouter/client', name: 'app_add_client')]
    public function addClient(
        Request $request,
        EntityManagerInterface $entityManager,
        ClientContractService $contractService,
        MailService $mailerService,
        UrlGeneratorInterface $urlGenerator,
        Security $security
    ): Response {
        $client = new Client();
        $intervention = new Intervention();

        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        $form = $this->createForm(ClientInterventionFormType::class, [
            'client' => $client,
            'intervention' => $intervention,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'installateur
            $intervention->setInstallator($user);
            $client->setCreatedBy($user);

            $entityManager->persist($client);
            $entityManager->persist($intervention);
            $entityManager->flush();

            $token = Uuid::v4()->toRfc4122();
            $client->setSecureToken($token);
            $entityManager->flush();

            $completionUrl = $urlGenerator->generate('client_complete_info', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $mailerService->sendEmail(
                to: $client->getEmail() ?: 'chris.vermersch@hotmail.com',
                subject: 'Demande d\'information complémentaire contrat de supervision',
                template: 'emails/request_document.html.twig',
                context: ['client' => $client, 'completionUrl' => $completionUrl]
            );

            $mailerService->sendEmail(
                to: 'contact@lodmi.com',
                subject: 'Demande de Supervision',
                template: 'emails/lodmi_contract.html.twig',
                context: ['client' => $client, 'completionUrl' => $completionUrl]
            );

            $mailerService->sendEmail(
                to: 'chris.vermersch@hotmail.com',
                subject: 'Demande de Supervision',
                template: 'emails/confirmation_installator.html.twig',
                context: ['client' => $client, 'completionUrl' => $completionUrl]
            );


            $this->addFlash('info', 'Le client a été ajouté, mais certaines informations sont manquantes.');

            return $this->redirectToRoute('app_add_client');
        }

        return $this->render('client/add.client.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/client/complete-info', name: 'client_complete_info')]
    public function completeClientInfo(Request $request, EntityManagerInterface $em, ClientContractService $contractService): Response
    {
        $token = $request->query->get('token');

        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Lien invalide ou client introuvable.');
        }

        $interventions = $em->getRepository(Intervention::class)->findBy(['Client' => $client]);

        $chargingStations = [];
        foreach ($interventions as $intervention) {
            $chargingStations[] = $intervention->getChargingStation();
        }

        $form = $this->createForm(ClientContractFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();

            $freeBadges = isset($data['freeBadges']) ? (int) $data['freeBadges'] : 0;

            $badge = $em->getRepository(Badge::class)->findOneBy(['client' => $client]);

            if (!$badge) {
                $badge = new Badge();
                $badge->setClient($client);
            }

            $badge->setNumber($freeBadges);
            $em->persist($badge);

            foreach ($chargingStations as $station) {
                $stationId = $station->getId();
                if (isset($data["priceKwh_$stationId"]) && isset($data["priceResale_$stationId"]) && isset($data["pricePublic_$stationId"])) {
                    $tarification = $em->getRepository(Tarification::class)->findOneBy(['chargingStation' => $station]);

                    if (!$tarification) {
                        $tarification = new Tarification();
                        $tarification->setChargingStation($station);
                    }

                    $tarification->setClient($client);
                    $tarification->setPurcharsePrice((float) $data["priceKwh_$stationId"]);
                    $tarification->setPublicPrice((float) $data["pricePublic_$stationId"]);
                    $tarification->setResalePrice((float) $data["priceResale_$stationId"]);
                    $tarification->setReducedPrice((float) $data["priceKwh_$stationId"]);

                    $em->persist($tarification);
                }
            }

            foreach ($chargingStations as $station) {
                $stationId = $station->getId();
                if (isset($data["public_$stationId"]) && isset($data["adress_$stationId"])) {
                    $setting = $em->getRepository(ChargingStationSetting::class)->findOneBy(['chargingStation' => $station]);

                    if (!$setting) {
                        $setting = new ChargingStationSetting();
                        $setting->setChargingStation($station);
                        $setting->setClient($client);
                    }

                    $setting->setPublic((bool) $data["public_$stationId"]);
                    $setting->setAdress($data["adress_$stationId"]);
                    $setting->setInstalledAt(new \DateTime($data["installedAt_$stationId"] ?? 'now'));
                    $setting->setSupervisedAt(new \DateTime($data["supervisedAt_$stationId"] ?? 'now'));

                    $em->persist($setting);
                }
            }
            $em->flush();

            if ($this->isClientDataComplete($client)) $contractService->generateAndSendContract($client);

            return $this->redirectToRoute('thank_you', ['token' => $client->getSecureToken()]);
        }

        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'chargingStations' => $chargingStations,
        ]);
    }

    /**
     * Vérifie si tous les champs obligatoires sont remplis.
     */
    private function isClientDataComplete(Client $client): bool
    {
        return !empty($client->getEmail())
            && !empty($client->getSocietyName())
            && !empty($client->getPhone())
            && !empty($client->getAdress())
            && !empty($client->getName())
            && !empty($client->getLastname())
            && !empty($client->getSiret())
            && !empty($client->getCodeNaf())
            && !empty($client->getLegalForm());
    }

    #[Route('/thank-you', name: 'thank_you')]
    public function thankYou(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->query->get('token');

        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        return $this->render('client/thank_you.html.twig', [
            'token' => $token,
            'client' => $client
        ]);
    }

    #[Route('/send-otp', name: 'send_otp', methods: ['POST'])]
    public function sendOtp(Request $request, EntityManagerInterface $em, HttpClientInterface $httpClient): JsonResponse
    {
        $token = $request->request->get('token');

        if (!$token) {
            return new JsonResponse(['error' => 'Token manquant.'], 400);
        }

        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable.'], 404);
        }

        $otpCode = random_int(100000, 999999);
        $client->setOtpCode((string) $otpCode);
        $client->setOtpExpiresAt(new \DateTime('+10 minutes'));
        $em->persist($client);
        $em->flush();

        try {
            $response = $httpClient->request('POST', 'https://srv.mobi-gest.com:4443/api/send-otp', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'phone' => $client->getPhone(),
                    'otpCode' => $otpCode,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return new JsonResponse(['error' => 'Erreur lors de l\'envoi du SMS.'], 500);
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Service SMS indisponible.', 'details' => $e->getMessage()], 500);
        }
    }

    #[Route('/verify-otp', name: 'verify_otp', methods: ['POST'])]
    public function verifyOtp(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->request->get('token');
        $otp = $request->request->get('otp');
    
        if (!$token || !$otp) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }
    
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable.'], 404);
        }
    
        if (!$client->isOtpValid($otp)) {
            return new JsonResponse(['error' => 'Code invalide ou expiré.'], 403);
        }
    
        $client->setIsOtpVerified(true);
        $client->setOtpCode(null);
        $client->setOtpExpiresAt(null);
        $em->persist($client);
        $em->flush();
    
        return new JsonResponse(['success' => true, 'message' => 'OTP validé avec succès.']);
    }
    
    #[Route('/sign-contract', name: 'sign_contract')]
    public function signContract(Request $request, EntityManagerInterface $em, UniversignService $universignService): Response
    {
        $token = $request->query->get('token');
    
        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }
    
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client || !$client->getSignatureTransactionId()) {
            throw $this->createNotFoundException('Aucune demande de signature trouvée.');
        }
    
        $signUrl = "https://sign.universign.com/sign?id=" . $client->getSignatureTransactionId();
    
        return $this->redirect($signUrl);
    }
    

}
