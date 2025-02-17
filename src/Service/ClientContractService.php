<?php

namespace App\Service;

use App\Entity\Client;
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

        $firstPagePath = "$projectDir/first_page_{$client->getId()}.pdf";
        $this->pdfEditorService->createCustomPdf($firstPagePath, $this->renderPdf('pdf/first_page_template.html.twig', $client));

        $thirdPagePath = "$projectDir/third_page_{$client->getId()}.pdf";
        $this->pdfEditorService->createCustomPdf($thirdPagePath, $this->renderPdf('pdf/third_page_template.html.twig', $client));

        $twentySecondPath = "$projectDir/twenty_second_{$client->getId()}.pdf";
        $this->pdfEditorService->createCustomPdf($twentySecondPath, $this->renderPdf('pdf/twenty_second_template.html.twig', $client));

        $twentyThirdPath = "$projectDir/twenty_third_{$client->getId()}.pdf";
        $this->pdfEditorService->createCustomPdf($twentyThirdPath, $this->renderPdf('pdf/twenty_third_template.html.twig', $client));

        $existingPdfPath2 = "$projectDir/contrat_exploitation-2.pdf";
        $existingPdfPath4 = "$projectDir/contrat_exploitation-4-21.pdf";
        $existingPdfPath5 = "$projectDir/contrat_exploitation-24-27.pdf";

        $mergedPdfPath = "$projectDir/contrat_final_{$client->getId()}.pdf";
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
            return null;
        }

        if ($sendEmail) {
            $this->mailService->sendEmailWithAttachment(
                to: $client->getEmail(),
                subject: "Votre contrat est prêt",
                htmlTemplate: $this->renderPdf('emails/full_pdf.html.twig', $client),
                pdfPath: $mergedPdfPath
            );
        }

        return $mergedPdfPath;
    }

    private function renderPdf(string $template, Client $client): string
    {
        return $this->twig->render($template, ['client' => $client]);
    }
}
