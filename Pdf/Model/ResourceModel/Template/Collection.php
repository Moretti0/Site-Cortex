<?php
namespace Cortex\Pdf\Model\ResourceModel\Template;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Cortex\Pdf\Model\Template;
use Cortex\Pdf\Model\ResourceModel\Template as TemplateResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'template_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            Template::class,
            TemplateResource::class
        );

        // Usar o método integrado para definir a tabela principal
        $this->_mainTable = $this->getTable('cortex_pdf_template');
    }
}