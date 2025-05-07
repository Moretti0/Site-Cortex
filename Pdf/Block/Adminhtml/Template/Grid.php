<?php
namespace Cortex\Pdf\Block\Adminhtml\Template;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Cortex\Pdf\Model\PdfTemplate;

class Grid extends Template
{
    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param Context $context
     * @param PdfTemplate $pdfTemplate
     * @param array $data
     */
    public function __construct(
        Context $context,
        PdfTemplate $pdfTemplate,
        array $data = []
    ) {
        $this->pdfTemplate = $pdfTemplate;
        parent::__construct($context, $data);
    }

    /**
     * Retorna a lista de templates
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->pdfTemplate->listTemplates();
    }

    /**
     * Retorna a URL para criar um novo template
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('cortex_pdf/template/new');
    }

    /**
     * Retorna a URL para editar um template
     *
     * @param string $name
     * @return string
     */
    public function getEditUrl($name)
    {
        return $this->getUrl('cortex_pdf/template/edit', ['name' => $name]);
    }

    /**
     * Retorna a URL para excluir um template
     *
     * @param string $name
     * @return string
     */
    public function getDeleteUrl($name)
    {
        return $this->getUrl('cortex_pdf/template/delete', ['name' => $name]);
    }
}