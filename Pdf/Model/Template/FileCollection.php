<?php
namespace Cortex\Pdf\Model\Template;

use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Interface para coleção de templates de arquivos
 */
interface FileCollection extends SearchResultInterface
{
    /**
     * Popula a coleção com os dados dos arquivos
     *
     * @return void
     */
    public function _populateCollection();

    /**
     * Get ID field name
     *
     * @return string
     */
    public function getIdFieldName();
} 