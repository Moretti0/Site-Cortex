<?php
namespace Cortex\Pdf\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ImageType implements ArrayInterface
{
    const TYPE_NONE = 0;
    const TYPE_DIMENSIONAL = 1;
    const TYPE_BACK_PANEL = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_NONE, 'label' => __('Nenhum')],
            ['value' => self::TYPE_DIMENSIONAL, 'label' => __('Dimensional')],
            ['value' => self::TYPE_BACK_PANEL, 'label' => __('Painel Traseiro')]
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