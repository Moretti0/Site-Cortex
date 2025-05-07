<?php
namespace Cortex\Pdf\Model\ResourceModel\Template\Grid;


use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Cortex\Pdf\Model\PdfTemplate;
use Cortex\Pdf\Model\Template\FileCollection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;


/**
 * Coleção de templates para o grid que carrega a partir de arquivos
 */
class TemplateGridCollection extends SearchResult implements FileCollection
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param PdfTemplate $pdfTemplate
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager,
            $mainTable, $resourceModel, $identifierName, $connectionName
        );
    }
    
    /**
     * Popula a coleção com os dados dos arquivos
     *
     * @return void
     */
    public function _populateCollection()
    {
        $templates = $this->pdfTemplate->getTemplates();

        foreach ($templates as $template) {
            // Adiciona itens à coleção convertendo cada template em um item de coleção
            $item = $this->getNewEmptyItem();
            $item->setData($template);
            $this->addItem($item);
        }
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get ID field name
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return 'id';
    }
}