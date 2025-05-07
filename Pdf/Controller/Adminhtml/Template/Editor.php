<?php
namespace Cortex\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Json;
use Cortex\Pdf\Model\PdfTemplate;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Controller\ResultFactory;

class Editor extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var PdfTemplate
     */
    protected PdfTemplate $pdfTemplate;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var File
     */
    protected File $fileDriver;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param PdfTemplate $pdfTemplate
     * @param Filesystem $filesystem
     * @param File $fileDriver
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PdfTemplate $pdfTemplate,
        Filesystem $filesystem,
        File $fileDriver
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->pdfTemplate = $pdfTemplate;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Cortex_Pdf::template');
    }

    /**
     * Editor action
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cortex_Pdf::template');
        $resultPage->getConfig()->getTitle()->prepend(__('Editor de Templates PDF'));

        // Adicionar assets necessários
        $resultPage->getLayout()->getBlock('head')
            ->addJs('Cortex_Pdf/js/template-editor.js')
            ->addCss('Cortex_Pdf/css/template-editor.css');

        return $resultPage;
    }

    /**
     * Salvar template action
     *
     * @return Json
     */
    public function save(): Json
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $templateData = $this->getRequest()->getPostValue();

            if (empty($templateData['name'])) {
                throw new \Exception(__('Nome do template é obrigatório.'));
            }

            // Salvar template
            $success = $this->pdfTemplate->saveTemplate([
                'name' => $templateData['name'],
                'content' => $templateData['content'] ?? '',
                'layout' => $templateData['layout'] ?? []
            ]);

            if (!$success) {
                throw new \Exception(__('Erro ao salvar template.'));
            }

            $result->setData([
                'success' => true,
                'message' => __('Template salvo com sucesso.')
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Carregar template action
     *
     * @return Json
     */
    public function load(): Json
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $templateName = $this->getRequest()->getParam('name');

            if (empty($templateName)) {
                throw new \Exception(__('Nome do template é obrigatório.'));
            }

            $template = $this->pdfTemplate->loadTemplate($templateName);

            $result->setData([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Preview template action
     *
     * @return Json
     */
    public function preview(): Json
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $templateData = $this->getRequest()->getPostValue();

            if (empty($templateData['content'])) {
                throw new \Exception(__('Conteúdo do template é obrigatório.'));
            }

            // Gerar preview
            $preview = $this->generatePreview(
                $templateData['content'],
                $templateData['layout'] ?? []
            );

            $result->setData([
                'success' => true,
                'preview' => $preview
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Gera uma prévia do template
     *
     * @param string $content
     * @param array $layout
     * @return string
     */
    private function generatePreview(string $content, array $layout = []): string
    {
        // Implementar método de prévia
        // Aqui poderia ser adicionada a lógica para gerar o HTML da prévia
        // baseado no conteúdo do template e nas configurações de layout

        // Por enquanto, retornar apenas o conteúdo original
        return $content;
    }
}