<?php
namespace Cortex\Pdf\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\UrlInterface as Url;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Cortex\Pdf\Model\PdfGenerator;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Generate implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param FormKeyValidator $formKeyValidator
     * @param Url $url
     * @param ProductRepositoryInterface $productRepository
     * @param PdfGenerator $pdfGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        FormKeyValidator $formKeyValidator,
        Url $url,
        ProductRepositoryInterface $productRepository,
        PdfGenerator $pdfGenerator,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->url = $url;
        $this->productRepository = $productRepository;
        $this->pdfGenerator = $pdfGenerator;
        $this->logger = $logger;
    }

    /**
     * Execute action para gerar PDF via Ajax
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            // Validar o form key para proteger contra CSRF
            if (!$this->formKeyValidator->validate($this->request)) {
                throw new LocalizedException(__('Token de formulário inválido'));
            }

            // Obter IDs do produto e template
            $productId = (int)$this->request->getParam('product_id');
            if (!$productId) {
                throw new LocalizedException(__('ID do produto não fornecido'));
            }

            $templateId = (int)$this->request->getParam('template_id', 0);

            // Verificar se o produto existe
            $product = $this->productRepository->getById($productId);

            // Gerar o PDF
            $pdfFile = $this->pdfGenerator->generateProductPdf($product, $templateId);

            // Codificar o caminho do arquivo para URL segura
            $encodedFilePath = base64_encode($pdfFile);

            return $result->setData([
                'success' => true,
                'message' => __('PDF gerado com sucesso'),
                'file' => $encodedFilePath,
                'download_url' => $this->url->getUrl('cortex_pdf/product/download', ['file' => $encodedFilePath])
            ]);

        } catch (LocalizedException $e) {
            $this->logger->error('Erro ao gerar PDF via Ajax: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Produto não encontrado ao gerar PDF: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('Produto não encontrado')
            ]);
        } catch (\Exception $e) {
            $this->logger->critical('Erro inesperado ao gerar PDF: ' . $e->getMessage(), ['exception' => $e]);
            return $result->setData([
                'success' => false,
                'message' => __('Ocorreu um erro ao gerar o PDF. Por favor, tente novamente.')
            ]);
        }
    }
}