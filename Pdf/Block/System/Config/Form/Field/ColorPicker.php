<?php
namespace Cortex\Pdf\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getValue();

        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/colorpicker/js/colorpicker"], function($) {
                $(document).ready(function() {
                    $("#' . $element->getHtmlId() . '").css("backgroundColor", "' . $value . '");

                    $("#' . $element->getHtmlId() . '").ColorPicker({
                        color: "' . $value . '",
                        onChange: function(hsb, hex, rgb) {
                            $("#' . $element->getHtmlId() . '").css("backgroundColor", "#" + hex);
                            $("#' . $element->getHtmlId() . '").val("#" + hex);
                        }
                    });
                });
            });
        </script>';

        return $html;
    }
}