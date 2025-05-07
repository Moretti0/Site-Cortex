<?php
namespace Cortex\Pdf\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Cortex\Pdf\Api\PdfGeneratorInterface;
use Psr\Log\LoggerInterface;

class View extends Action
{
    /**
     * @var PdfGeneratorInterface
     */
    protected $pdfGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param PdfGeneratorInterface $pdfGenerator
     * @param FileFactory $fileFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PdfGeneratorInterface $pdfGenerator,
        FileFactory $fileFactory,
        LoggerInterface $logger
    ) {
        $this->pdfGenerator = $pdfGenerator;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * View PDF action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $sku = $this->getRequest()->getParam('sku');
            if (!$sku) {
                throw new LocalizedException(__('SKU do produto não especificado.'));
            }

            $templateId = $this->getRequest()->getParam('template_id');

            $pdfContent = $this->pdfGenerator->generateByProductSku($sku, true, $templateId);

            $fileName = 'produto_' . $this->sanitizeFilename($sku) . '.pdf';

            return $this->fileFactory->create(
                $fileName,
                $pdfContent,
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'application/pdf',
                null,
                false
            );

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Produto não encontrado.'));
            $this->logger->error($e->getMessage());
            return $this->_redirect('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error($e->getMessage());
            return $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Um erro ocorreu ao gerar o PDF. Por favor, tente novamente.'));
            $this->logger->critical($e->getMessage());
            return $this->_redirect('*/*/');
        }
    }

    /**
     * Sanitiza o nome do arquivo
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }
}