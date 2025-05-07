<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class PdfWatermark
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
     * Add watermark to PDF
     *
     * @param string $content
     * @param array $options
     * @return string
     */
    public function addWatermark($content, $options = [])
    {
        try {
            $text = $options['text'] ?? '';
            $opacity = $options['opacity'] ?? 0.3;
            $angle = $options['angle'] ?? 45;
            $fontSize = $options['font_size'] ?? 36;
            $color = $options['color'] ?? 'gray';

            $tempFile = $this->tempDirectory . '/' . uniqid() . '.pdf';
            $watermarkedFile = $this->tempDirectory . '/' . uniqid() . '_watermarked.pdf';

            // Salvar PDF temporário
            $this->fileDriver->filePutContents($tempFile, $content);

            // Comando para adicionar marca d'água
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH ' .
                '-sOutputFile=%s ' .
                '-c "<</PageSize[595 842]>> setpagedevice ' .
                '0 0 595 842 rectfill ' .
                '0.3 setgray ' .
                '/Helvetica-Bold findfont 36 scalefont setfont ' .
                '297 421 moveto (%s) stringwidth pop 2 div neg 0 rmoveto ' .
                '(%s) show" ' .
                '-f %s',
                escapeshellarg($watermarkedFile),
                escapeshellarg($text),
                escapeshellarg($text),
                escapeshellarg($tempFile)
            );

            // Executar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Erro ao adicionar marca d\'água: ' . implode("\n", $output));
            }

            // Ler PDF com marca d'água
            $watermarkedContent = $this->fileDriver->fileGetContents($watermarkedFile);

            // Limpar arquivos temporários
            $this->fileDriver->deleteFile($tempFile);
            $this->fileDriver->deleteFile($watermarkedFile);

            return $watermarkedContent;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar marca d\'água: ' . $e->getMessage());
            return $content;
        }
    }

    /**
     * Add image watermark to PDF
     *
     * @param string $content
     * @param string $imagePath
     * @param array $options
     * @return string
     */
    public function addImageWatermark($content, $imagePath, $options = [])
    {
        try {
            $opacity = $options['opacity'] ?? 0.3;
            $position = $options['position'] ?? 'center';
            $scale = $options['scale'] ?? 0.5;

            $tempFile = $this->tempDirectory . '/' . uniqid() . '.pdf';
            $watermarkedFile = $this->tempDirectory . '/' . uniqid() . '_watermarked.pdf';

            // Salvar PDF temporário
            $this->fileDriver->filePutContents($tempFile, $content);

            // Comando para adicionar marca d'água com imagem
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH ' .
                '-sOutputFile=%s ' .
                '-c "<</PageSize[595 842]>> setpagedevice ' .
                '0 0 595 842 rectfill ' .
                '297 421 translate ' .
                '%s scale ' .
                '0 0 moveto ' .
                '(%s) run" ' .
                '-f %s',
                escapeshellarg($watermarkedFile),
                $scale,
                escapeshellarg($imagePath),
                escapeshellarg($tempFile)
            );

            // Executar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Erro ao adicionar marca d\'água com imagem: ' . implode("\n", $output));
            }

            // Ler PDF com marca d'água
            $watermarkedContent = $this->fileDriver->fileGetContents($watermarkedFile);

            // Limpar arquivos temporários
            $this->fileDriver->deleteFile($tempFile);
            $this->fileDriver->deleteFile($watermarkedFile);

            return $watermarkedContent;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar marca d\'água com imagem: ' . $e->getMessage());
            return $content;
        }
    }
}