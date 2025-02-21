<?php
namespace App\Controller;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Form\ClientFormType;
use App\Form\InterventionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $interventionForm = $this->createForm(InterventionFormType::class);
        $clientForm = $this->createForm(ClientFormType::class, $client);

        $clientForm->handleRequest($request);
        $interventionForm->handleRequest($request);

        if ($clientForm->isSubmitted() && $clientForm->isValid() && 
            $interventionForm->isSubmitted() && $interventionForm->isValid()) {

            $entityManager->persist($client);
            foreach ($interventionForm->get('interventions')->getData() as $intervention) {
                $intervention->setClient($client);
                $entityManager->persist($intervention);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'clientForm' => $clientForm->createView(),
            'interventionForm' => $interventionForm->createView(),
        ]);
    }
}
