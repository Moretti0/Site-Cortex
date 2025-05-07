<?php
namespace Cortex\Pdf\Model\ResourceModel\Template;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Config extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('cortex_pdf_template_config', 'template_id');
    }
}
