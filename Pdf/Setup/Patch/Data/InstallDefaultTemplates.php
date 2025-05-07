<?php
namespace Cortex\Pdf\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class InstallDefaultTemplates implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Filesystem $filesystem
     * @param File $fileDriver
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Filesystem $filesystem,
        File $fileDriver
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $templateDirectory = $mediaDirectory->getAbsolutePath('cortex_pdf/templates');

        // Criar diretório de templates se não existir
        if (!$this->fileDriver->isDirectory($templateDirectory)) {
            $this->fileDriver->createDirectory($templateDirectory, 0755);
        }

        // Copiar templates padrão
        $defaultTemplates = [
            'basic.json',
            'technical.json',
            'marketing.json'
        ];

        foreach ($defaultTemplates as $template) {
            $sourceFile = __DIR__ . '/../../etc/templates/default/' . $template;
            $targetFile = $templateDirectory . '/' . $template;

            if ($this->fileDriver->isExists($sourceFile)) {
                $content = $this->fileDriver->fileGetContents($sourceFile);
                $this->fileDriver->filePutContents($targetFile, $content);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}