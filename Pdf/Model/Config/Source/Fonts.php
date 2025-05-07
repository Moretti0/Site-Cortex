<?php
namespace Cortex\Pdf\Model\Config\Source;

class Fonts implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'helvetica', 'label' => __('Helvetica')],
            ['value' => 'times', 'label' => __('Times New Roman')],
            ['value' => 'courier', 'label' => __('Courier')],
            ['value' => 'dejavusans', 'label' => __('DejaVu Sans')],
            ['value' => 'dejavuserif', 'label' => __('DejaVu Serif')],
            ['value' => 'freesans', 'label' => __('Free Sans')],
            ['value' => 'freeserif', 'label' => __('Free Serif')],
            ['value' => 'freemono', 'label' => __('Free Mono')]
        ];
    }
}