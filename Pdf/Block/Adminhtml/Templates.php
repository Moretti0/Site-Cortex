<?php
namespace Cortex\Pdf\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cortex\Pdf\Model\PdfTemplate;

class Templates extends Template
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
     * Get templates
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->pdfTemplate->getTemplates();
    }
}