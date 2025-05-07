<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PageSize implements OptionSourceInterface
{
    /**
     * Retorna opções para tamanho de página
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'A4', 'label' => __('A4')],
            ['value' => 'A3', 'label' => __('A3')],
            ['value' => 'Letter', 'label' => __('Carta')],
            ['value' => 'Legal', 'label' => __('Ofício')],
            ['value' => 'Tabloid', 'label' => __('Tablóide')],
            ['value' => 'Executive', 'label' => __('Executivo')]
        ];
    }
}