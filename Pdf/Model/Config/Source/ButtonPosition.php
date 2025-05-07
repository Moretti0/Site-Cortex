<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ButtonPosition implements OptionSourceInterface
{
    /**
     * Retorna opções para posição do botão
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'before_add_to_cart', 'label' => __('Antes do botão Adicionar ao Carrinho')],
            ['value' => 'after_add_to_cart', 'label' => __('Depois do botão Adicionar ao Carrinho')],
            ['value' => 'top_product_info', 'label' => __('Topo da área de informações do produto')],
            ['value' => 'bottom_product_info', 'label' => __('Base da área de informações do produto')],
            ['value' => 'after_price', 'label' => __('Depois do preço')]
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