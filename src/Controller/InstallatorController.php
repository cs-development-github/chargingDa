<?php

namespace App\Controller;

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
    public function addInstallateur(Request $request, UserPasswordHasherInterface $passwordHasher,Security $security, EntityManagerInterface $entityManager): Response
    {
        $installateur = new User();
        $form = $this->createForm(InstallateurType::class, $installateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($installateur, $installateur->getPassword());
            $installateur->setPassword($hashedPassword);
            $installateur->setRoles(['ROLE_USER']); // L'installateur est par défaut un utilisateur normal
            $installateur->setIsActive(true);
    
            // Récupère l'utilisateur connecté
            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un installateur.');
            }
    
            // Assigne l'utilisateur connecté comme créateur
            $installateur->setCreatedBy($user);
    
            $entityManager->persist($installateur);
            $entityManager->flush();
    
            $this->addFlash('success', 'Installateur ajouté avec succès !');
            return $this->redirectToRoute('app_equipes'); // Retour à la liste des équipes
        }
    
        return $this->render('equipe/index.html.twig', [
            'teamForm' => $this->createForm(TeamType::class)->createView(),
            'instalatorForm' => $form->createView(),
        ]);
    }
    
}
