<?php
namespace Cortex\Pdf\Controller\Adminhtml\TemplateConfig;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Cortex\Pdf\Api\TemplateConfigRepositoryInterface;
use Cortex\Pdf\Model\PdfGenerator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class RunGenerateByCategory extends Action
{
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $templateRepository;
    protected $pdfGenerator;
    protected $resultRedirectFactory;
    protected $messageManager;

    public function __construct(
        Action\Context $context,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        TemplateConfigRepositoryInterface $templateRepository,
        PdfGenerator $pdfGenerator,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->templateRepository = $templateRepository;
        $this->pdfGenerator = $pdfGenerator;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $categoryId = (int)($data['category_id'] ?? 0);
        $templateId = (int)($data['template_id'] ?? 0);

        if (!$categoryId || !$templateId) {
            $this->messageManager->addErrorMessage(__('Informe ID da categoria e do template.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/generateByCategory');
        }

        try {
            $category = $this->categoryRepository->get($categoryId);
            $template = $this->templateRepository->getById($templateId);
            $collection = $this->productCollectionFactory->create()
                ->addCategoryFilter($category)
                ->addAttributeToSelect('*');

            $gerados = 0;
            foreach ($collection as $product) {
                $this->pdfGenerator->generate($product->getId(), $template);
                $gerados++;
            }

            $this->messageManager->addSuccessMessage(__('%1 PDFs gerados com sucesso.', $gerados));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Erro ao gerar PDFs: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/generateByCategory');
    }
}
