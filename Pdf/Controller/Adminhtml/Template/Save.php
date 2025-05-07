<?php
namespace Cortex\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Cortex\Pdf\Model\PdfTemplate;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @var PdfTemplate
     */
    protected PdfTemplate $pdfTemplate;

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
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Cortex_Pdf::templates');
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $templateId = $this->pdfTemplate->saveTemplate($data);
                $this->messageManager->addSuccessMessage(__('Template salvo com sucesso.'));

                if ($this->getRequest()->getParam('back')) {
                    $id = $templateId ?: ($data['id'] ?? null);
                    if ($id) {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $this->messageManager->addNoticeMessage(__('Não foi possível continuar editando.'));
                        return $resultRedirect->setPath('*/*/');
                    }
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                } else {
                    return $resultRedirect->setPath('*/*/');
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}