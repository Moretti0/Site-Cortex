<?php
namespace Cortex\Pdf\Model;

use Cortex\Pdf\Api\PdfGeneratorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Cortex\Pdf\Api\CategoryStyleRepositoryInterface;
use Cortex\Pdf\Model\Template\Config as TemplateConfig;
use Magento\Framework\Exception\LocalizedException;
use Cortex\Pdf\Model\WkhtmltopdfGenerator;

class PdfGenerator implements PdfGeneratorInterface
{
    protected ProductRepositoryInterface $productRepository;
    protected WkhtmltopdfGenerator $pdfRenderer;
    protected CategoryRepository $categoryRepository;
    protected CategoryStyleRepositoryInterface $styleRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepository $categoryRepository,
        CategoryStyleRepositoryInterface $styleRepository,
        WkhtmltopdfGenerator $pdfRenderer
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->styleRepository = $styleRepository;
        $this->pdfRenderer = $pdfRenderer;
    }

    public function generate(int $productId, TemplateConfig $template): string
    {
        $product = $this->productRepository->getById($productId);
        $templateData = json_decode($template->getJsonConfig(), true);

        if (!$templateData || !isset($templateData['sections'])) {
            throw new LocalizedException(__('Template inválido ou incompleto.'));
        }

        $categoryIds = $product->getCategoryIds();
        $style = null;
        if (!empty($categoryIds)) {
            $style = $this->styleRepository->getByCategoryId((int)$categoryIds[0]);
        }

        $headerImage = $style && $style->getHeaderImage() ? $style->getHeaderImage() : '';
        $backgroundImage = $style && $style->getBackgroundImage() ? $style->getBackgroundImage() : '';
        $footerText = $style && $style->getFooterText() ? $style->getFooterText() : '';

        $backgroundCss = $backgroundImage ? "background: url('{$backgroundImage}') no-repeat center center; background-size: cover;" : '';

        $html = "<html><head><style>body{font-family:Arial,sans-serif;font-size:12px;{$backgroundCss}}</style></head><body>";
        if ($headerImage) {
            $html .= "<div style='text-align:center;'><img src='{$headerImage}' style='max-width:100%; height:auto;'></div>";
        }

        $html .= "<h1>{$product->getName()}</h1>";

        foreach ($templateData['sections'] as $section) {
            $html .= "<h2>" . htmlspecialchars($section['label']) . "</h2>";
            $html .= "<table border='1' cellpadding='4' cellspacing='0' width='100%'>";
            foreach ($section['attributes'] as $attributeCode) {
                $label = ucfirst(str_replace('_', ' ', $attributeCode));
                $value = $product->getData($attributeCode);
                if (is_array($value)) $value = implode(', ', $value);
                $html .= "<tr><td><strong>" . htmlspecialchars($label) . "</strong></td><td>" . htmlspecialchars((string) $value) . "</td></tr>";
            }
            $html .= "</table><br>";
        }

        if ($footerText) {
            $html .= "<footer style='position:fixed;bottom:20px;width:100%;text-align:center;font-size:10px;color:#555;'>{$footerText}</footer>";
        }

        $html .= "</body></html>";

        return $this->pdfRenderer->renderFromHtml($html, $product->getSku() . "_template" . $template->getId() . ".pdf");
    }

    public function generateByProductId(int $productId, bool $saveToFile = true, $templateId = null): string
    {
        $template = $this->templateRepository->getDefaultTemplate();
        if (!$template) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Nenhum template padrão encontrado.'));
        }
        return $this->generate($productId, $template);
    }

    public function generateByProductSku(string $sku, bool $saveToFile = true, $templateId = null): string
    {
        $product = $this->productRepository->get($sku);
        $template = $this->templateRepository->getDefaultTemplate();
        if (!$template) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Nenhum template padrão encontrado.'));
        }
        return $this->generate((int) $product->getId(), $template);
    }
}
