<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Form\InstallateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

final class EquipeController extends AbstractController
{
    #[Route('/equipe', name: 'app_equipes')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teamForm = $this->createForm(TeamType::class);
        $instalatorForm = $this->createForm(InstallateurType::class);

        return $this->render('equipe/index.html.twig', [
            'teamForm' => $teamForm->createView(),
            'instalatorForm' => $instalatorForm->createView(),
        ]);
    }

    /**
     * @Route("/equipes/add", name="app_equipes_add", methods={"POST"})
     */
    public function addEquipe(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un installateur.');
            }

            $team->setCreatedBy($user);

            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('success', 'Équipe ajoutée avec succès !');
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/index.html.twig', [
            'teamForm' => $form->createView(),
        ]);
    }
}
