<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Form\InstallateurType;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class InstallatorController extends AbstractController
{
    #[Route('/installator', name: 'app_installator')]
    public function index(): Response
    {
        return $this->render('installator/index.html.twig', [
            'controller_name' => 'InstallatorController',
        ]);
    }

    #[Route('/equipe/installateur/add', name: 'app_installateur_add', methods: ['POST'])]
    public function addInstallateur(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        Security $security, 
        EntityManagerInterface $entityManager
    ): Response {
        $installateur = new User();
        $form = $this->createForm(InstallateurType::class, $installateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($installateur, $installateur->getPassword());
            $installateur->setPassword($hashedPassword);
            $installateur->setRoles(['ROLE_USER']);
            $installateur->setIsActive(true);
    
            // Vérification de l'utilisateur connecté
            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un installateur.');
            }
            $installateur->setCreatedBy($user);
    
            $teamId = $request->request->all('installator')['team'] ?? null;
            if ($teamId) {
                $team = $entityManager->getRepository(Team::class)->find($teamId);
                if ($team) {
                    $installateur->setTeam($team);
                } else {
                    $this->addFlash('danger', 'Équipe non trouvée.');
                    return $this->redirectToRoute('app_equipes');
                }
            }
    
            $entityManager->persist($installateur);
            $entityManager->flush();
    
            $this->addFlash('success', 'Installateur ajouté avec succès !');
    
            return $team 
                ? $this->redirectToRoute('app_equipes_show', ['slug' => $team->getSlug()])
                : $this->redirectToRoute('app_equipes');
        }
    
        return $this->render('equipe/index.html.twig', [
            'teamForm' => $this->createForm(TeamType::class)->createView(),
            'instalatorForm' => $form->createView(),
        ]);
    }
}
