<?php
namespace Cortex\Pdf\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductFactory;
use Cortex\Pdf\Model\PdfGenerator;
use Psr\Log\LoggerInterface;

class Preview extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var PdfGenerator
     */
    protected $pdfGenerator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductFactory $productFactory
     * @param PdfGenerator $pdfGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductFactory $productFactory,
        PdfGenerator $pdfGenerator,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->pdfGenerator = $pdfGenerator;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $productId = $this->getRequest()->getParam('id');
            $template = $this->getRequest()->getParam('template');

            if (!$productId || !$template) {
                throw new \Exception(__('Parâmetros inválidos'));
            }

            $product = $this->productFactory->create()->load($productId);
            if (!$product->getId()) {
                throw new \Exception(__('Produto não encontrado'));
            }

            $pdfContent = $this->pdfGenerator->generate($product, $template, true);
            $fileName = sprintf('preview_%s_%s.pdf', $product->getSku(), $template);
            $filePath = $this->pdfGenerator->savePdf($pdfContent, $fileName);

            return $result->setData([
                'success' => true,
                'previewUrl' => $this->getUrl('cortex_pdf/product/download', [
                    'file' => base64_encode($filePath)
                ])
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao gerar preview do PDF: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}