<?php
namespace Cortex\Pdf\Model;

use Cortex\Pdf\Model\ResourceModel\Template\CollectionFactory;

class TemplateRepository
{
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function getList()
    {
        try {
            $collection = $this->collectionFactory->create();
            if (method_exists($collection, 'addFieldToFilter')) {
                $collection->addFieldToFilter('is_active', 1);
            }
            if (method_exists($collection, 'setOrder')) {
                $collection->setOrder('name', 'ASC');
            }
            return $collection;
        } catch (\Exception $e) {
            // Retornar uma coleção vazia em caso de erro
            return [];
        }
    }
}