<?php
declare(strict_types=1);

namespace Cortex\Pdf\Model;

use Cortex\Pdf\Api\WatermarkInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;
use Exception;
use Magento\Framework\Exception\FileSystemException;

class Watermark implements WatermarkInterface
{
    private const TEMP_DIR = 'cortex_pdf/watermark';

    private $filesystem;
    private $file;
    private $logger;
    private $tempDir;

    public function __construct(
        Filesystem $filesystem,
        File $file,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->logger = $logger;

        $varDir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->tempDir = $varDir->getAbsolutePath(self::TEMP_DIR);

        try {
            if (!$this->file->isDirectory($this->tempDir)) {
                $this->file->createDirectory($this->tempDir);
            }
        } catch (FileSystemException $e) {
            $this->logger->error('Erro ao criar diretório temporário para marca d\'água: ' . $e->getMessage());
        }
    }

    /**
     * Adiciona marca d'água ao PDF
     *
     * @param string $pdfContent Conteúdo do PDF
     * @param array $options Opções da marca d'água
     * @return string Conteúdo do PDF com marca d'água
     */
    public function addWatermark(string $pdfContent, array $options = []): string
    {
        try {
            $tempFile = $this->tempDir . '/temp_' . uniqid() . '.pdf';
            $outputFile = $this->tempDir . '/watermarked_' . uniqid() . '.pdf';

            // Salva o PDF temporariamente
            $this->file->filePutContents($tempFile, $pdfContent);

            // Opções padrão
            $defaultOptions = [
                'text' => 'CONFIDENCIAL',
                'font' => 'Helvetica',
                'fontSize' => 40,
                'opacity' => 0.5,
                'rotation' => 45,
                'position' => 'center'
            ];

            $options = array_merge($defaultOptions, $options);

            // Comando GhostScript para adicionar marca d'água
            $gsCommand = sprintf(
                'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s ' .
                '-c "[ /Marked true /PDFMarks pdfmark" ' .
                '-c "[ /Title (%s) /Color [0.5 0.5 0.5] /Opacity %f /Rotate %d ' .
                '/Position [%s] /Font /%s /FontSize %d /Watermark pdfmark" ' .
                '-f %s',
                escapeshellarg($outputFile),
                escapeshellarg($options['text']),
                $options['opacity'],
                $options['rotation'],
                $this->getPositionCoordinates($options['position']),
                $options['font'],
                $options['fontSize'],
                escapeshellarg($tempFile)
            );

            exec($gsCommand, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Erro ao adicionar marca d\'água ao PDF');
            }

            $watermarkedContent = $this->file->fileGetContents($outputFile);

            // Limpa arquivos temporários
            $this->file->deleteFile($tempFile);
            $this->file->deleteFile($outputFile);

            return $watermarkedContent;

        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar marca d\'água ao PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adiciona marca d'água de imagem ao PDF
     *
     * @param string $pdfContent Conteúdo do PDF
     * @param string $imagePath Caminho da imagem
     * @param array $options Opções da marca d'água
     * @return string Conteúdo do PDF com marca d'água
     */
    public function addImageWatermark(string $pdfContent, string $imagePath, array $options = []): string
    {
        try {
            $tempFile = $this->tempDir . '/temp_' . uniqid() . '.pdf';
            $outputFile = $this->tempDir . '/watermarked_' . uniqid() . '.pdf';

            // Salva o PDF temporariamente
            $this->file->filePutContents($tempFile, $pdfContent);

            // Opções padrão
            $defaultOptions = [
                'opacity' => 0.5,
                'scale' => 1.0,
                'position' => 'center'
            ];

            $options = array_merge($defaultOptions, $options);

            // Comando GhostScript para adicionar marca d'água de imagem
            $gsCommand = sprintf(
                'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s ' .
                '-c "[ /Marked true /PDFMarks pdfmark" ' .
                '-c "[ /Image (%s) /Opacity %f /Scale %f ' .
                '/Position [%s] /ImageWatermark pdfmark" ' .
                '-f %s',
                escapeshellarg($outputFile),
                escapeshellarg($imagePath),
                $options['opacity'],
                $options['scale'],
                $this->getPositionCoordinates($options['position']),
                escapeshellarg($tempFile)
            );

            exec($gsCommand, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Erro ao adicionar marca d\'água de imagem ao PDF');
            }

            $watermarkedContent = $this->file->fileGetContents($outputFile);

            // Limpa arquivos temporários
            $this->file->deleteFile($tempFile);
            $this->file->deleteFile($outputFile);

            return $watermarkedContent;

        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar marca d\'água de imagem ao PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getPositionCoordinates(string $position): string
    {
        $positions = [
            'center' => '306 396',
            'top' => '306 792',
            'bottom' => '306 0',
            'left' => '0 396',
            'right' => '612 396',
            'top-left' => '0 792',
            'top-right' => '612 792',
            'bottom-left' => '0 0',
            'bottom-right' => '612 0'
        ];

        return $positions[$position] ?? $positions['center'];
    }
}