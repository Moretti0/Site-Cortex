<?php
namespace Cortex\Pdf\Model\Category;

use Magento\Framework\Model\AbstractModel;

class Style extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Cortex\Pdf\Model\ResourceModel\Category\Style::class);
    }
}
