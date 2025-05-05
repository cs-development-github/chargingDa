<?php

namespace App\Service;

use setasign\Fpdi\Fpdi;

class PdfEditorService
{
    public function mergePdfs(array $pdfFiles, string $outputPath)
    {
        $cmd = "gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$outputPath ";
        foreach ($pdfFiles as $file) {
            $cmd .= " $file ";
        }

        shell_exec($cmd);
        return file_exists($outputPath);
    }

    public function createCustomPdf(string $outputPath, string $htmlContent)
    {
        $htmlFile = sys_get_temp_dir() . '/custom_pdf.html';
        file_put_contents($htmlFile, $htmlContent);

        $cmd = "wkhtmltopdf $htmlFile $outputPath";
        shell_exec($cmd);

        unlink($htmlFile);

        return file_exists($outputPath);
    }
}