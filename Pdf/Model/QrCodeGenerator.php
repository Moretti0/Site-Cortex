<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeGenerator
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * Gera o QR Code para o produto
     *
     * @param string $sku
     * @return string
     */
    public function generate($sku)
    {
        try {
            if (!$this->isEnabled()) {
                return '';
            }

            $url = $this->getProductUrl($sku);
            if (!$url) {
                return '';
            }

            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 5,
                'imageBase64' => true,
                'moduleValues' => [
                    // finder
                    1536 => '#000000', // dark (true)
                    6 => '#FFFFFF',   // light (false)
                    // alignment
                    2560 => '#000000',
                    10 => '#FFFFFF',
                    // timing
                    3072 => '#000000',
                    12 => '#FFFFFF',
                    // format
                    3584 => '#000000',
                    14 => '#FFFFFF',
                    // version
                    4096 => '#000000',
                    16 => '#FFFFFF',
                    // data
                    1024 => '#000000',
                    4 => '#FFFFFF',
                    // darkmodule
                    512 => '#000000',
                    // separator
                    8 => '#FFFFFF',
                    // quietzone
                    18 => '#FFFFFF',
                ],
            ]);

            $qrcode = new QRCode($options);
            return $qrcode->render($url);

        } catch (\Exception $e) {
            $this->logger->error('Erro ao gerar QR Code: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Verifica se o QR Code está habilitado
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/qrcode/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Obtém a URL do produto
     *
     * @param string $sku
     * @return string
     */
    protected function getProductUrl($sku)
    {
        try {
            return $this->urlBuilder->getUrl('catalog/product/view', [
                'sku' => $sku,
                '_secure' => true
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter URL do produto: ' . $e->getMessage());
            return '';
        }
    }
}