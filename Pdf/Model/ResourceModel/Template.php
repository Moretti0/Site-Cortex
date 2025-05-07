<?php
namespace Cortex\Pdf\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Template extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('cortex_pdf_template', 'template_id');
    }
}