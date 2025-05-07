<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\Filesystem\DirectoryList;

class WkhtmltopdfGenerator
{
    protected DirectoryList $directoryList;

    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    public function renderFromHtml(string $html, string $filename): string
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $outputPath = $mediaDir . '/cortex/pdf/' . $filename;

        // Garante diretório
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0775, true);
        }

        // Arquivo temporário HTML
        $tempHtml = tempnam(sys_get_temp_dir(), 'cortex_pdf_') . '.html';
        file_put_contents($tempHtml, $html);

        // Comando wkhtmltopdf
        $command = sprintf(
            'wkhtmltopdf --encoding utf-8 "%s" "%s"',
            escapeshellarg($tempHtml),
            escapeshellarg($outputPath)
        );

        exec($command, $output, $status);

        // Limpa arquivo temporário
        unlink($tempHtml);

        if ($status !== 0) {
            throw new \RuntimeException("Falha ao gerar PDF com wkhtmltopdf.");
        }

        return $outputPath;
    }
}
