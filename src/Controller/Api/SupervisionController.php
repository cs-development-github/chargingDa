<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Factory\StationSupervisionFactory;
use App\Service\ClientSupervisionDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SupervisionController extends AbstractController
{
    #[Route('/api/supervise/{token}', name: 'api_supervise_client', methods: ['POST'])]
    public function superviseClient(
        string $token,
        EntityManagerInterface $em,
        StationSupervisionFactory $factory,
        ClientSupervisionDataService $supervisionService
    ): JsonResponse {
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            return $this->json(['error' => 'Client introuvable'], 404);
        }

        $interventions = $em->getRepository(Intervention::class)->findBy(['client' => $client]);
        $dtos = $factory->createFromInterventions($interventions);

        $steps = $supervisionService->superviseClientStations($client, $dtos);

        return $this->json($steps);
    }
}
