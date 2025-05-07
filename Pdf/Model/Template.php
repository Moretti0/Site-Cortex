<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\Model\AbstractModel;
use Cortex\Pdf\Model\ResourceModel\Template as ResourceTemplate;

class Template extends AbstractModel
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceTemplate::class);
    }
}