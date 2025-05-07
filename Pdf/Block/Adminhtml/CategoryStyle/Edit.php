<?php
namespace Cortex\Pdf\Block\Adminhtml\CategoryStyle;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Cortex_Pdf';
        $this->_controller = 'adminhtml_categorystyle';
        $this->_mode = 'edit';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Salvar Estilo'));
        $this->buttonList->update('delete', 'label', __('Excluir Estilo'));
    }

    public function getHeaderText()
    {
        return __('Editar Estilo por Categoria');
    }
}
