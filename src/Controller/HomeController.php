<?php

namespace App\Controller;


use App\Entity\Client;
use App\Entity\ChargingStations;
use App\Entity\Intervention;
use App\Form\ClientFormType;
use Symfony\Component\Uid\Uuid;
use App\Service\ClientMailService;
use App\Form\InterventionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends AbstractController
{
    public function __construct(private ClientMailService $clientMailService)
    {
    }

    #[Route('/dashboard', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $interventions = $entityManager->getRepository(Intervention::class)->findBy(['installator' => $user], ['id' => 'DESC']);
    
        $clientsData = [];
    
        foreach ($interventions as $intervention) {
            $client = $intervention->getClient();
    
            if ($client) {
                $clientId = $client->getId();
                if (!isset($clientsData[$clientId])) {
                    $clientsData[$clientId] = [
                        'email' => $client->getEmail(),
                        'societyName' => $client->getSocietyName(),
                        'stations' => []
                    ];
                }
                if ($intervention->getChargingStation()) {
                    $clientsData[$clientId]['stations'][] = [
                        'station' => $intervention->getChargingStation(),
                        'borneName' => $intervention->getBorneName(),
                    ];
                }
            }
        }
    
        $clientForm = $this->createForm(ClientFormType::class);
        $interventionForm = $this->createForm(InterventionFormType::class);
    
        return $this->render('home/index.html.twig', [
            'clientsData' => $clientsData,
            'clientForm' => $clientForm->createView(),
            'interventionForm' => $interventionForm->createView(),
        ]);
    }
    

    #[Route('/supervision/{id}', name: 'start_supervision')]
    public function startSupervision($id, EntityManagerInterface $entityManager): Response
    {

        $station = $entityManager->getRepository(ChargingStations::class)->find($id);

        if (!$station) {
            throw $this->createNotFoundException('Borne non trouvée.');
        }

        $this->addFlash('success', 'Supervision démarrée pour ' . $station->getModel());

        return $this->redirectToRoute('app_home');
    }


    #[Route('/submit-form', name: 'app_submit_form', methods: ['POST'])]
    public function submitForm(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $client = new Client();
        $clientForm = $this->createForm(ClientFormType::class, $client);
        $interventionForm = $this->createForm(InterventionFormType::class);

        $clientForm->handleRequest($request);
        $interventionForm->handleRequest($request);

        if (
            $clientForm->isSubmitted() && $clientForm->isValid() &&
            $interventionForm->isSubmitted() && $interventionForm->isValid()
        ) {

            $user = $this->getUser();

            if (!$user) {
                return new Response("Utilisateur non authentifié", Response::HTTP_UNAUTHORIZED);
            }

            $client->setCreatedBy($user);

            $token = Uuid::v4()->toRfc4122();
            $client->setSecureToken($token);

            $entityManager->persist($client);
            $entityManager->flush();

            foreach ($interventionForm->get('interventions')->getData() as $intervention) {
                $intervention->setClient($client);
                $intervention->setInstallator($user);
                $entityManager->persist($intervention);
            }

            $entityManager->flush();

            $completionUrl = $urlGenerator->generate('client_complete_info', [
                'token' => $token,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->clientMailService->sendClientCompletionEmail($client, $completionUrl);
            $this->clientMailService->sendSupportNotification($client, $completionUrl);
            $this->clientMailService->sendInstallerConfirmation($client, $completionUrl);

            $this->addFlash('success', 'Client et interventions enregistrés avec succès.');
            return $this->redirectToRoute('app_home');
        }

        return new Response('❌ Formulaire invalide - Vérifie les erreurs', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/charging/stations/{id}/docs', name: 'station_docs_json', methods: ['GET'])]
    public function getStationDocs(ChargingStations $station): JsonResponse
    {
        $docs = $station->getChargingStationDocumentations()->toArray();

        $data = array_map(function ($doc) {
            return [
                'image' => '/uploads/Documentations/' . $doc->getImage(),
                'ocpp' => $doc->getOcpp(),
                'napn' => $doc->getNapn(),
            ];
        }, $docs);

        return $this->json($data);
    }

}