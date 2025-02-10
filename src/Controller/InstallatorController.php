<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InstallateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/installateur/add', name: 'app_installateur_add', methods: ['POST'])]
    public function addInstallateur(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $installateur = new User();
        $installateur->setRoles(['ROLE_USER']); // Par défaut, c'est un utilisateur normal

        $form = $this->createForm(InstallateurType::class, $installateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($installateur, $installateur->getPassword());
            $installateur->setPassword($hashedPassword);

            $entityManager->persist($installateur);
            $entityManager->flush();

            $this->addFlash('success', 'Installateur ajouté avec succès !');
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/index.html.twig', [
            'installateurForm' => $form->createView(),
        ]);
    }
}
