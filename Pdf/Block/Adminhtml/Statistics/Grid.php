<?php
namespace Cortex\Pdf\Block\Adminhtml\Statistics;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Cortex\Pdf\Api\HistoryRepositoryInterface;

class Grid extends Template
{
    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * @param Context $context
     * @param HistoryRepositoryInterface $historyRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        HistoryRepositoryInterface $historyRepository,
        array $data = []
    ) {
        $this->historyRepository = $historyRepository;
        parent::__construct($context, $data);
    }

    /**
     * Retorna as estatísticas
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_pdfs' => $this->historyRepository->getTotalPdfs(),
            'total_templates' => $this->historyRepository->getTotalTemplates(),
            'last_generated' => $this->historyRepository->getLastGenerated(),
            'most_used_template' => $this->historyRepository->getMostUsedTemplate()
        ];
    }
}