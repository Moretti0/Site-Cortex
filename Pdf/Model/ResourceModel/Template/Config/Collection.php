<?php
namespace Cortex\Pdf\Model\ResourceModel\Template\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Cortex\Pdf\Model\Template\Config as TemplateConfigModel;
use Cortex\Pdf\Model\ResourceModel\Template\Config as TemplateConfigResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(TemplateConfigModel::class, TemplateConfigResource::class);
    }
}
