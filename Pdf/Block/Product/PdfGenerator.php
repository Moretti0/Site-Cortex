<?php
namespace Cortex\Pdf\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Cortex\Pdf\Model\PdfTemplate;

class PdfGenerator extends Template {
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param PdfTemplate $pdfTemplate
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        PdfTemplate $pdfTemplate,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pdfTemplate = $pdfTemplate;
        parent::__construct($context, $data);
    }

    /**
     * Definir produto
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Obter produto
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * Verificar se o módulo está habilitado
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Obter templates disponíveis
     *
     * @return array
     */
    public function getAvailableTemplates(): array
    {
        return $this->pdfTemplate->getAvailableTemplates();
    }

    /**
     * Obter URL de geração de PDF
     *
     * @param string $templateName
     * @return string
     */
    public function getPdfGenerationUrl(string $templateName): string
    {
        return $this->getUrl('cortex_pdf/product/generate', [
            'id' => $this->getProduct()->getId(),
            'template' => $templateName
        ]);
    }

    /**
     * Obter URL de preview de PDF
     *
     * @param string $templateName
     * @return string
     */
    public function getPdfPreviewUrl(string $templateName): string
    {
        return $this->getUrl('cortex_pdf/product/preview', [
            'id' => $this->getProduct()->getId(),
            'template' => $templateName
        ]);
    }

    /**
     * Obter texto do botão
     *
     * @return string
     */
    public function getButtonText(): string
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/general/button_text',
            ScopeInterface::SCOPE_STORE
        ) ?: __('Gerar PDF');
    }

    /**
     * Obter posição do botão
     *
     * @return string
     */
    public function getButtonPosition(): string
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/general/button_position',
            ScopeInterface::SCOPE_STORE
        ) ?: 'after_add_to_cart';
    }

    /**
     * Verificar se deve mostrar o botão
     *
     * @return bool
     */
    public function shouldShowButton()
    {
        return $this->isEnabled() && $this->getProduct() && !empty($this->getAvailableTemplates());
    }
} 