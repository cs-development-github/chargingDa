<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientFormType;
use App\Service\ClientContractService;
use App\Service\MailService;
use App\Service\PdfEditorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(Client::class)->findAll();

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/ajouter/client', name: 'app_add_client')]
    public function addClient(
        Request $request,
        EntityManagerInterface $entityManager,
        ClientContractService $contractService,
        MailService $mailerService,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $client = new Client();
        $form = $this->createForm(ClientFormType::class, $client);
        $form->add('submit', SubmitType::class, [
            'label' => 'JE TRANSMETS LE CONTRAT',
            'attr' => ['class' => 'confirm-btn']
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();
    
            if ($this->isClientDataComplete($client)) {
                $contractService->generateAndSendContract($client);
                $this->addFlash('success', 'Le client a été ajouté et le contrat a été envoyé.');
            } else {
                $token = Uuid::v4()->toRfc4122();
                $client->setSecureToken($token);
                $entityManager->flush();
    
                $completionUrl = $urlGenerator->generate('client_complete_info', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
    
                $mailerService->sendEmail(
                    to: $client->getEmail() ?: 'chris.vermersch@hotmail.com',
                    subject: 'Demande de documents manquants',
                    template: 'emails/request_document.html.twig',
                    context: ['client' => $client, 'completionUrl' => $completionUrl]
                );
    
                $this->addFlash('info', 'Le client a été ajouté, mais certaines informations sont manquantes.');
            }
    
            return $this->redirectToRoute('app_add_client');
        }
    
        return $this->render('client/add.client.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/client/complete-info', name: 'client_complete_info')]
    public function completeClientInfo(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->query->get('token');

        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Lien invalide ou client introuvable.');
        }

        $form = $this->createForm(ClientFormType::class, $client, [
            'action' => $this->generateUrl('client_update_info'),
            'method' => 'POST'
        ]);

        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client
        ]);
    }

    #[Route('/client/update-info', name: 'client_update_info', methods: ['POST'])]
    public function updateClientInfo(
        Request $request,
        EntityManagerInterface $em,
        ClientContractService $contractService
    ): Response {
        $token = $request->request->get('token');
    
        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }
    
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }
    
        $form = $this->createForm(ClientFormType::class, $client);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $client->setSecureToken(null);
            $em->persist($client);
            $em->flush();
    
            $contractService->generateAndSendContract($client);
    
            $this->addFlash('success', 'Les informations ont été mises à jour et le contrat a été envoyé.');
    
            return $this->redirectToRoute('app_clients');
        }
    
        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client
        ]);
    }
    

    /**
     * Vérifie si tous les champs obligatoires sont remplis.
     */
    private function isClientDataComplete(Client $client): bool
    {
        return !empty($client->getEmail())
            && !empty($client->getSocietyName())
            && !empty($client->getPhone())
            && !empty($client->getAdress())
            && !empty($client->getName())
            && !empty($client->getLastname())
            && !empty($client->getSiret())
            && !empty($client->getCodeNaf())
            && !empty($client->getPriceKwh())
            && !empty($client->getPriceResale())
            && !empty($client->getLegalForm());
    }

    #[Route('/merge-pdfs', name: 'merge_pdfs')]
    public function mergePdfs(PdfEditorService $pdfEditorService): Response
    {
        $pdfFiles = [
            $this->getParameter('kernel.project_dir') . '/public/pdf/contrat_exploitation-2.pdf',
            $this->getParameter('kernel.project_dir') . '/public/pdf/contrat_exploitation-2.pdf',
            $this->getParameter('kernel.project_dir') . '/public/pdf/contrat_exploitation-3.pdf',
        ];

        $outputPath = $this->getParameter('kernel.project_dir') . '/public/pdf/contrat_exploitation_final.pdf';

        if ($pdfEditorService->mergePdfs($pdfFiles, $outputPath)) {
            return new Response('PDF fusionné avec succès ! <a href="/pdf/contrat_exploitation_final.pdf">Voir le PDF fusionné</a>');
        }

        return new Response('Erreur lors de la fusion des PDFs.');
    }

    #[Route('/generate-pdf', name: 'generate_pdf')]
    public function generatePdf(
        PdfEditorService $pdfEditorService,
        EntityManagerInterface $em,
        int $clientId
    ): Response {
        $client = $em->getRepository(Client::class)->find($clientId);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        $htmlContent = $this->renderView('pdf/contrat_template.html.twig', [
            'client' => $client
        ]);

        $pdfPath = $this->getParameter('kernel.project_dir') . "/public/pdf/contrat_{$client->getId()}.pdf";
        $pdfEditorService->createCustomPdf($pdfPath, $htmlContent);

        return new Response('PDF généré avec succès ! <a href="/pdf/contrat_' . $client->getId() . '.pdf">Voir le PDF</a>');
    }
}
