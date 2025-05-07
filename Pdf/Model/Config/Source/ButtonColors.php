<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ButtonColors implements ArrayInterface
{
    const BLUE = 'blue';
    const GREEN = 'green';
    const RED = 'red';
    const ORANGE = 'orange';
    const PURPLE = 'purple';
    const CUSTOM = 'custom';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BLUE, 'label' => __('Azul')],
            ['value' => self::GREEN, 'label' => __('Verde')],
            ['value' => self::RED, 'label' => __('Vermelho')],
            ['value' => self::ORANGE, 'label' => __('Laranja')],
            ['value' => self::PURPLE, 'label' => __('Roxo')],
            ['value' => self::CUSTOM, 'label' => __('Personalizado')]
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