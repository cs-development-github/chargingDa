<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Tarification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class ClientContractService
{
    public function __construct(
        private PdfEditorService $pdfEditorService,
        private MailService $mailService,
        private EntityManagerInterface $entityManager,
        private KernelInterface $kernel,
        private Environment $twig
    ) {}

    public function generateAndSendContract(Client $client, bool $sendEmail = true, bool $requestSignature = true): ?string
    {
        $projectDir = $this->kernel->getProjectDir() . "/public/pdf";
        $clientId = $client->getId();

        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0777, true);
        }

        $pdfFiles = [];
        $templates = [
            'first_page' => 'pdf/first_page_template.html.twig',
            'third_page' => 'pdf/third_page_template.html.twig',
            'twenty_second' => 'pdf/twenty_second_template.html.twig'
        ];

        foreach ($templates as $key => $template) {
            $pdfPath = "$projectDir/{$key}_{$clientId}.pdf";
            $htmlContent = $this->renderPdf($template, $client);
            file_put_contents("$projectDir/debug_{$key}.html", $htmlContent);
            
            $this->pdfEditorService->createCustomPdf($pdfPath, $htmlContent);
            if (file_exists($pdfPath) && filesize($pdfPath) > 0) {
                $pdfFiles[] = $pdfPath;
            }
        }

        // Récupération des informations de la borne de recharge
        $chargingData = $this->getChargingStationData($client);
        $twentyThirdPath = "$projectDir/twenty_third_{$clientId}.pdf";
        $htmlTwentyThird = $this->renderPdf('pdf/twenty_third_template.html.twig', $client, $chargingData);
        file_put_contents("$projectDir/debug_twenty_third.html", $htmlTwentyThird);
        $this->pdfEditorService->createCustomPdf($twentyThirdPath, $htmlTwentyThird);
        if (file_exists($twentyThirdPath) && filesize($twentyThirdPath) > 0) {
            $pdfFiles[] = $twentyThirdPath;
        }

        // Chemins des fichiers PDF fixes
        $existingPdfPaths = [
            "$projectDir/contrat_exploitation-2.pdf",
            "$projectDir/contrat_exploitation-4-21.pdf",
            "$projectDir/contrat_exploitation-24-27.pdf"
        ];
        $pdfFiles = array_merge($pdfFiles, array_filter($existingPdfPaths, 'file_exists'));

        // Fusion des fichiers PDF
        $mergedPdfPath = "$projectDir/contrat_final_{$clientId}.pdf";
        $success = $this->pdfEditorService->mergePdfs($pdfFiles, $mergedPdfPath);

        if (!$success || !file_exists($mergedPdfPath) || filesize($mergedPdfPath) === 0) {
            throw new \RuntimeException("Échec de la fusion des PDFs pour le client ID {$clientId}");
        }

        // Envoi de l'email si demandé
        if ($sendEmail) {
            $emailHtml = $this->renderPdf('emails/full_pdf.html.twig', $client);
            $this->mailService->sendEmailWithAttachment(
                to: $client->getEmail(),
                subject: "Votre contrat est prêt",
                htmlTemplate: $emailHtml,
                pdfPath: $mergedPdfPath
            );
        }
        return $mergedPdfPath;
    }

    private function renderPdf(string $template, Client $client, array $data = []): string
    {
        $context = array_merge(['client' => $client], $data);
        return $this->twig->render($template, $context);
    }

    private function getChargingStationData(Client $client): array
    {
        $tarifications = $this->entityManager->getRepository(Tarification::class)->findBy(['client' => $client]);

        if (empty($tarifications)) {
            throw new \RuntimeException("Aucune tarification trouvée pour le client ID {$client->getId()}");
        }

        $totalConnectors = 0;
        $chargingStationsNames = [];
        $tarifsData = [];

        foreach ($tarifications as $tarification) {
            $chargingStation = $tarification->getChargingStation();
            if (!$chargingStation) {
                throw new \RuntimeException("Aucune borne associée pour le client ID {$client->getId()} dans la tarification ID {$tarification->getId()}");
            }

            $pdc = $chargingStation->getConnectors();
            $connectorsCount = is_countable($pdc) ? count($pdc) : (int) $pdc;
            $totalConnectors += $connectorsCount;

            $chargingStationsNames[] = $chargingStation->getModel();

            $tarifsData[] = [
                'chargingStationModel' => $chargingStation->getModel(),
                'purchasePrice' => $tarification->getPurcharsePrice(),
                'resalePrice'   => $tarification->getResalePrice(),
                'publicPrice'   => $tarification->getPublicPrice(),
                'reducedPrice'  => $tarification->getReducedPrice(),
            ];
        }

        return [
            'chargingStationName' => implode(', ', array_unique($chargingStationsNames)),
            'totalConnectors'     => $totalConnectors,
            'tarifications'       => $tarifsData,
        ];
    }
}