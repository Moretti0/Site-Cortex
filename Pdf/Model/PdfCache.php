<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class PdfCache
{
    const CACHE_TAG = 'CORTEX_PDF';
    const CACHE_LIFETIME = 86400; // 24 horas

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var StateInterface
     */
    protected $cacheState;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $cacheDirectory;

    /**
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param StateInterface $cacheState
     * @param Manager $cacheManager
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        StateInterface $cacheState,
        Manager $cacheManager,
        Filesystem $filesystem,
        File $fileDriver,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheState = $cacheState;
        $this->cacheManager = $cacheManager;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;

        $this->cacheDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('cortex_pdf/cache');

        if (!$this->fileDriver->isDirectory($this->cacheDirectory)) {
            $this->fileDriver->createDirectory($this->cacheDirectory, 0755);
        }
    }

    /**
     * Get cached PDF
     *
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        try {
            $cacheKey = $this->getCacheKey($key);
            $cacheFile = $this->cacheDirectory . '/' . $cacheKey;

            if ($this->fileDriver->isExists($cacheFile)) {
                $content = $this->fileDriver->fileGetContents($cacheFile);
                $data = json_decode($content, true);

                if ($data && isset($data['content']) && isset($data['expires'])) {
                    if ($data['expires'] > time()) {
                        return $data['content'];
                    } else {
                        $this->fileDriver->deleteFile($cacheFile);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter PDF do cache: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Save PDF to cache
     *
     * @param string $key
     * @param string $content
     * @return bool
     */
    public function save($key, $content)
    {
        try {
            $cacheKey = $this->getCacheKey($key);
            $cacheFile = $this->cacheDirectory . '/' . $cacheKey;

            $data = [
                'content' => $content,
                'expires' => time() + self::CACHE_LIFETIME
            ];

            $this->fileDriver->filePutContents($cacheFile, json_encode($data));
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao salvar PDF no cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove PDF from cache
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        try {
            $cacheKey = $this->getCacheKey($key);
            $cacheFile = $this->cacheDirectory . '/' . $cacheKey;

            if ($this->fileDriver->isExists($cacheFile)) {
                $this->fileDriver->deleteFile($cacheFile);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao remover PDF do cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean cache
     *
     * @return bool
     */
    public function clean()
    {
        try {
            $files = $this->fileDriver->readDirectory($this->cacheDirectory);
            foreach ($files as $file) {
                if ($this->fileDriver->isFile($file)) {
                    $this->fileDriver->deleteFile($file);
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao limpar cache de PDFs: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se o cache está habilitado
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/advanced/cache_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cache key
     *
     * @param string $key
     * @return string
     */
    protected function getCacheKey($key)
    {
        return md5($key);
    }
}