<?php
namespace Cortex\Pdf\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Cortex\Pdf\Model\Template\ConfigFactory;
use Cortex\Pdf\Model\ResourceModel\Template\Config as TemplateResource;

class CreateDefaultTemplate implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $templateFactory;
    protected $templateResource;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConfigFactory $templateFactory,
        TemplateResource $templateResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->templateFactory = $templateFactory;
        $this->templateResource = $templateResource;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $template = $this->templateFactory->create();
        $template->setData([
            'name' => 'Template Padrão',
            'layout' => 'default',
            'json_config' => json_encode([
                'title' => 'Template Padrão',
                'layout' => 'default',
                'sections' => [
                    [
                        'label' => 'Características Básicas',
                        'attributes' => ['name', 'sku', 'short_description']
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => 1
        ]);

        $this->templateResource->save($template);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
