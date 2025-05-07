<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class Statistics
{
    const STATS_DIRECTORY = 'cortex_pdf/stats';
    const STATS_FILE = 'statistics.json';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

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
    protected $statsDirectory;

    /**
     * @var string
     */
    protected $statsFile;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        File $fileDriver,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;

        $this->statsDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath(self::STATS_DIRECTORY);

        if (!$this->fileDriver->isDirectory($this->statsDirectory)) {
            $this->fileDriver->createDirectory($this->statsDirectory, 0755);
        }

        $this->statsFile = $this->statsDirectory . '/' . self::STATS_FILE;
    }

    /**
     * Registrar geração de PDF
     *
     * @param string $sku
     * @param string $templateName
     * @param float $generationTime
     * @param int $fileSize
     * @param bool $success
     * @return bool
     */
    public function recordPdfGeneration($sku, $templateName, $generationTime, $fileSize, $success = true)
    {
        try {
            $stats = $this->loadStats();

            // Incrementar contadores
            $stats['total_generations']++;
            if ($success) {
                $stats['successful_generations']++;
            } else {
                $stats['failed_generations']++;
            }

            // Atualizar estatísticas por template
            if (!isset($stats['templates'][$templateName])) {
                $stats['templates'][$templateName] = [
                    'count' => 0,
                    'total_time' => 0,
                    'total_size' => 0,
                    'success_count' => 0,
                    'fail_count' => 0
                ];
            }
            $stats['templates'][$templateName]['count']++;
            $stats['templates'][$templateName]['total_time'] += $generationTime;
            $stats['templates'][$templateName]['total_size'] += $fileSize;
            if ($success) {
                $stats['templates'][$templateName]['success_count']++;
            } else {
                $stats['templates'][$templateName]['fail_count']++;
            }

            // Atualizar estatísticas por produto
            if (!isset($stats['products'][$sku])) {
                $stats['products'][$sku] = [
                    'count' => 0,
                    'last_generation' => null
                ];
            }
            $stats['products'][$sku]['count']++;
            $stats['products'][$sku]['last_generation'] = date('Y-m-d H:i:s');

            // Atualizar estatísticas de performance
            $stats['total_generation_time'] += $generationTime;
            $stats['total_file_size'] += $fileSize;
            $stats['average_generation_time'] = $stats['total_generation_time'] / $stats['total_generations'];
            $stats['average_file_size'] = $stats['total_file_size'] / $stats['total_generations'];

            // Salvar estatísticas
            $this->saveStats($stats);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao registrar estatísticas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter estatísticas gerais
     *
     * @return array
     */
    public function getGeneralStats()
    {
        try {
            $stats = $this->loadStats();
            return [
                'total_generations' => $stats['total_generations'] ?? 0,
                'successful_generations' => $stats['successful_generations'] ?? 0,
                'failed_generations' => $stats['failed_generations'] ?? 0,
                'success_rate' => $this->calculateSuccessRate($stats),
                'average_generation_time' => $stats['average_generation_time'] ?? 0,
                'average_file_size' => $stats['average_file_size'] ?? 0,
                'total_file_size' => $stats['total_file_size'] ?? 0
            ];
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter estatísticas gerais: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter estatísticas por template
     *
     * @return array
     */
    public function getTemplateStats()
    {
        try {
            $stats = $this->loadStats();
            return $stats['templates'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter estatísticas por template: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter estatísticas por produto
     *
     * @return array
     */
    public function getProductStats()
    {
        try {
            $stats = $this->loadStats();
            return $stats['products'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter estatísticas por produto: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpar estatísticas
     *
     * @return bool
     */
    public function clearStats()
    {
        try {
            $this->saveStats([
                'total_generations' => 0,
                'successful_generations' => 0,
                'failed_generations' => 0,
                'total_generation_time' => 0,
                'total_file_size' => 0,
                'average_generation_time' => 0,
                'average_file_size' => 0,
                'templates' => [],
                'products' => []
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao limpar estatísticas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Carregar estatísticas
     *
     * @return array
     */
    protected function loadStats()
    {
        try {
            if ($this->fileDriver->isExists($this->statsFile)) {
                $content = $this->fileDriver->fileGetContents($this->statsFile);
                return json_decode($content, true) ?: [];
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao carregar estatísticas: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Salvar estatísticas
     *
     * @param array $stats
     * @return bool
     */
    protected function saveStats($stats)
    {
        try {
            $this->fileDriver->filePutContents($this->statsFile, json_encode($stats, JSON_PRETTY_PRINT));
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao salvar estatísticas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcular taxa de sucesso
     *
     * @param array $stats
     * @return float
     */
    protected function calculateSuccessRate($stats)
    {
        $total = $stats['total_generations'] ?? 0;
        if ($total === 0) {
            return 0;
        }
        return ($stats['successful_generations'] ?? 0) / $total * 100;
    }
}