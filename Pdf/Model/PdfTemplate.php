<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class PdfTemplate extends AbstractModel
{
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
    protected $templateDirectory;

    /**
     * @var array|null
     */
    protected static ?array $memoryTemplates = null;

    /**
     * @var bool
     */
    protected bool $useMemory = false;

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

        $this->templateDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath('cortex_pdf/templates');

        if (!$this->fileDriver->isDirectory($this->templateDirectory)) {
            $this->fileDriver->createDirectory($this->templateDirectory, 0755);
        }
    }

    /**
     * Ativar/desativar modo memória (para testes)
     *
     * @param bool $enabled
     * @return void
     */
    public function setMemoryMode(bool $enabled = true): void
    {
        $this->useMemory = $enabled;
        if ($enabled && self::$memoryTemplates === null) {
            self::$memoryTemplates = [];
        }
    }

    /**
     * Get all templates
     *
     * @return array
     */
    public function getTemplates(): array
    {
        if ($this->useMemory) {
            return array_values(self::$memoryTemplates);
        }
        try {
            $templates = [];
            $files = $this->fileDriver->readDirectory($this->templateDirectory);

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    $content = $this->fileDriver->fileGetContents($file);
                    $data = json_decode($content, true);

                    if ($data) {
                        $templates[] = [
                            'id' => pathinfo($file, PATHINFO_FILENAME),
                            'name' => $data['name'] ?? 'Template sem nome',
                            'description' => $data['description'] ?? '',
                            'created_at' => $data['created_at'] ?? '',
                            'updated_at' => $data['updated_at'] ?? ''
                        ];
                    }
                }
            }

            return $templates;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter templates: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Load template by name
     *
     * @param string $name
     * @return array|null
     */
    public function loadTemplate(string $name): ?array
    {
        if ($this->useMemory) {
            foreach (self::$memoryTemplates as $template) {
                if ($template['id'] === $name || $template['name'] === $name) {
                    return $template;
                }
            }
            return null;
        }
        try {
            $templates = $this->getTemplates();

            foreach ($templates as $template) {
                if ($template['id'] === $name) {
                    return $this->getTemplateById($template['id']);
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao carregar template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save template
     *
     * @param array $data
     * @return string|false
     */
    public function saveTemplate(array $data)
    {
        if ($this->useMemory) {
            if (empty($data['name'])) {
                return false;
            }
            $templateId = $data['id'] ?? uniqid();
            $templateData = [
                'id' => $templateId,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'content' => $data['content'] ?? [],
                'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            self::$memoryTemplates[$templateId] = $templateData;
            return $templateId;
        }
        try {
            if (empty($data['name'])) {
                throw new \Exception('Nome do template é obrigatório');
            }

            $templateId = $data['id'] ?? uniqid();
            $filename = $this->templateDirectory . '/' . $templateId . '.json';

            $templateData = [
                'id' => $templateId,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'content' => $data['content'] ?? [],
                'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->fileDriver->filePutContents($filename, json_encode($templateData, JSON_PRETTY_PRINT));

            return $templateId;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao salvar template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete template
     *
     * @param string $templateId
     * @return bool
     */
    public function deleteTemplate(string $templateId): bool
    {
        if ($this->useMemory) {
            if (isset(self::$memoryTemplates[$templateId])) {
                unset(self::$memoryTemplates[$templateId]);
                return true;
            }
            return false;
        }
        try {
            $filename = $this->templateDirectory . '/' . $templateId . '.json';

            if ($this->fileDriver->isExists($filename)) {
                $this->fileDriver->deleteFile($filename);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao excluir template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get template by ID
     *
     * @param string $templateId
     * @return array|null
     */
    public function getTemplateById(string $templateId): ?array
    {
        try {
            $filename = $this->templateDirectory . '/' . $templateId . '.json';

            if ($this->fileDriver->isExists($filename)) {
                $content = $this->fileDriver->fileGetContents($filename);
                $data = json_decode($content, true);

                if ($data) {
                    return $data;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter template por ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template by name
     *
     * @param string $name
     * @return array|null
     */
    public function getTemplateByName(string $name): ?array
    {
        try {
            $templates = $this->getTemplates();

            foreach ($templates as $template) {
                if ($template['name'] === $name) {
                    return $this->getTemplateById($template['id']);
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter template por nome: ' . $e->getMessage());
            return null;
        }
    }
}