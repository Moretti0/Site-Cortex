<?php
namespace Cortex\Pdf\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Cortex\Pdf\Model\TemplateRepository;

class TemplateOptions implements OptionSourceInterface
{
    protected $templateRepository;

    public function __construct(TemplateRepository $templateRepository) 
    {
        $this->templateRepository = $templateRepository;
    }

    public function toOptionArray()
    {
        $options = [];
        try {
            $templates = $this->templateRepository->getList();
            foreach ($templates as $template) {
                $options[] = [
                    'value' => $template->getId(),
                    'label' => $template->getName()
                ];
            }
        } catch (\Exception $e) {
            // Em caso de erro, retornar pelo menos uma opção padrão
            $options[] = [
                'value' => 'default',
                'label' => 'Template Padrão'
            ];
        }
        return $options;
    }
}
