<?php

namespace App\Controller;


use App\Entity\Client;
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

class HomeController extends AbstractController
{
    public function __construct(private ClientMailService $clientMailService) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $clientForm = $this->createForm(ClientFormType::class);
        $interventionForm = $this->createForm(InterventionFormType::class);
    
        return $this->render('home/index.html.twig', [
            'clientForm' => $clientForm->createView(),
            'interventionForm' => $interventionForm->createView(),
        ]);
    }
    
    #[Route('/submit-form', name: 'app_submit_form', methods: ['POST'])]
    public function submitForm(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $client = new Client();
        $clientForm = $this->createForm(ClientFormType::class, $client);
        $interventionForm = $this->createForm(InterventionFormType::class);
    
        $clientForm->handleRequest($request);
        $interventionForm->handleRequest($request);
    
        if ($clientForm->isSubmitted() && $clientForm->isValid() &&
            $interventionForm->isSubmitted() && $interventionForm->isValid()) {
    
            $user = $this->getUser();
    
            if (!$user) {
                return new Response("Utilisateur non authentifié", Response::HTTP_UNAUTHORIZED);
            }
    
            // 🔹 Associer l'utilisateur créateur du client
            $client->setCreatedBy($user);
    
            // 🔹 Générer un token UUID unique
            $token = Uuid::v4()->toRfc4122();
            $client->setSecureToken($token); // ✅ Enregistrement du token
    
            // 🔹 Sauvegarde du client en base
            $entityManager->persist($client);
            $entityManager->flush(); 
    
            // 🔹 Associer les interventions au client et utilisateur
            foreach ($interventionForm->get('interventions')->getData() as $intervention) {
                $intervention->setClient($client);
                $intervention->setInstallator($user);
                $entityManager->persist($intervention);
            }
    
            $entityManager->flush();
    
            // 🔹 Générer l'URL avec le token
            $completionUrl = $urlGenerator->generate('client_complete_info', [
                'token' => $token,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
    
            // 🔹 Envoi des emails avec le token dans l'URL
            $this->clientMailService->sendClientCompletionEmail($client, $completionUrl);
            $this->clientMailService->sendSupportNotification($client, $completionUrl);
            $this->clientMailService->sendInstallerConfirmation($client, $completionUrl);
    
            $this->addFlash('success', 'Client et interventions enregistrés avec succès.');
            return $this->redirectToRoute('app_home');
        }
    
        return new Response('❌ Formulaire invalide - Vérifie les erreurs', Response::HTTP_BAD_REQUEST);
    }
}
