<?php
namespace Cortex\Pdf\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Cortex\Pdf\Model\PdfTemplate;
use Magento\Framework\Api\Filter;
use Psr\Log\LoggerInterface;

/**
 * DataProvider para os templates PDF
 */
class TemplateDataProvider extends AbstractDataProvider
{
    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @var array
     */
    protected $loadedData = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param PdfTemplate $pdfTemplate
     * @param LoggerInterface $logger
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PdfTemplate $pdfTemplate,
        LoggerInterface $logger,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pdfTemplate = $pdfTemplate;
        $this->logger = $logger;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if ($this->loadedData === null) {
            $this->loadedData = [];
            $items = $this->pdfTemplate->getTemplates();

            foreach ($items as $template) {
                $this->loadedData[$template['id']] = $template;
            }
        }

        return $this->loadedData;
    }

    /**
     * Add filter
     *
     * @param Filter $filter
     * @return bool
     */
    public function addFilter(Filter $filter)
    {
        // Implementação básica - para filtros mais complexos, adicione lógica aqui
        return true;
    }
}