<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LandingPageController extends AbstractController
{
    #[Route('/', name: 'app_landing_page')]
    public function index(): Response
    {
        return $this->render('landing_page/index.html.twig', [
            'controller_name' => 'LandingPageController',
        ]);
    }

    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('landing_page/cgu.html.twig', [
        ]);
    }
    #[Route('/aide', name: 'app_aide')]
    public function aide(): Response
    {
        return $this->render('landing_page/aide.html.twig', []);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('landing_page/contact.html.twig', []);
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('landing_page/faq.html.twig', []);
    }


    #[Route('/propos', name: 'app_propos')]
    public function propos(): Response
    {
        return $this->render('landing_page/propos.html.twig', []);
    }



}
