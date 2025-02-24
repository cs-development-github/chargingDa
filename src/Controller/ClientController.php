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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

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
            // Mise à jour des prix des bornes et association avec Tarification
            foreach ($chargingStations as $station) {
                $stationId = $station->getId();
                if (isset($data["priceKwh_$stationId"]) && isset($data["priceResale_$stationId"])) {
                    $tarification = $em->getRepository(Tarification::class)->findOneBy(['chargingStation' => $station]);
    
                    if (!$tarification) {
                        $tarification = new Tarification();
                        $tarification->setChargingStation($station);
                    }
    
                    $tarification->setClient($client);
                    $tarification->setPurcharsePrice((float) $data["priceKwh_$stationId"]);
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
    
            // $client->setSecureToken(null);
            $em->flush();

            if ($this->isClientDataComplete($client)) $contractService->generateAndSendContract($client);
    
            return $this->redirectToRoute('thank_you');
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
        public function tankYou(): Response
        {
            return $this->render('client/thank_you.html.twig');
        }

}
