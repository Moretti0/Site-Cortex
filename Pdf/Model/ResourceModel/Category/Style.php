<?php
namespace Cortex\Pdf\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Style extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('cortex_pdf_category_style', 'style_id');
    }
}
