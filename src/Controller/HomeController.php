<?php

namespace App\Controller;


use App\Entity\Client;
use App\Entity\ChargingStations;
use App\Entity\Intervention;
use App\Form\ClientFormType;
use Symfony\Component\Uid\Uuid;
use App\Service\ClientMailService;
use App\Form\InterventionFormType;
use App\Repository\ChargingStationsRepository;
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

    #[Route('/tableau-de-bord', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        if ($user instanceof \App\Entity\User && $user->isVerified() && !$user->getEmailVerifiedAt()) {
            $this->clientMailService->sendInstallerWelcomeEmail($user); // méthode à créer
            $user->setEmailVerifiedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }
    
        $interventions = $entityManager->getRepository(Intervention::class)->findBy(['installator' => $user], ['id' => 'DESC']);
    
$clientsData = [];

foreach ($interventions as $intervention) {
    if ($intervention->isDeleted()) {
        continue; // skip les interventions supprimées
    }

    $client = $intervention->getClient();

    if (!$client) {
        continue;
    }

    $clientId = $client->getId();

    if (!isset($clientsData[$clientId])) {
        $clientsData[$clientId] = [
            'email' => $client->getEmail(),
            'societyName' => $client->getSocietyName(),
            'stations' => [],
            'interventionIds' => [],
        ];
    }

    if ($intervention->getChargingStation()) {
        $clientsData[$clientId]['stations'][] = [
            'station' => $intervention->getChargingStation(),
            'borneName' => $intervention->getBorneName(),
            'interventionId' => $intervention->getId(),
        ];
    }

    $clientsData[$clientId]['interventionIds'][] = $intervention->getId();
}

    
        $clientForm = $this->createForm(ClientFormType::class);
        $interventionForm = $this->createForm(InterventionFormType::class);
        return $this->render('home/index.html.twig', [
            'clientsData' => $clientsData,
            'clientForm' => $clientForm->createView(),
            'interventionForm' => $interventionForm->createView(),
        ]);
    }

    #[Route('/tableau-de-bord/ajout-client', name: 'dashboard_add_client')]
    public function addClient(
        Request $request,
        EntityManagerInterface $entityManager,
        ChargingStationsRepository $chargingStationsRepository
    ): Response {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $client = new Client();
        $clientForm = $this->createForm(ClientFormType::class, $client);
        $clientForm->handleRequest($request);
    
        $interventionForm = $this->createForm(InterventionFormType::class);
        $interventionForm->handleRequest($request);
    
        $chargingStationModels = $chargingStationsRepository->findAll();
    
        return $this->render('home/add_client.html.twig', [
            'clientForm' => $clientForm->createView(),
            'interventionForm' => $interventionForm->createView(),
            'chargingStationModels' => $chargingStationModels,
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

            $randomNumber = random_int(1000, 9999);
            $documentId = '2025SUP' . $randomNumber;
            $client->setDocumentId($documentId);

            $entityManager->persist($client);
            $entityManager->flush();

            foreach ($interventionForm->get('interventions')->getData() as $i => $intervention) {
                $intervention->setClient($client);
                $intervention->setInstallator($user);
                $reference = $this->generateInterventionReference($user->getName(), $i + 1);
                $intervention->setReference($reference);
                $entityManager->persist($intervention);
            }

            $entityManager->flush();

            $completionUrl = $urlGenerator->generate('supervision_step', [
                'step' => 1,
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

    private function generateInterventionReference(string $clientName, int $index = 1): string
    {
        $prefix = 'int-sup';
        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/u', '', $clientName), 0, 3));
        $date = (new \DateTime())->format('dmy');
        $iteration = str_pad((string)$index, 4, '0', STR_PAD_LEFT);

        return sprintf('%s-%s-%s-%s', $prefix, $initials, $date, $iteration);
    }

    #[Route('/ph', name: 'ph_machine')]
    public function ph(): Response
    {
        return $this->render('home/ph.html.twig');
    }
}