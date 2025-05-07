<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Orientation implements ArrayInterface
{
    const PORTRAIT = 'P';
    const LANDSCAPE = 'L';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PORTRAIT, 'label' => __('Retrato')],
            ['value' => self::LANDSCAPE, 'label' => __('Paisagem')]
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