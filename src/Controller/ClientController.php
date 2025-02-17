<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientFormType;
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

            $isComplete = $this->isClientDataComplete($client);
            $template = $isComplete ? 'emails/full_pdf.html.twig' : 'emails/request_document.html.twig';

            $clientEmail = $client->getEmail();
            $recipient = $clientEmail ?: ($isComplete ? 'chris.vermersch@hotmail.com' : null);

            if (!$recipient) {
                $this->addFlash('error', 'Impossible d’envoyer l’e-mail : aucune adresse e-mail valide trouvée.');
                return $this->redirectToRoute('app_add_client');
            }

            // Si le dossier est incomplet, on génère un token sécurisé et une URL
            $context = ['client' => $client];

            if (!$isComplete) {
                $token = Uuid::v4()->toRfc4122();
                $client->setSecureToken($token);
                $entityManager->flush();

                $completionUrl = $urlGenerator->generate(
                    'client_complete_info',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $context['completionUrl'] = $completionUrl;
            }

            // Envoi de l'e-mail
            $mailerService->sendEmail(
                to: $recipient,
                subject: $isComplete ? 'Contrat client validé' : 'Demande de documents manquants',
                template: $template,
                context: $context
            );

            $this->addFlash('success', 'Le client a été ajouté avec succès et un e-mail a été envoyé.');

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

        // Recherche du client via le token
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Lien invalide ou client introuvable.');
        }

        // Création du formulaire
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
        PdfEditorService $pdfEditorService,
        MailService $mailService
    ): Response {
        $token = $request->request->get('token');

        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }

        // Vérification du client en base
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        // Création du formulaire et gestion de la requête
        $form = $this->createForm(ClientFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Suppression du token après soumission
            $client->setSecureToken(null);
            $em->persist($client);
            $em->flush();

            // ✅ 1. Remplacer les placeholders dans le HTML
            $htmlContent = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contrat Exploitation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 900px; background: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); border-radius: 5px; margin: auto; }
            h1 { text-align: center; font-size: 24px; text-transform: uppercase; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 20px; }
            .section-title { font-size: 18px; font-weight: bold; margin-top: 20px; border-bottom: 2px solid #ccc; padding-bottom: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
            th { background-color: #f4f4f4; font-weight: bold; }
            tbody tr:nth-child(even) { background-color: #f9f9f9; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Contrat de Service de Plateforme de Gestion</h1>
            <p>Arras, le <strong>' . date("d/m/Y") . '</strong></p>
    
            <h2 class="section-title">1) Le prestataire :</h2>
            <p>
                LODMI société de droit Français enregistrée au RCS Arras sous le numéro B 901 360 602, 
                ayant son siège social au 11 Rue Willy Brandt 62000 ARRAS, immatriculée au registre du 
                commerce, sous le SIRET n° 90136060200030, dûment représentée par 
                M. Thomas SAINT MACHIN, agissant en qualité de Président de ladite société.
            </p>
    
            <h2 class="section-title">2) Le client :</h2>
            <table>
                <thead>
                    <tr>
                        <th>Raison sociale</th>
                        <th>Siret</th>
                        <th>Forme juridique</th>
                        <th>Code Naf</th>
                        <th>Numéro de TVA</th>
                        <th>Adresse</th>
                        <th>Représentant</th>
                        <th>Numéro de contrat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . htmlspecialchars($client->getSocietyName()) . '</td>
                        <td>' . htmlspecialchars($client->getSiret()) . '</td>
                        <td> a définir </td>
                        <td>' . htmlspecialchars($client->getCodeNaf()) . '</td>
                        <td>' . htmlspecialchars($client->getPriceKwh()) . '</td>
                        <td>' . htmlspecialchars($client->getAdress()) . '</td>
                        <td>' . htmlspecialchars($client->getName()) . ' ' . htmlspecialchars($client->getLastname()) . '</td>
                        <td>' . uniqid() . '</td>
                    </tr>
                </tbody>
            </table>
    
            <p>Agissant en qualités, les Parties reconnaissent avoir la capacité juridique suffisante 
            pour conclure le présent contrat de service de plateforme de gestion.</p>
        </div>
    </body>
    </html>
            ';

            // ✅ 2. Générer le PDF
            $pdfPath = $this->getParameter('kernel.project_dir') . "/public/pdf/contrat_{$client->getId()}.pdf";
            $pdfEditorService->createCustomPdf($pdfPath, $htmlContent);

            // ✅ 3. Envoyer le PDF par email
            $mailService->sendEmailWithAttachment(
                $client->getEmail(),
                "Votre contrat est prêt",
                "Veuillez trouver ci-joint votre contrat de service.",
                $pdfPath
            );

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
            && !empty($client->getPriceResale());
    }

    #[Route('/merge-pdfs', name: 'merge_pdfs')]
    public function mergePdfs(PdfEditorService $pdfEditorService): Response
    {
        $pdfFiles = [
            $this->getParameter('kernel.project_dir') . '/public/pdf/contrat_exploitation-1.pdf',
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
    
        return new Response('PDF généré avec succès ! <a href="/pdf/contrat_'. $client->getId() .'.pdf">Voir le PDF</a>');
    }
    
}
