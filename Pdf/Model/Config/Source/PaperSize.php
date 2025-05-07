<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaperSize implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'A4', 'label' => __('A4 (210 x 297 mm)')],
            ['value' => 'A5', 'label' => __('A5 (148 x 210 mm)')],
            ['value' => 'Letter', 'label' => __('Carta (216 x 279 mm)')],
            ['value' => 'Legal', 'label' => __('Ofício (216 x 356 mm)')],
            ['value' => 'Executive', 'label' => __('Executivo (184 x 267 mm)')]
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