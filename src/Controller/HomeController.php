<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('ROLE_USER')] // S'assure que l'utilisateur est connecté
    public function index(InterventionRepository $interventionRepository): Response
    {
        $user = $this->getUser();

        // Récupérer la prochaine intervention de l'utilisateur
        $nextIntervention = $interventionRepository->findNextInterventionByUser($user);

        return $this->render('home/index.html.twig', [
            'nextIntervention' => $nextIntervention,
        ]);
    }
}