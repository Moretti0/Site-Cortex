<?php
namespace Cortex\Pdf\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

interface HistoryRepositoryInterface
{
    /**
     * Salva um registro de histórico
     *
     * @param string $sku
     * @param string $pdfPath
     * @return void
     * @throws LocalizedException
     */
    public function save($sku, $pdfPath);

    /**
     * Obtém o histórico de um produto
     *
     * @param string $sku
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBySku($sku);

    /**
     * Limpa o histórico antigo
     *
     * @param int $daysToKeep
     * @return void
     * @throws LocalizedException
     */
    public function cleanOldHistory($daysToKeep = 30);
} 