<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Form\ClientFormType;
use App\Form\InterventionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
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
    public function submitForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        dump("🚀 Données reçues :", $request->request->all());
    
        $client = new Client();
        $clientForm = $this->createForm(ClientFormType::class, $client);
        $interventionForm = $this->createForm(InterventionFormType::class);
    
        $clientForm->handleRequest($request);
        $interventionForm->handleRequest($request);
    
        dump("🔍 Client Form soumis ?", $clientForm->isSubmitted()); 
        dump("🔍 Client Form valide ?", $clientForm->isValid());
        dump("📌 Erreurs Client Form :", $clientForm->getErrors(true, false));
    
        dump("🔍 Intervention Form soumis ?", $interventionForm->isSubmitted());
        dump("🔍 Intervention Form valide ?", $interventionForm->isValid());
        dump("📌 Erreurs Intervention Form :", $interventionForm->getErrors(true, false));
    
        if ($clientForm->isSubmitted() && $clientForm->isValid() &&
            $interventionForm->isSubmitted() && $interventionForm->isValid()) {
    
            // 🔹 Sauvegarde du client en base
            $entityManager->persist($client);
            $entityManager->flush(); 
    
            // 🔹 Récupération de l'utilisateur connecté
            $user = $this->getUser();
            if (!$user) {
                return new Response("Utilisateur non authentifié", Response::HTTP_UNAUTHORIZED);
            }
    
            // 🔹 Associer les interventions au client et utilisateur
            foreach ($interventionForm->get('interventions')->getData() as $intervention) {
                $intervention->setClient($client);
                $intervention->setInstallator($user);
                $entityManager->persist($intervention);
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Client et interventions enregistrés avec succès.');
            return $this->redirectToRoute('app_home');
        }
    
        return new Response('❌ Formulaire invalide - Vérifie les erreurs', Response::HTTP_BAD_REQUEST);
    }
}
