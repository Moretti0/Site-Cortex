<?php
namespace Cortex\Pdf\Model;

use Cortex\Pdf\Api\HistoryRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class HistoryRepository implements HistoryRepositoryInterface
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
    protected $historyDirectory;

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

        $this->historyDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('cortex_pdf/history');

        if (!$this->fileDriver->isDirectory($this->historyDirectory)) {
            $this->fileDriver->createDirectory($this->historyDirectory, 0755);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($sku, $filePath)
    {
        try {
            $historyFile = $this->historyDirectory . '/' . $this->getHistoryFileName($sku);
            $data = [
                'sku' => $sku,
                'file_path' => $filePath,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->fileDriver->filePutContents($historyFile, json_encode($data));
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao salvar histórico: ' . $e->getMessage());
            throw new LocalizedException(__('Erro ao salvar histórico: %1', $e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBySku($sku)
    {
        try {
            $historyFile = $this->historyDirectory . '/' . $this->getHistoryFileName($sku);

            if (!$this->fileDriver->isExists($historyFile)) {
                throw new NoSuchEntityException(__('Histórico não encontrado para o SKU: %1', $sku));
            }

            $content = $this->fileDriver->fileGetContents($historyFile);
            return json_decode($content, true);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter histórico: ' . $e->getMessage());
            throw new LocalizedException(__('Erro ao obter histórico: %1', $e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanOldHistory($daysToKeep = 30)
    {
        try {
            $files = $this->fileDriver->readDirectory($this->historyDirectory);
            $now = time();

            foreach ($files as $file) {
                if ($this->fileDriver->isFile($file)) {
                    $fileTime = $this->fileDriver->stat($file)['mtime'];
                    $daysOld = ($now - $fileTime) / (60 * 60 * 24);

                    if ($daysOld > $daysToKeep) {
                        $this->fileDriver->deleteFile($file);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao limpar histórico antigo: ' . $e->getMessage());
            throw new LocalizedException(__('Erro ao limpar histórico antigo: %1', $e->getMessage()));
        }
    }

    /**
     * Gera o nome do arquivo de histórico
     *
     * @param string $sku
     * @return string
     */
    private function getHistoryFileName($sku)
    {
        return 'history_' . md5($sku) . '.json';
    }
}