<?php
namespace Cortex\Pdf\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Registry;

class PdfButton extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Verifica se o módulo está habilitado
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
     * Obtém o texto do botão
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
     * Obtém a URL do PDF
     *
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->getUrl('cortex_pdf/product/generate', [
            'id' => $this->getRequest()->getParam('id')
        ]);
    }

    /**
     * Obtém a classe CSS do botão
     *
     * @return string
     */
    public function getCssClass()
    {
        return $this->getData('css_class') ?: '';
    }

    /**
     * Obtém o produto atual
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}