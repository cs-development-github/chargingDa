<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Tarification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class ClientContractService
{
    public function __construct(
        private PdfEditorService $pdfEditorService,
        private MailService $mailService,
        private EntityManagerInterface $entityManager,
        private KernelInterface $kernel,
        private Environment $twig,
        private HttpClientInterface $httpClient,
        private UrlGeneratorInterface $urlGenerator,
        private ContractSignatureService $contractSignatureService,
        private LoggerInterface $logger
    ) {}

    public function generateAndSendContract(Client $client, bool $requestSignature = true): ?string
    {
        $baseDir = $this->kernel->getProjectDir() . '/var/contracts';
        $clientId = $client->getDocumentId();
        $tmpDir = $baseDir . "/tmp/{$clientId}";

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $pdfFiles = [];

        $pdfFiles[] = $this->generateCustomPdf("pdf/first_page_template.html.twig", "$tmpDir/first_page.pdf", ['client' => $client]);

        $pdfFiles[] = $this->getExistingPdf("$baseDir/contrat_exploitation-2.pdf");

        $pdfFiles[] = $this->generateCustomPdf("pdf/third_page_template.html.twig", "$tmpDir/third_page.pdf", ['client' => $client]);

        $pdfFiles[] = $this->getExistingPdf("$baseDir/contrat_exploitation-4-21.pdf");

        $pdfFiles[] = $this->generateCustomPdf("pdf/twenty_second_template.html.twig", "$tmpDir/twenty_second.pdf", ['client' => $client]);

        $chargingData = $this->getChargingStationData($client);
        $pdfFiles[] = $this->generateCustomPdf("pdf/twenty_third_template.html.twig", "$tmpDir/twenty_third.pdf", ['client' => $client] + $chargingData);

        $pdfFiles[] = $this->getExistingPdf("$baseDir/contrat_exploitation-24-25.pdf");

        $pdfFiles = array_filter($pdfFiles);

        $finalPath = "$baseDir/contrat_final_{$clientId}.pdf";
        $success = $this->pdfEditorService->mergePdfs($pdfFiles, $finalPath);

        if (!$success || !file_exists($finalPath) || filesize($finalPath) === 0) {
            $this->logger->error("Erreur de fusion de contrat pour client ID $clientId");
            throw new \RuntimeException("Échec de la fusion des PDFs pour le client ID {$clientId}");
        }

        $this->logger->info("Contrat généré avec succès pour le client ID $clientId");

        if ($requestSignature) {
            $this->contractSignatureService->sign($client);
        }

        $this->cleanupTempFiles($tmpDir);

        return $finalPath;
    }
    
    private function cleanupTempFiles(string $tmpDir): void
    {
        if (!is_dir($tmpDir)) {
            return;
        }

        $files = glob($tmpDir . '/*.pdf');
        foreach ($files as $file) {
            @unlink($file);
        }

        @rmdir($tmpDir);
    }

    private function generateCustomPdf(string $template, string $outputPath, array $params): ?string
    {
        $html = $this->twig->render($template, $params);
        $this->pdfEditorService->createCustomPdf($outputPath, $html);

        return (file_exists($outputPath) && filesize($outputPath) > 0) ? $outputPath : null;
    }

    private function getExistingPdf(string $path): ?string
    {
        return (file_exists($path) && filesize($path) > 0) ? $path : null;
    }

    private function getChargingStationData(Client $client): array
    {
        $tarifications = $this->entityManager->getRepository(Tarification::class)->findBy(['client' => $client]);

        $totalConnectors = 0;
        $chargingStationsNames = [];
        $tarifsData = [];
        $typeOfs = [];

        foreach ($tarifications as $tarification) {
            $chargingStation = $tarification->getChargingStation();
            if (!$chargingStation) {
                throw new \RuntimeException(sprintf(
                    "Aucune borne associée pour le client ID %d dans la tarification ID %d",
                    $client->getId(),
                    $tarification->getId()
                ));
            }

            $pdc = $chargingStation->getConnectors();
            $connectorsCount = is_countable($pdc) ? count($pdc) : (int) $pdc;
            $totalConnectors += $connectorsCount;

            $chargingStationsNames[] = $chargingStation->getModel();

            $tarifsData[] = [
                'chargingStationModel' => $chargingStation->getModel(),
                'resalePrice'          => $tarification->getResalePrice(),
                'publicPrice'          => $tarification->getPublicPrice(),
                'reducedPrice'         => $tarification->getReducedPrice(),
                'parkingTime'          => $tarification->getParkingTimeResale(),
                'minutePrice'          => $tarification->getRechargeTimeResale(),
                'typeOf'               => $tarification->getOfferType(),
            ];

            $typeOfs[] = $tarification->getOfferType();
        }

        $typeOfs = array_unique($typeOfs);
        $unifiedTypeOf = count($typeOfs) === 1 ? $typeOfs[0] : null;

        return [
            'chargingStationName' => implode(', ', array_unique($chargingStationsNames)),
            'totalConnectors'     => $totalConnectors,
            'tarifications'       => $tarifsData,
            'typeOf'              => $unifiedTypeOf,
        ];
    }
}
