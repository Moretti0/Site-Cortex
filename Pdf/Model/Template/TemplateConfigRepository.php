<?php
namespace Cortex\Pdf\Model\Template;

use Cortex\Pdf\Api\TemplateConfigRepositoryInterface;
use Cortex\Pdf\Model\ResourceModel\Template\Config as ResourceConfig;
use Cortex\Pdf\Model\Template\ConfigFactory;
use Cortex\Pdf\Model\ResourceModel\Template\Config\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class TemplateConfigRepository implements TemplateConfigRepositoryInterface
{
    protected $resource;
    protected $configFactory;
    protected $collectionFactory;

    public function __construct(
        ResourceConfig $resource,
        ConfigFactory $configFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->configFactory = $configFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $id): Config
    {
        $model = $this->configFactory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new LocalizedException(__('Template não encontrado com ID %1', $id));
        }
        return $model;
    }

    public function save(Config $template): Config
    {
        $this->resource->save($template);
        return $template;
    }

    public function delete(Config $template): bool
    {
        $this->resource->delete($template);
        return true;
    }

    public function getList(): \Cortex\Pdf\Model\ResourceModel\Template\Config\Collection
    {
        return $this->collectionFactory->create();
    }

    public function getDefaultTemplate(): ?Config
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->setOrder('template_id', 'ASC')
            ->setPageSize(1);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }
}
