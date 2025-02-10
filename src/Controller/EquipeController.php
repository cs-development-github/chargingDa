<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\InstallateurType;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EquipeController extends AbstractController
{
    #[Route('/equipe', name: 'app_equipes')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teamForm = $this->createForm(TeamType::class);
        $installateurForm = $this->createForm(InstallateurType::class);

        return $this->render('equipe/index.html.twig', [
            'teamForm' => $teamForm->createView(), // Assure-toi que cette ligne est bien présente
            'installateurForm' => $installateurForm->createView(),
        ]);
    }

    /**
     * @Route("/equipes/add", name="app_equipes_add", methods={"POST"})
     */
    public function addEquipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Team();
        $form = $this->createForm(TeamType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            $this->addFlash('success', 'Équipe ajoutée avec succès !');
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/index.html.twig', [
            'teamForm' => $form->createView(),
        ]);
    }
}
