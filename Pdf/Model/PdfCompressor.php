<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class PdfCompressor
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        File $fileDriver,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;

        $this->tempDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('cortex_pdf/temp');

        if (!$this->fileDriver->isDirectory($this->tempDirectory)) {
            $this->fileDriver->createDirectory($this->tempDirectory, 0755);
        }
    }

    /**
     * Compress PDF
     *
     * @param string $content
     * @param array $options
     * @return string
     */
    public function compress($content, $options = [])
    {
        try {
            $quality = $options['quality'] ?? 85;
            $dpi = $options['dpi'] ?? 150;
            $tempFile = $this->tempDirectory . '/' . uniqid() . '.pdf';
            $compressedFile = $this->tempDirectory . '/' . uniqid() . '_compressed.pdf';

            // Salvar PDF temporário
            $this->fileDriver->filePutContents($tempFile, $content);

            // Comando para compressão
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellarg($compressedFile),
                escapeshellarg($tempFile)
            );

            // Executar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Erro ao comprimir PDF: ' . implode("\n", $output));
            }

            // Ler PDF comprimido
            $compressedContent = $this->fileDriver->fileGetContents($compressedFile);

            // Limpar arquivos temporários
            $this->fileDriver->deleteFile($tempFile);
            $this->fileDriver->deleteFile($compressedFile);

            return $compressedContent;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao comprimir PDF: ' . $e->getMessage());
            return $content;
        }
    }

    /**
     * Optimize PDF
     *
     * @param string $content
     * @param array $options
     * @return string
     */
    public function optimize($content, $options = [])
    {
        try {
            $tempFile = $this->tempDirectory . '/' . uniqid() . '.pdf';
            $optimizedFile = $this->tempDirectory . '/' . uniqid() . '_optimized.pdf';

            // Salvar PDF temporário
            $this->fileDriver->filePutContents($tempFile, $content);

            // Comando para otimização
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/printer -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellarg($optimizedFile),
                escapeshellarg($tempFile)
            );

            // Executar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Erro ao otimizar PDF: ' . implode("\n", $output));
            }

            // Ler PDF otimizado
            $optimizedContent = $this->fileDriver->fileGetContents($optimizedFile);

            // Limpar arquivos temporários
            $this->fileDriver->deleteFile($tempFile);
            $this->fileDriver->deleteFile($optimizedFile);

            return $optimizedContent;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao otimizar PDF: ' . $e->getMessage());
            return $content;
        }
    }
}