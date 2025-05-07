<?php
namespace Cortex\Pdf\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Cortex\Pdf\Model\PdfTemplate;

class Save extends Action
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
     * Save template action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $data = $this->getRequest()->getPostValue();

            if (empty($data)) {
                throw new \Exception(__('Dados do template não fornecidos.'));
            }

            $templateId = $this->pdfTemplate->saveTemplate($data);

            if ($templateId) {
                $result->setData([
                    'success' => true,
                    'message' => __('Template salvo com sucesso.'),
                    'template_id' => $templateId
                ]);
            } else {
                throw new \Exception(__('Erro ao salvar template.'));
            }
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