<?php

namespace App\Controller;

use App\Entity\Intervention;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class InterventionController extends AbstractController
{
    #[Route('/tableau-de-bord/intervention', name: 'app_intervention')]
    public function index(Request $request, InterventionRepository $repo, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $borneName = $request->query->get('borneName');
        $clientName = $request->query->get('clientName');
        $reference = $request->query->get('reference');
        $interventions = $repo->findAllForGrouping($borneName, $clientName, $reference);
        $grouped = [];

        foreach ($interventions as $intervention) {
            $prefix = substr($intervention->getReference(), 0, 24);
            if (!isset($grouped[$prefix])) {
                $grouped[$prefix] = [
                    'refPrefix' => $prefix,
                    'nbBornes' => 0,
                    'anyInterventionId' => $intervention->getId(),
                    'intervention' => $intervention,
                ];
            }
            $grouped[$prefix]['nbBornes']++;
        }

        $pagination = $paginator->paginate(array_values($grouped), $request->query->getInt('page', 1), 10);


        return $this->render('intervention/index.html.twig', [
            'pagination' => $pagination,
            'filters' => [
                'borneName' => $borneName,
                'clientName' => $clientName,
                'reference' => $reference,
            ],
        ]);
    }

    #[Route('/tableau-de-bord/intervention/{id}', name: 'intervention_client_show', methods: ['GET'])]
    public function show(int $id, InterventionRepository $repo): Response
    {
        $intervention = $repo->find($id);

        if (!$intervention) {
            throw $this->createNotFoundException('Intervention introuvable.');
        }

        $groupPrefix = substr($intervention->getReference(), 0, 24);
        $groupInterventions = $repo->findByGroupPrefix($groupPrefix);

        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
            'groupInterventions' => $groupInterventions,
        ]);
    }

    #[Route('/tableau-de-bord/intervention/{id}', name: 'intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $intervention->getId(), $request->request->get('_token'))) {
            $em->remove($intervention);
            $em->flush();

            $this->addFlash('success', 'Intervention supprimée avec succès.');
        }

        return $this->redirectToRoute('app_intervention');
    }

    #[Route('/intervention/delete-multiple', name: 'intervention_soft_delete_multiple', methods: ['POST'])]
    public function softDeleteMultiple(Request $request, EntityManagerInterface $em): Response
    {
        $ids = explode(',', $request->request->get('ids', ''));
        
        $repo = $em->getRepository(Intervention::class);
        foreach ($ids as $id) {
            $intervention = $repo->find($id);
            if ($intervention) {
                $intervention->setIsDeleted(true);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Interventions supprimées avec succès.');
        return $this->redirectToRoute('app_home');
    }

}
