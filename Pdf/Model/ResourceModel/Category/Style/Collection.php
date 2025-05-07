<?php
namespace Cortex\Pdf\Model\ResourceModel\Category\Style;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Cortex\Pdf\Model\Category\Style::class,
            \Cortex\Pdf\Model\ResourceModel\Category\Style::class
        );
    }
}
