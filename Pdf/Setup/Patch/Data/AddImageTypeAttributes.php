<?php
namespace Cortex\Pdf\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddImageTypeAttributes implements DataPatchInterface
{
    protected $setup;

    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply()
    {
        $connection = $this->setup->getConnection();
        $attributeSetId = 4; // padrão

        $exists = $connection->fetchOne(
            "SELECT COUNT(*) FROM eav_attribute_group WHERE attribute_set_id = :set_id AND attribute_group_name = :group_name",
            ['set_id' => $attributeSetId, 'group_name' => 'Images']
        );

        if (!$exists) {
            $connection->insert('eav_attribute_group', [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_name' => 'Images',
                'sort_order' => 20,
                'attribute_group_code' => 'images'
            ]);
        }
    }

    public static function getDependencies() { return []; }

    public function getAliases() { return []; }
}
