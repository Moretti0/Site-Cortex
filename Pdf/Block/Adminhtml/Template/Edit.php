<?php
namespace Cortex\Pdf\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize template edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'template_id';
        $this->_blockGroup = 'Cortex_Pdf';
        $this->_controller = 'adminhtml_template';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Salvar Template'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Salvar e Continuar Editando'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );

        $this->buttonList->update('delete', 'label', __('Excluir Template'));
    }

    /**
     * Retrieve text for header element
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('cortex_pdf_template')->getId()) {
            return __("Editar Template '%1'", $this->escapeHtml($this->_coreRegistry->registry('cortex_pdf_template')->getName()));
        } else {
            return __('Novo Template');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
}