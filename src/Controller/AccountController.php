<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(Security $security): Response
    {

        $user = $security->getUser();

        if(!$user) return $this->redirectToRoute('app_login');

        return $this->render('account/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/account/update', name: 'app_account_update', methods: ['POST'])]
    public function updateProfile(Request $request, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $user->setName($data['name'] ?? $user->getName());
        $user->setLastname($data['lastname'] ?? $user->getLastname());
        $user->setSocietyName($data['societyName'] ?? $user->getSocietyName());
        $user->setPhone($data['phone'] ?? $user->getPhone());
        $user->setEmail($data['email'] ?? $user->getEmail());

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Profil mis à jour avec succès']);
    }
}
