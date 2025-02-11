<?php

namespace App\Controller;

use App\Entity\SimCard;
use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Form\InstallateurType;
use App\Form\SimCardType;
use App\Repository\SimCardRepository;
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
    public function index(Security $security, TeamRepository $teamRepository, UserRepository $userRepository, SimCardRepository $simCardRepository): Response
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


        $teamSims = [];
        foreach ($teams as $team) {
            $teamSims[$team->getId()] = $simCardRepository->count(['team' => $team]);
        }

        return $this->render('equipe/index.html.twig', [
            'teams' => $teams,
            'teamInstallateurs' => $teamInstallateurs,
            'teamSims' => $teamSims,
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

        return $this->render('equipe/index.html.twig', []);
    }

    #[Route('/equipes/add/sims', name: 'app_sim_add', methods: ['POST'])]
    public function addSim(
        Request $request, 
        Security $security, 
        EntityManagerInterface $entityManager
    ): Response {
        $sim = new SimCard();
        $form = $this->createForm(SimCardType::class, $sim);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'utilisateur connecté
            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter une carte SIM.');
            }
    
            // 🔥 Récupération de l'équipe depuis la requête
            $teamId = $request->request->all('sim')['team'] ?? null;
            if ($teamId) {
                $team = $entityManager->getRepository(Team::class)->find($teamId);
                if ($team) {
                    $sim->setTeam($team);
                } else {
                    $this->addFlash('danger', 'Équipe non trouvée.');
                    return $this->redirectToRoute('app_equipes');
                }
            }
    
            $entityManager->persist($sim);
            $entityManager->flush();
    
            $this->addFlash('success', 'Carte SIM ajoutée avec succès !');
    
            return $team 
                ? $this->redirectToRoute('app_equipes_show', ['slug' => $team->getSlug()])
                : $this->redirectToRoute('app_equipes');
        }
    
        return $this->render('equipe/index.html.twig', [
            'simForm' => $form->createView(),
        ]);
    }

    #[Route('/equipe/{slug}', name: 'app_equipes_show', methods: ['GET'])]
    public function show(Team $team, UserRepository $userRepository, SimCardRepository $simCardRepository): Response
    {
        $installateurs = $userRepository->findBy(['team' => $team]);
        $sims = $simCardRepository->findBy(['team' => $team]);

        return $this->render('equipe/show.html.twig', [
            'team' => $team,
            'installateurs' => $installateurs,
            'sims' => $sims,
            'instalatorForm' => $this->createForm(InstallateurType::class)->createView(),
            'simForm' => $this->createForm(SimCardType::class)->createView(),
        ]);
    }

    #[Route('/equipe/{slug}/attribuer-chef', name: 'app_equipes_attribuer_chef', methods: ['POST'])]
    public function attribuerChef(Request $request, Team $team, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $chefId = $request->request->get('chef_id');
        $chef = $userRepository->find($chefId);
    
        if ($chef && $chef->getTeam() === $team) {
            foreach ($userRepository->findBy(['team' => $team]) as $installateur) {
                $installateur->setIschefEffectif(false);
            }
            $chef->setIschefEffectif(true);
            $entityManager->flush();
    
            $this->addFlash('success', 'Chef d\'équipe attribué avec succès.');
        } else {
            $this->addFlash('error', 'Installateur non valide.');
        }
    
        return $this->redirectToRoute('app_equipes_show', ['slug' => $team->getSlug()]);
    }
    
    #[Route('/installateur/{id}/supprimer', name: 'app_installateur_supprimer', methods: ['POST'])]
    public function supprimerInstallateur(User $installateur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $installateur->setTeam(null);
        $entityManager->flush();
    
        $this->addFlash('success', 'Installateur retiré de l\'équipe.');
        return $this->redirectToRoute('app_equipes_show', ['slug' => $installateur->getTeam()->getSlug()]);
    }

    #[Route('/equipe/{slug}/supprimer', name: 'app_equipes_supprimer', methods: ['POST'])]
    public function supprimerEquipe(Team $team, EntityManagerInterface $entityManager, Security $security): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $team->getCreatedBy() !== $security->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($team);
        $entityManager->flush();

        $this->addFlash('success', 'Équipe supprimée avec succès.');
        return $this->redirectToRoute('app_equipes');
    }
}
