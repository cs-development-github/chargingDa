<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\ChargingStationSetting;
use App\Entity\Client;
use App\Entity\Intervention;
use App\Entity\Tarification;
use App\Factory\StationSupervisionFactory;
use App\Form\ClientContractFormType;
use App\Form\ClientFormType;
use App\Form\ClientInterventionFormType;
use App\Form\InterventionFormType;
use App\Service\ClientContractService;
use App\Service\ClientSupervisionDataService;
use App\Service\MailService;
use App\Service\PdfEditorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
    private HttpClientInterface $httpClient;
    private string $universignApiUrl;
    private string $universignApiKey;
    private EntityManagerInterface $em;
    private ClientSupervisionDataService $clientSupervisionDataService;
    private StationSupervisionFactory $stationSupervisionFactory;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        ClientSupervisionDataService $clientSupervisionDataService,
        stationSupervisionFactory $stationSupervisionFactory
    ) {
        $this->httpClient = $httpClient;
        $this->universignApiUrl = $params->get('universign_api_url');
        $this->universignApiKey = $params->get('universign_api_key');
        $this->em = $em;
        $this->clientSupervisionDataService = $clientSupervisionDataService;
        $this->stationSupervisionFactory = $stationSupervisionFactory;
    }

    #[Route('/clients', name: 'app_clients')]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }

        $clients = $em->getRepository(Client::class)->findByUserOrTeam($user);

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

                    $tarification->setFixedFeePublic((float) ($data["fixedFeePublic_$stationId"] ?? 0));
                    $tarification->setRechargeTimePublic((float) ($data["rechargeTimePublic_$stationId"] ?? 0));
                    $tarification->setParkingTimePublic((float) ($data["parkingTimePublic_$stationId"] ?? 0));

                    $tarification->setFixedFeeResale((float) ($data["fixedFeeResale_$stationId"] ?? 0));
                    $tarification->setRechargeTimeResale((float) ($data["rechargeTimeResale_$stationId"] ?? 0));
                    $tarification->setParkingTimeResale((float) ($data["parkingTimeResale_$stationId"] ?? 0));

                    $em->persist($tarification);
                }
            }

            foreach ($chargingStations as $station) {
                $stationId = $station->getId();

                if (isset($data["public_$stationId"]) && isset($data["addressLine_$stationId"])) {
                    $setting = $em->getRepository(ChargingStationSetting::class)->findOneBy(['chargingStation' => $station]);

                    if (!$setting) {
                        $setting = new ChargingStationSetting();
                        $setting->setChargingStation($station);
                        $setting->setClient($client);
                    }

                    $setting->setPublic((bool) $data["public_$stationId"]);
                    $setting->setAddressLine($data["addressLine_$stationId"] ?? '');
                    $setting->setPostalCode($data["postalCode_$stationId"] ?? '');
                    $setting->setCity($data["city_$stationId"] ?? '');
                    $setting->setCountry($data["country_$stationId"] ?? '');

                    $setting->setRegion($data["region_$stationId"] ?? null);
                    $setting->setDepartment($data["department_$stationId"] ?? null);
                    $setting->setLatitude(isset($data["latitude_$stationId"]) ? (float) $data["latitude_$stationId"] : null);
                    $setting->setLongitude(isset($data["longitude_$stationId"]) ? (float) $data["longitude_$stationId"] : null);

                    $em->persist($setting);
                }
            }

            $em->flush();

            if ($this->isClientDataComplete($client)) {
                $contractService->generateAndSendContract($client);

                return $this->redirectToRoute('client_success_page', ['token' => $client->getSecureToken()]);
            }
        }

        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'chargingStations' => $chargingStations,
        ]);
    }

    #[Route('/client/success/{token}', name: 'client_success_page', methods: ['GET'])]
    public function successPage(string $token, EntityManagerInterface $em): Response
    {
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable');
        }

        return $this->render('client/process.html.twig', [
            'client' => $client,
        ]);
    }


    private function isClientDataComplete(Client $client): bool
    {
        return !empty($client->getEmail())
            && !empty($client->getSocietyName())
            && !empty($client->getPhone())
            && !empty($client->getAddress())
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

    #[Route('/sign-contract', name: 'sign_contract', methods: ['GET'])]
    public function signContract(Request $request, EntityManagerInterface $em, MailService $mailerService): Response
    {
        $token = $request->query->get('token');
    
        if (!$token) {
            return new JsonResponse(['error' => 'Token manquant.'], 400);
        }
    
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable.'], 404);
        }
    
        $pdfPath = $this->getParameter('kernel.project_dir') . "/public/pdf/contrat_final_{$client->getId()}.pdf";
    
        if (!file_exists($pdfPath)) {
            return new JsonResponse(['error' => "Le fichier PDF du contrat est introuvable."], 404);
        }
    
        try {
            // 1️⃣ Création de la transaction
            error_log("🛠 Création de la transaction Universign...");
            $transactionResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions", [
                'auth_basic' => [$this->universignApiKey, ''],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['name' => "Signature Contrat - {$client->getName()}", 'language' => 'fr'],
            ]);
    
            $transactionData = $transactionResponse->toArray();
            $transactionId = $transactionData['id'] ?? null;
            error_log("✅ Transaction créée avec ID : " . $transactionId);
    
            if (!$transactionId) {
                throw new \RuntimeException("Échec de la création de la transaction Universign.");
            }
    
            // 🕐 Attente pour s'assurer que la transaction est bien enregistrée
            sleep(3);
    
            // 2️⃣ Envoi du fichier PDF à Universign
            error_log("📄 Envoi du fichier PDF...");
            $fileResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/files", [
                'auth_basic' => [$this->universignApiKey, ''],
                'body' => ['file' => fopen($pdfPath, 'r')],
            ]);
    
            $fileData = $fileResponse->toArray();
            $fileId = $fileData['id'] ?? null;
            error_log("✅ Fichier envoyé avec ID : " . $fileId);
    
            if (!$fileId) {
                throw new \RuntimeException("Échec de l'envoi du fichier à Universign.");
            }
    
            // 3️⃣ Ajout du fichier à la transaction
            error_log("📌 Ajout du document à la transaction...");
            $docResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents", [
                'auth_basic' => [$this->universignApiKey, ''],
                'body' => ['document' => $fileId],
            ]);
    
            $docData = $docResponse->toArray();
            error_log("✅ Document ajouté : " . json_encode($docData));
    
            // Vérification après ajout du document
            sleep(3);
    
            // 4️⃣ Récupération de la transaction pour obtenir le document ID
            error_log("🔎 Vérification de la transaction après ajout du document...");
            $documentResponse = $this->httpClient->request('GET', "{$this->universignApiUrl}/v1/transactions/{$transactionId}", [
                'auth_basic' => [$this->universignApiKey, ''],
            ]);
            $documentData = $documentResponse->toArray();
            error_log("✅ Transaction mise à jour : " . json_encode($documentData));
    
            $documentId = $documentData['documents'][0]['id'] ?? null;
            if (!$documentId) {
                throw new \RuntimeException("Le document ne semble pas avoir été correctement ajouté.");
            }
    
            $signatureResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents/{$documentId}/fields", [
                'auth_basic' => [$this->universignApiKey, ''],
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => http_build_query([
                    'type' => 'signature',
                    'position[page]' => 26,  // ✅ Page 26
                    'position[x]' => 50,     // ✅ Position X (gauche)
                    'position[y]' => 50,    // ✅ Position Y (bas)
                    'position[width]' => 200, // ✅ Largeur
                    'position[height]' => 50  // ✅ Hauteur
                ]),
            ]);

            $signatureData = $signatureResponse->toArray();
            error_log("✅ Champ de signature ajouté : " . json_encode($signatureData));

            $signatureFieldId = $signatureData['id'] ?? null;
            if (!$signatureFieldId) {
                throw new \RuntimeException("Échec de l'ajout du champ de signature.");
            }

    
            // 6️⃣ Ajout du signataire
            error_log("👤 Ajout du signataire ({$client->getEmail()})...");
            $signerResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/signatures", [
                'auth_basic' => [$this->universignApiKey, ''],
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => http_build_query([
                    'signer' => $client->getEmail(),
                    'field'  => $signatureFieldId, // ✅ Lien avec le champ signature
                ]),
            ]);
    
            $signerData = $signerResponse->toArray();
            error_log("✅ Signataire ajouté : " . json_encode($signerData));
    
            // 7️⃣ Démarrer la transaction
            error_log("🚀 Démarrage de la transaction...");
            $startResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/start", [
                'auth_basic' => [$this->universignApiKey, ''],
            ]);
            error_log("✅ Transaction démarrée.");
    
            // 8️⃣ Récupération du lien de signature
            error_log("🔎 Récupération de l'URL de signature...");
            sleep(3);
            $finalTransactionResponse = $this->httpClient->request('GET', "{$this->universignApiUrl}/v1/transactions/{$transactionId}", [
                'auth_basic' => [$this->universignApiKey, ''],
            ]);
            $finalTransactionData = $finalTransactionResponse->toArray();
            error_log("✅ Transaction finale récupérée.");
    
            // Extraction de l'URL de signature
            $signatureUrl = $finalTransactionData['actions'][0]['url'] ?? null;
    
            if (!$signatureUrl) {
                throw new \RuntimeException("Impossible de récupérer l'URL de signature.");
            }
            error_log("🔗 Lien de signature récupéré : " . $signatureUrl);
    
            // 📧 Envoi de l'e-mail avec le lien de signature
            error_log("📧 Envoi de l'email au client...");
            $mailerService->sendEmail(
                to: $client->getEmail(),
                subject: "Signature électronique de votre contrat",
                template: 'emails/sign_contract.html.twig',
                context: [
                    'client' => $client,
                    'signatureUrl' => $signatureUrl
                ]
            );
            error_log("✅ Email envoyé avec succès !");
    
            // 🔗 Sauvegarde en base de données
            $client->setSignatureTransactionId($transactionId);
            $em->persist($client);
            $em->flush();
    
        return $this->redirectToRoute('thank_you', ['token' => $token]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur Universign',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
