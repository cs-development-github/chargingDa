<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientFormType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientsController',
        ]);
    }

    #[Route('/ajouter/client', name: 'app_add_client')]
    public function addClient(Request $request, EntityManagerInterface $entityManager, MailService $mailerService): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientFormType::class, $client);
        $form->add('submit', SubmitType::class, [
            'label' => 'JE TRANSMET LE CONTRAT',
            'attr' => ['class' => 'confirm-btn']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            // Vérifier si tous les champs sont remplis
            $isComplete = $this->checkAllFieldsFilled($client);

            // Définition du template email
            $template = $isComplete ? 'emails/full_pdf.html.twig' : 'emails/request_document.html.twig';

            // Envoi de l'e-mail
            $mailerService->sendEmail(
                to: 'admin@tondomaine.com', // Remplace par l'adresse de destination
                subject: $isComplete ? 'Contrat client validé' : 'Demande de documents manquants',
                template: $template,
                context: ['client' => $client]
            );

            $this->addFlash('success', 'Le client a été ajouté avec succès.');

            return $this->redirectToRoute('app_add_client');
        }

        return $this->render('client/add.client.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function checkAllFieldsFilled(Client $client): bool
    {
        return !empty($client->getEmail()) && !empty($client->getSocietyName());
    }
}
