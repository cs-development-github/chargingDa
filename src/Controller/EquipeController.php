<?php

namespace App\Controller;

use App\Entity\SimCard;
use App\Entity\Team;
use App\Form\TeamType;
use App\Form\InstallateurType;
use App\Form\SimCardType;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

final class EquipeController extends AbstractController
{
    #[Route('/equipe', name: 'app_equipes')]
    public function index(Security $security, TeamRepository $teamRepository, UserRepository $userRepository): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
    
        $teams = $teamRepository->findBy(['createdBy' => $user]);
    
        $teamInstallateurs = [];
        foreach ($teams as $team) {
            $teamInstallateurs[$team->getId()] = $userRepository->findBy(['team' => $team]);
        }
    
        return $this->render('equipe/index.html.twig', [
            'teams' => $teams,
            'teamInstallateurs' => $teamInstallateurs,
            'totalTeams' => count($teams),
            'teamForm' => $this->createForm(TeamType::class)->createView(),
            'instalatorForm' => $this->createForm(InstallateurType::class)->createView(),
            'simForm' => $this->createForm(SimCardType::class)->createView(),
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
        ]);
    }

    /**
     * @Route("/equipes/add/sims", name="app_sim_add", methods={"POST"})
     */
    public function addSim(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $sim = new SimCard();
        $form = $this->createForm(SimCardType::class, $sim);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sim);
            $entityManager->flush();

            $this->addFlash('success', 'Sim ajoutée avec succès !');
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/index.html.twig', [
        ]);
    }
}
