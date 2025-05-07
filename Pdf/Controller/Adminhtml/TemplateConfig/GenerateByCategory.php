<?php
namespace Cortex\Pdf\Controller\Adminhtml\TemplateConfig;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class GenerateByCategory extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cortex_Pdf::template_config');
        $resultPage->getConfig()->getTitle()->prepend(__('Gerar PDFs por Categoria'));
        return $resultPage;
    }
}
