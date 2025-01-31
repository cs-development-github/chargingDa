<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChargingStationsController extends AbstractController
{
    #[Route('/charging/stations', name: 'app_charging_stations')]
    public function index(): Response
    {
        return $this->render('charging_stations/index.html.twig', [
            'controller_name' => 'ChargingStationsController',
        ]);
    }
}
