<?php
namespace Cortex\Pdf\Controller\Adminhtml\TemplateConfig;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Cortex\Pdf\Model\Template\ConfigFactory;
use Cortex\Pdf\Model\ResourceModel\Template\Config as ResourceConfig;

class Save extends Action
{
    protected $configFactory;
    protected $resource;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        ConfigFactory $configFactory,
        ResourceConfig $resource,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->configFactory = $configFactory;
        $this->resource = $resource;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->configFactory->create();
            if (!empty($data['template_id'])) {
                $this->resource->load($model, $data['template_id']);
            }
            $model->addData($data);

            try {
                $this->resource->save($model);
                $this->messageManager->addSuccessMessage(__('Template salvo com sucesso.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['template_id']]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
