<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ButtonSizes implements ArrayInterface
{
    const SMALL = 'small';
    const MEDIUM = 'medium';
    const LARGE = 'large';
    const XLARGE = 'xlarge';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SMALL, 'label' => __('Pequeno')],
            ['value' => self::MEDIUM, 'label' => __('Médio')],
            ['value' => self::LARGE, 'label' => __('Grande')],
            ['value' => self::XLARGE, 'label' => __('Extra Grande')]
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