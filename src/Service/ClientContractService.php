<?php

namespace App\Service;

use Twig\Environment;
use App\Entity\Client;
use App\Entity\Tarification;
use App\Service\ContractSignatureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        private ContractSignatureService $contractSignatureService
    ) {}

    public function generateAndSendContract(Client $client, bool $sendEmail = true, bool $requestSignature = true): ?string
    {
        $projectDir = $this->kernel->getProjectDir() . "/public/pdf";
        $clientId = $client->getDocumentId();

        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0777, true);
        }

        $pdfFiles = [];
        $templateMap = [
            'first_page' => 'pdf/first_page_template.html.twig',
            'third_page' => 'pdf/third_page_template.html.twig',
            'twenty_second' => 'pdf/twenty_second_template.html.twig',
        ];

        foreach ($templateMap as $prefix => $template) {
            $html = $this->renderPdf($template, $client);
            $pdfPath = "$projectDir/{$prefix}_{$clientId}.pdf";

            $this->pdfEditorService->createCustomPdf($pdfPath, $html);
            if (file_exists($pdfPath) && filesize($pdfPath) > 0) {
                $pdfFiles[] = $pdfPath;
            }
        }

        // Génération de la page 23 avec données supplémentaires
        $chargingData = $this->getChargingStationData($client);
        $html23 = $this->renderPdf('pdf/twenty_third_template.html.twig', $client, $chargingData);
        $pdfPath23 = "$projectDir/twenty_third_{$clientId}.pdf";

        $this->pdfEditorService->createCustomPdf($pdfPath23, $html23);
        if (file_exists($pdfPath23) && filesize($pdfPath23) > 0) {
            $pdfFiles[] = $pdfPath23;
        }

        // Fichiers fixes existants
        $fixedPaths = [
            "$projectDir/contrat_exploitation-2.pdf",
            "$projectDir/contrat_exploitation-4-21.pdf",
            "$projectDir/contrat_exploitation-24-25.pdf"
        ];
        $pdfFiles = array_merge($pdfFiles, array_filter($fixedPaths, 'file_exists'));

        // Fusion en PDF final
        $finalPath = "$projectDir/contrat_final_{$clientId}.pdf";
        $success = $this->pdfEditorService->mergePdfs($pdfFiles, $finalPath);

        if (!$success || !file_exists($finalPath) || filesize($finalPath) === 0) {
            throw new \RuntimeException("Échec de la fusion des PDFs pour le client ID {$clientId}");
        }

        if ($requestSignature) {
            $this->contractSignatureService->sign($client);
        }

        return $finalPath;
    }

    private function renderPdf(string $template, Client $client, array $data = []): string
    {
        return $this->twig->render($template, array_merge(['client' => $client], $data));
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