<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ButtonIcons implements ArrayInterface
{
    const NONE = 'none';
    const EYE = 'eye';
    const DOCUMENT = 'document';
    const DOWNLOAD = 'download';
    const PRINT = 'print';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('Sem Ícone')],
            ['value' => self::EYE, 'label' => __('Olho')],
            ['value' => self::DOCUMENT, 'label' => __('Documento')],
            ['value' => self::DOWNLOAD, 'label' => __('Download')],
            ['value' => self::PRINT, 'label' => __('Imprimir')]
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