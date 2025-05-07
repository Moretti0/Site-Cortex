<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class QrCodePosition implements ArrayInterface
{
    const POSITION_TOP_LEFT = 'top-left';
    const POSITION_TOP_RIGHT = 'top-right';
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::POSITION_TOP_LEFT, 'label' => __('Superior Esquerdo')],
            ['value' => self::POSITION_TOP_RIGHT, 'label' => __('Superior Direito')],
            ['value' => self::POSITION_BOTTOM_LEFT, 'label' => __('Inferior Esquerdo')],
            ['value' => self::POSITION_BOTTOM_RIGHT, 'label' => __('Inferior Direito')]
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