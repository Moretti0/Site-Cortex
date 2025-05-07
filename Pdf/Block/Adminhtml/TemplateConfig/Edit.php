<?php
namespace Cortex\Pdf\Block\Adminhtml\TemplateConfig;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry;

    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'template_id';
        $this->_blockGroup = 'Cortex_Pdf';
        $this->_controller = 'adminhtml_templateConfig';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Salvar Template'));
        $this->buttonList->update('delete', 'label', __('Excluir Template'));
    }

    public function getHeaderText()
    {
        return __('Editar Template de PDF');
    }
}
