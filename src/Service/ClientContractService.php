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

    public function generateAndSendContract(Client $client, bool $sendEmail = true): ?string
    {
        $projectDir = $this->kernel->getProjectDir() . "/public/pdf";
        $clientId = $client->getId();

        // Génération des pages PDF standards
        $firstPagePath = "$projectDir/first_page_{$clientId}.pdf";
        $this->pdfEditorService->createCustomPdf(
            $firstPagePath,
            $this->renderPdf('pdf/first_page_template.html.twig', $client)
        );

        $thirdPagePath = "$projectDir/third_page_{$clientId}.pdf";
        $this->pdfEditorService->createCustomPdf(
            $thirdPagePath,
            $this->renderPdf('pdf/third_page_template.html.twig', $client)
        );

        $twentySecondPath = "$projectDir/twenty_second_{$clientId}.pdf";
        $this->pdfEditorService->createCustomPdf(
            $twentySecondPath,
            $this->renderPdf('pdf/twenty_second_template.html.twig', $client)
        );

        // Récupération des informations liées à la borne de recharge
        $chargingData = $this->getChargingStationData($client);
        $htmlTwentyThird = $this->renderPdf(
            'pdf/twenty_third_template.html.twig',
            $client,
            $chargingData
        );
        $twentyThirdPath = "$projectDir/twenty_third_{$clientId}.pdf";
        $this->pdfEditorService->createCustomPdf($twentyThirdPath, $htmlTwentyThird);

        // Chemins des PDF existants à fusionner
        $existingPdfPath2 = "$projectDir/contrat_exploitation-2.pdf";
        $existingPdfPath4 = "$projectDir/contrat_exploitation-4-21.pdf";
        $existingPdfPath5 = "$projectDir/contrat_exploitation-24-27.pdf";

        $mergedPdfPath = "$projectDir/contrat_final_{$clientId}.pdf";
        $success = $this->pdfEditorService->mergePdfs([
            $firstPagePath,
            $existingPdfPath2,
            $thirdPagePath,
            $existingPdfPath4,
            $twentySecondPath,
            $twentyThirdPath,
            $existingPdfPath5
        ], $mergedPdfPath);

        if (!$success || !file_exists($mergedPdfPath)) {
            // Vous pouvez logger l'erreur ou lancer une exception personnalisée ici
            return null;
        }

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

    /**
     * Rendu d'un template Twig en y injectant le client et des données additionnelles.
     */
    private function renderPdf(string $template, Client $client, array $data = []): string
    {
        $context = array_merge(['client' => $client], $data);
        return $this->twig->render($template, $context);
    }

    /**
     * Récupère les informations de la borne de recharge à partir de la tarification liée au client.
     *
     * @throws \RuntimeException si aucune tarification ou borne n'est trouvée.
     */
    private function getChargingStationData(Client $client): array
    {
        $tarifications = $this->entityManager
            ->getRepository(Tarification::class)
            ->findBy(['client' => $client]);
    
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
    
            // On récupère le nombre de connecteurs via getConnectors()
            $pdc = $chargingStation->getConnectors();
            $connectorsCount = is_countable($pdc) ? count($pdc) : (int) $pdc;
            $totalConnectors += $connectorsCount;
    
            $chargingStationsNames[] = $chargingStation->getModel();

            // Récupération des prix depuis la tarification
            $tarifsData[] = [
                'chargingStationModel' => $chargingStation->getModel(),
                'purchasePrice' => $tarification->getPurcharsePrice(),
                'resalePrice'   => $tarification->getResalePrice(),
                'reducedPrice'  => $tarification->getReducedPrice(),
            ];
        }
    
        $chargingStationsNames = implode(', ', array_unique($chargingStationsNames));
    
        return [
            'chargingStationName' => $chargingStationsNames,
            'totalConnectors'     => $totalConnectors,
            'tarifications'       => $tarifsData,
        ];
    }
    
}