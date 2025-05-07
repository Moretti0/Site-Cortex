<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LayoutTemplates implements OptionSourceInterface
{
    /**
     * Retorna opções para layout de templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Padrão')],
            ['value' => 'clean', 'label' => __('Simples')],
            ['value' => 'modern', 'label' => __('Moderno')],
            ['value' => 'detailed', 'label' => __('Detalhado')],
            ['value' => 'minimalist', 'label' => __('Minimalista')],
            ['value' => 'professional', 'label' => __('Profissional')],
            ['value' => 'custom', 'label' => __('Personalizado')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}