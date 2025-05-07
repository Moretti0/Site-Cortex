<?php
namespace Cortex\Pdf\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class PdfButton extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HttpContext $httpContext,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get button text
     *
     * @return string
     */
    public function getButtonText()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/general/button_text',
            ScopeInterface::SCOPE_STORE
        ) ?: __('Gerar PDF');
    }

    /**
     * Get button CSS classes
     *
     * @return string
     */
    public function getButtonCssClasses()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/general/button_css_classes',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get PDF URL
     *
     * @return string
     */
    public function getPdfUrl()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $this->getUrl('cortex_pdf/product/generate', [
            'id' => $product->getId()
        ]);
    }

    /**
     * Check if should use theme button
     *
     * @return bool
     */
    public function useThemeButton()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/general/use_theme_button',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Obtém a URL para visualização do PDF
     *
     * @return string
     */
    public function getViewPdfUrl()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $this->getUrl('cortex_pdf/product/preview', [
            'sku' => $product->getSku(),
            '_secure' => true
        ]);
    }

    /**
     * Obtém a cor do botão
     *
     * @return string
     */
    public function getButtonColor()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/button_design/button_color',
            ScopeInterface::SCOPE_STORE
        ) ?: 'blue';
    }

    /**
     * Obtém o tamanho do botão
     *
     * @return string
     */
    public function getButtonSize()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/button_design/button_size',
            ScopeInterface::SCOPE_STORE
        ) ?: 'medium';
    }

    /**
     * Obtém a animação do botão
     *
     * @return string
     */
    public function getButtonAnimation()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/button_design/button_animation',
            ScopeInterface::SCOPE_STORE
        ) ?: 'none';
    }

    /**
     * Obtém o ícone do botão
     *
     * @return string
     */
    public function getButtonIcon()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/button_design/button_icon',
            ScopeInterface::SCOPE_STORE
        ) ?: 'eye';
    }

    /**
     * Verifica se é um dispositivo móvel
     *
     * @return bool
     */
    public function isMobile()
    {
        return $this->httpContext->getValue('is_mobile') === true;
    }

    /**
     * Obtém o tamanho do botão em mobile
     *
     * @return string
     */
    public function getMobileButtonSize()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/responsiveness/mobile_button_size',
            ScopeInterface::SCOPE_STORE
        ) ?: 'medium';
    }

    /**
     * Obtém a posição do botão em mobile
     *
     * @return string
     */
    public function getMobileButtonPosition()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/responsiveness/mobile_button_position',
            ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Obtém o rótulo ARIA
     *
     * @return string
     */
    public function getAriaLabel()
    {
        return $this->scopeConfig->getValue(
            'cortex_pdf/accessibility/aria_label',
            ScopeInterface::SCOPE_STORE
        ) ?: $this->getButtonText();
    }

    /**
     * Verifica se a navegação por teclado está habilitada
     *
     * @return bool
     */
    public function isKeyboardNavigationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/accessibility/keyboard_navigation',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled() || !$this->getProduct()) {
            return '';
        }
        return parent::_toHtml();
    }
}