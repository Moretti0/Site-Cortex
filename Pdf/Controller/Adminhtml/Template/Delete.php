<?php
namespace Cortex\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Cortex\Pdf\Model\PdfTemplate;

class Delete extends Action
{
    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param Context $context
     * @param PdfTemplate $pdfTemplate
     */
    public function __construct(
        Context $context,
        PdfTemplate $pdfTemplate
    ) {
        parent::__construct($context);
        $this->pdfTemplate = $pdfTemplate;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cortex_Pdf::templates');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $templateId = $this->getRequest()->getParam('id');

        if ($templateId) {
            try {
                $this->pdfTemplate->deleteTemplate($templateId);
                $this->messageManager->addSuccessMessage(__('Template excluído com sucesso.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $templateId]);
            }
        }
        $this->messageManager->addErrorMessage(__('Template não encontrado.'));
        return $resultRedirect->setPath('*/*/');
    }
}