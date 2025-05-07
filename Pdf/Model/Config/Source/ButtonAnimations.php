<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ButtonAnimations implements ArrayInterface
{
    const NONE = 'none';
    const FADE = 'fade';
    const SCALE = 'scale';
    const SLIDE = 'slide';
    const BOUNCE = 'bounce';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('Sem Animação')],
            ['value' => self::FADE, 'label' => __('Fade')],
            ['value' => self::SCALE, 'label' => __('Escala')],
            ['value' => self::SLIDE, 'label' => __('Deslizar')],
            ['value' => self::BOUNCE, 'label' => __('Quicar')]
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