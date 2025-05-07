<?php
namespace Cortex\Pdf\Controller\Adminhtml\CategoryStyle;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cortex_Pdf::category_style');
        $resultPage->getConfig()->getTitle()->prepend(__('Estilos por Categoria'));
        return $resultPage;
    }
}
