<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PageOrientation implements OptionSourceInterface
{
    /**
     * Retorna opções para orientação de página
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'portrait', 'label' => __('Retrato')],
            ['value' => 'landscape', 'label' => __('Paisagem')]
        ];
    }
}