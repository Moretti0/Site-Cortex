<?php
namespace Cortex\Pdf\Model\Config\Source;

class TableStyles implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'simple', 'label' => __('Simples')],
            ['value' => 'striped', 'label' => __('Listrado')],
            ['value' => 'bordered', 'label' => __('Bordas Completas')],
            ['value' => 'minimal', 'label' => __('Minimalista')],
            ['value' => 'modern', 'label' => __('Moderno')]
        ];
    }
}