<?php
namespace Cortex\Pdf\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Cortex\Pdf\Model\PdfTemplate;
use Cortex\Pdf\Model\WkhtmltopdfGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class GeneratePdf extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @var WkhtmltopdfGenerator
     */
    protected $pdfGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ResultLayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PdfTemplate $pdfTemplate
     * @param WkhtmltopdfGenerator $pdfGenerator
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param LayoutFactory $layoutFactory
     * @param ResultLayoutFactory $resultLayoutFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PdfTemplate $pdfTemplate,
        WkhtmltopdfGenerator $pdfGenerator,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->pdfTemplate = $pdfTemplate;
        $this->pdfGenerator = $pdfGenerator;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Generate PDF action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->getParam('isAjax') || $this->getRequest()->isXmlHttpRequest();

        try {
            // Buscar o produto do registro ou pelo parâmetro da requisição
            $product = $this->registry->registry('current_product');

            if (!$product) {
                $productId = $this->getRequest()->getParam('product_id');
                if ($productId) {
                    try {
                        $product = $this->productRepository->getById($productId);
                    } catch (\Exception $e) {
                        $this->logger->error('Erro ao buscar produto: ' . $e->getMessage());
                        throw new \Exception(__('Produto não encontrado.'));
                    }
                } else {
                    throw new \Exception(__('Produto não especificado.'));
                }
            }

            // Obter configurações do admin
            $templateType = $this->scopeConfig->getValue(
                'cortex_pdf/general/template_type',
                ScopeInterface::SCOPE_STORE
            );

            $paperSize = $this->scopeConfig->getValue(
                'cortex_pdf/pdf_config/paper_size',
                ScopeInterface::SCOPE_STORE
            );

            $orientation = $this->scopeConfig->getValue(
                'cortex_pdf/pdf_config/orientation',
                ScopeInterface::SCOPE_STORE
            );

            $showSpecifications = $this->scopeConfig->getValue(
                'cortex_pdf/pdf_config/show_specifications',
                ScopeInterface::SCOPE_STORE
            );

            $showGallery = $this->scopeConfig->getValue(
                'cortex_pdf/images/show_gallery',
                ScopeInterface::SCOPE_STORE
            );

            $maxGalleryImages = $this->scopeConfig->getValue(
                'cortex_pdf/images/max_gallery_images',
                ScopeInterface::SCOPE_STORE
            );

            $qrCodeEnabled = $this->scopeConfig->getValue(
                'cortex_pdf/qrcode/enabled',
                ScopeInterface::SCOPE_STORE
            );

            // Carregar o template
            $template = $this->pdfTemplate->loadTemplate($templateType);

            if (!$template) {
                throw new \Exception(__('Template não encontrado.'));
            }

            // Preparar dados do produto
            $productData = $this->prepareProductData($product);

            // Adicionar imagens do produto se a galeria estiver habilitada
            if ($showGallery) {
                $gallery = [];
                $images = $product->getMediaGalleryImages();
                $count = 0;
                foreach ($images as $image) {
                    if ($count >= $maxGalleryImages) {
                        break;
                    }
                    $gallery[] = [
                        'url' => $image->getUrl(),
                        'label' => $image->getLabel()
                    ];
                    $count++;
                }
                $productData['gallery'] = $gallery;
            }

            // Gerar QR Code se estiver habilitado
            if ($qrCodeEnabled) {
                $qrCode = $this->generateQrCode($product);
                $productData['qrcode'] = $qrCode;
            }

            // Substituir variáveis no template
            $content = $this->processTemplate($template['content'], $productData);

            // Adicionar QR Code se estiver habilitado
            if ($qrCodeEnabled && isset($productData['qrcode'])) {
                $content = str_replace('{{qrcode}}', $productData['qrcode'], $content);
            }

            // Gerar PDF
            $pdfContent = $this->generatePdf($content, [
                'paper_size' => $paperSize,
                'orientation' => $orientation
            ]);

            // Se for uma requisição AJAX, salvar o PDF temporariamente e retornar uma URL para download
            if ($isAjax) {
                $fileName = 'produto_' . $product->getSku() . '_' . uniqid() . '.pdf';
                $filePath = 'cortex_pdf/temp/' . $fileName;

                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

                try {
                    // Garantir que o diretório exista
                    $mediaDirectory->create('cortex_pdf/temp');

                    // Salvar o arquivo
                    $mediaDirectory->writeFile($filePath, $pdfContent);

                    // Construir URL para download
                    $downloadUrl = $this->_url->getUrl('cortex_pdf/product/download', [
                        'file' => base64_encode($filePath)
                    ]);

                    // Retornar resposta JSON
                    $resultJson = $this->resultJsonFactory->create();
                    return $resultJson->setData([
                        'success' => true,
                        'download_url' => $downloadUrl,
                        'message' => __('PDF gerado com sucesso.')
                    ]);
                } catch (\Exception $e) {
                    $this->logger->error('Erro ao salvar PDF temporário: ' . $e->getMessage());
                    throw new \Exception(__('Não foi possível salvar o PDF temporariamente.'));
                }
            } else {
                // Download direto para requisições não-AJAX
                $fileName = 'produto_' . $product->getSku() . '.pdf';

                $this->_prepareDownloadResponse($fileName, $pdfContent, 'application/pdf');
                return $this->getResponse();
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao gerar PDF: ' . $e->getMessage());

            if ($isAjax) {
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/');
            }
        }
    }

    /**
     * Prepare product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function prepareProductData($product)
    {
        return [
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'description' => $product->getDescription(),
            'short_description' => $product->getShortDescription(),
            'price' => $product->getPrice(),
            'processor_model' => $product->getData('processor_model'),
            'processor_speed' => $product->getData('processor_speed'),
            'processor_cores' => $product->getData('processor_cores'),
            'processor_cache' => $product->getData('processor_cache'),
            'motherboard_model' => $product->getData('motherboard_model'),
            'motherboard_chipset' => $product->getData('motherboard_chipset'),
            'motherboard_socket' => $product->getData('motherboard_socket'),
            'motherboard_memory' => $product->getData('motherboard_memory'),
            'motherboard_slots' => $product->getData('motherboard_slots'),
            'memory_type' => $product->getData('memory_type'),
            'memory_capacity' => $product->getData('memory_capacity'),
            'memory_speed' => $product->getData('memory_speed'),
            'memory_slots' => $product->getData('memory_slots'),
            'storage_type' => $product->getData('storage_type'),
            'storage_capacity' => $product->getData('storage_capacity'),
            'storage_interface' => $product->getData('storage_interface'),
            'gpu_model' => $product->getData('gpu_model'),
            'gpu_memory' => $product->getData('gpu_memory'),
            'gpu_interface' => $product->getData('gpu_interface'),
            'usb_ports' => $product->getData('usb_ports'),
            'network_ports' => $product->getData('network_ports'),
            'audio_ports' => $product->getData('audio_ports'),
            'wifi_specs' => $product->getData('wifi_specs'),
            'bluetooth_specs' => $product->getData('bluetooth_specs'),
            'power_supply' => $product->getData('power_supply'),
            'power_watts' => $product->getData('power_watts'),
            'power_efficiency' => $product->getData('power_efficiency'),
            'dimensions_height' => $product->getData('dimensions_height'),
            'dimensions_width' => $product->getData('dimensions_width'),
            'dimensions_depth' => $product->getData('dimensions_depth'),
            'weight' => $product->getData('weight'),
            'warranty_period' => $product->getData('warranty_period'),
            'warranty_terms' => $product->getData('warranty_terms')
        ];
    }

    /**
     * Process template with product data
     *
     * @param string $content
     * @param array $productData
     * @return string
     */
    protected function processTemplate($content, $productData)
    {
        foreach ($productData as $key => $value) {
            if (is_array($value)) {
                // Tratar arrays (como galeria de imagens)
                if ($key === 'gallery') {
                    $galleryHtml = '';
                    foreach ($value as $image) {
                        $galleryHtml .= '<div class="gallery-item">';
                        $galleryHtml .= '<img src="' . $image['url'] . '" alt="' . $image['label'] . '">';
                        $galleryHtml .= '</div>';
                    }
                    $content = str_replace('{{product.gallery}}', $galleryHtml, $content);
                }
            } else {
                $content = str_replace('{{product.' . $key . '}}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Generate QR Code
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function generateQrCode($product)
    {
        try {
            $qrCodeSize = $this->scopeConfig->getValue(
                'cortex_pdf/qrcode/size',
                ScopeInterface::SCOPE_STORE
            ) ?: 100;

            $qrCode = new \Endroid\QrCode\QrCode($product->getProductUrl());
            $qrCode->setSize($qrCodeSize);
            $qrCode->setMargin(10);

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            // Converter para base64 para incluir no HTML
            $dataUri = $result->getDataUri();

            return '<img src="' . $dataUri . '" alt="QR Code" class="product-qrcode">';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Generate PDF with settings
     *
     * @param string $content HTML content
     * @param array $settings PDF settings
     * @return string PDF content
     */
    protected function generatePdf($content, $settings)
    {
        try {
            // Verificar se o método específico existe
            if (method_exists($this->pdfGenerator, 'generateWithSettings')) {
                return $this->pdfGenerator->generateWithSettings($content, $settings);
            }

            // Fallback para o método genérico
            return $this->pdfGenerator->generateFromHtml($content, $settings);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao gerar PDF: ' . $e->getMessage());
            throw $e;
        }
    }
}