<?php
namespace Cortex\Pdf\Model\Template;

use Magento\Framework\Model\AbstractModel;

class Config extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Cortex\Pdf\Model\ResourceModel\Template\Config::class);
    }
}
