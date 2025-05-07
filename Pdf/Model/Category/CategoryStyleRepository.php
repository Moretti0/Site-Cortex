<?php
namespace Cortex\Pdf\Model\Category;

use Cortex\Pdf\Api\CategoryStyleRepositoryInterface;
use Cortex\Pdf\Model\ResourceModel\Category\Style as ResourceStyle;
use Cortex\Pdf\Model\Category\StyleFactory;
use Cortex\Pdf\Model\ResourceModel\Category\Style\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class CategoryStyleRepository implements CategoryStyleRepositoryInterface
{
    protected $resource;
    protected $styleFactory;
    protected $collectionFactory;

    public function __construct(
        ResourceStyle $resource,
        StyleFactory $styleFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->styleFactory = $styleFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getByCategoryId(int $categoryId): ?Style
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('category_id', $categoryId)
            ->setPageSize(1);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }

    public function save(Style $style): Style
    {
        $this->resource->save($style);
        return $style;
    }

    public function delete(Style $style): bool
    {
        $this->resource->delete($style);
        return true;
    }

    public function getList(): \Cortex\Pdf\Model\ResourceModel\Category\Style\Collection
    {
        return $this->collectionFactory->create();
    }
}
