<?php
namespace Cortex\Pdf\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Cortex\Pdf\Model\PdfTemplate;

class Edit extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PdfTemplate $pdfTemplate
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PdfTemplate $pdfTemplate
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pdfTemplate = $pdfTemplate;
    }

    /**
     * Edit template action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $templateId = $this->getRequest()->getParam('id');

            if (!$templateId) {
                throw new \Exception(__('ID do template não fornecido.'));
            }

            $template = $this->pdfTemplate->getTemplateById($templateId);

            if (!$template) {
                throw new \Exception(__('Template não encontrado.'));
            }

            $result->setData([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Check admin permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cortex_Pdf::templates');
    }
}