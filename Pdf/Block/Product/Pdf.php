<?php
namespace Cortex\Pdf\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Helper\Image;
use Magento\Store\Model\StoreManagerInterface;

class Pdf extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FilterManager $filterManager
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FilterManager $filterManager,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->filterManager = $filterManager;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Retorna o produto atual
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Retorna a URL do logo
     *
     * @return string|null
     */
    public function getLogo()
    {
        $logoPath = $this->_scopeConfig->getValue(
            'cortex_pdf/pdf_config/logo',
            ScopeInterface::SCOPE_STORE
        );

        if ($logoPath) {
            return $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'cortex/pdf/logo/' . $logoPath;
        }

        return null;
    }

    /**
     * Retorna a URL da imagem principal do produto
     *
     * @return string|null
     */
    public function getMainImage()
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        return $this->imageHelper->init($product, 'product_page_image_large')
            ->setImageFile($product->getImage())
            ->getUrl();
    }

    /**
     * Filtra o conteúdo HTML
     *
     * @param string $content
     * @return string
     */
    public function filterContent($content)
    {
        return $this->filterManager->stripTags(
            $content,
            [
                'allowableTags' => '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><b><i>',
                'escape' => false
            ]
        );
    }

    /**
     * Verifica se deve mostrar especificações
     *
     * @return bool
     */
    public function showSpecifications()
    {
        return $this->_scopeConfig->isSetFlag(
            'cortex_pdf/pdf_config/show_specifications',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retorna os atributos do produto
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        $attributes = $this->_scopeConfig->getValue(
            'cortex_pdf/pdf_config/attributes',
            ScopeInterface::SCOPE_STORE
        );

        if (empty($attributes)) {
            return [];
        }

        if (!is_array($attributes)) {
            $attributes = explode(',', $attributes);
        }

        $result = [];
        foreach ($attributes as $attributeCode) {
            $attribute = $product->getResource()->getAttribute($attributeCode);
            if (!$attribute) {
                continue;
            }

            $value = $product->getData($attributeCode);
            if ($value === null || $value === '') {
                continue;
            }

            $label = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();

            if ($attribute->usesSource()) {
                $value = $attribute->getSource()->getOptionText($value);
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $result[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        return $result;
    }

    /**
     * Verifica se deve mostrar galeria
     *
     * @return bool
     */
    public function showGallery()
    {
        return $this->_scopeConfig->isSetFlag(
            'cortex_pdf/images/show_gallery',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retorna as imagens da galeria
     *
     * @return array
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        $images = [];
        $gallery = $product->getMediaGalleryImages();
        if (!$gallery) {
            return [];
        }

        $maxImages = (int) $this->_scopeConfig->getValue(
            'cortex_pdf/images/max_gallery_images',
            ScopeInterface::SCOPE_STORE
        );

        $count = 0;
        foreach ($gallery as $image) {
            if ($image->getFile() === $product->getImage()) {
                continue;
            }

            $images[] = [
                'url' => $this->imageHelper->init($product, 'product_page_image_large')
                    ->setImageFile($image->getFile())
                    ->getUrl(),
                'label' => $image->getLabel() ?: $product->getName()
            ];

            $count++;
            if ($maxImages > 0 && $count >= $maxImages) {
                break;
            }
        }

        return $images;
    }

    /**
     * Retorna o texto do rodapé
     *
     * @return string
     */
    public function getFooterText()
    {
        return $this->_scopeConfig->getValue(
            'cortex_pdf/pdf_config/footer_text',
            ScopeInterface::SCOPE_STORE
        ) ?: '';
    }
}