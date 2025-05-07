<?php
namespace Cortex\Pdf\Block\Adminhtml\Template\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\ObjectManagerInterface;

class Form extends Generic
{
    /**
     * @var WysiwygConfig
     */
    protected $_wysiwygConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param WysiwygConfig $wysiwygConfig
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        WysiwygConfig $wysiwygConfig,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        // Obter o modelo do registro
        $model = $this->_coreRegistry->registry('cortex_pdf_template');

        // Se o modelo não existir, criar um modelo vazio
        if (!$model) {
            $model = $this->_coreRegistry->registry('pdf_template');

            // Se ainda não existir, criar um novo
            if (!$model) {
                $model = $this->objectManager->create(\Cortex\Pdf\Model\Template::class);
            }
        }

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('template_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Informações do Template'), 'class' => 'fieldset-wide']
        );

        // Verificar se o modelo existe E tem um ID antes de adicionar o campo hidden
        if ($model) {
            $id = null;
            if (is_object($model) && method_exists($model, 'getId')) {
                $id = $model->getId();
            } elseif (is_array($model) && isset($model['id'])) {
                $id = $model['id'];
            }

            if ($id) {
                $fieldset->addField(
                    'id',
                    'hidden',
                    ['name' => 'id']
                );
            }
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Nome'),
                'title' => __('Nome'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Descrição'),
                'title' => __('Descrição'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Conteúdo'),
                'title' => __('Conteúdo'),
                'required' => true,
                'config' => $this->_wysiwygConfig->getConfig([
                    'add_variables' => true,
                    'add_widgets' => true,
                    'add_images' => true,
                    'height' => '500px'
                ])
            ]
        );

        // Definir os valores do formulário apenas se o modelo tiver dados
        if ($model) {
            if (is_object($model) && method_exists($model, 'getData')) {
                $form->setValues($model->getData());
            } elseif (is_array($model)) {
                $form->setValues($model);
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getLayout()->getBlock('head')) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}