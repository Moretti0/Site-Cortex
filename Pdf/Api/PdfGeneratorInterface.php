<?php
namespace Cortex\Pdf\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface para o serviço de geração de PDF
 */
interface PdfGeneratorInterface
{
    /**
     * Gera um PDF para um produto específico
     *
     * @param string $sku O SKU do produto
     * @param bool $saveToFile Salvar o PDF em arquivo
     * @param int|null $templateId ID do template a ser usado
     * @return string Conteúdo do PDF em formato binário
     * @throws NoSuchEntityException Quando o produto não é encontrado
     * @throws LocalizedException
     */
    public function generateByProductSku(string $sku, bool $saveToFile = true, $templateId = null): string;

    /**
     * Gera um PDF para um produto específico
     *
     * @param int $productId O ID do produto
     * @param bool $saveToFile Salvar o PDF em arquivo
     * @param int|null $templateId ID do template a ser usado
     * @return string Conteúdo do PDF em formato binário
     * @throws NoSuchEntityException Quando o produto não é encontrado
     * @throws LocalizedException
     */
    public function generateByProductId(int $productId, bool $saveToFile = true, $templateId = null): string;
} 