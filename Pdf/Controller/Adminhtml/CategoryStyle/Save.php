<?php
namespace Cortex\Pdf\Controller\Adminhtml\CategoryStyle;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Cortex\Pdf\Model\Category\StyleFactory;
use Cortex\Pdf\Model\ResourceModel\Category\Style as ResourceStyle;

class Save extends Action
{
    protected $styleFactory;
    protected $resource;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        StyleFactory $styleFactory,
        ResourceStyle $resource,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->styleFactory = $styleFactory;
        $this->resource = $resource;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->styleFactory->create();
            if (!empty($data['id'])) {
                $this->resource->load($model, $data['id']);
            }
            $model->addData($data);

            try {
                $this->resource->save($model);
                $this->messageManager->addSuccessMessage(__('Estilo salvo com sucesso.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['id']]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
