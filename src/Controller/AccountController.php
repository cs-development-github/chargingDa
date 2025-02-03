<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
}
