<?php

namespace App\Service;

use setasign\Fpdi\Fpdi;

class PdfEditorService
{
    public function mergePdfs(array $pdfFiles, string $outputPath)
    {
        // ✅ Utilisation de Ghostscript pour fusionner les PDF
        $cmd = "gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$outputPath ";
        foreach ($pdfFiles as $file) {
            $cmd .= " $file ";
        }

        shell_exec($cmd);
        return file_exists($outputPath);
    }

    public function createCustomPdf(string $outputPath, string $htmlContent)
    {
        // ✅ Créer un fichier HTML temporaire
        $htmlFile = sys_get_temp_dir() . '/custom_pdf.html';
        file_put_contents($htmlFile, $htmlContent);

        // ✅ Commande WKHTMLtoPDF pour convertir le HTML en PDF
        $cmd = "wkhtmltopdf $htmlFile $outputPath";
        shell_exec($cmd);

        unlink($htmlFile); // Supprime le fichier temporaire

        return file_exists($outputPath);
    }
}