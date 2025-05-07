<?php
namespace Cortex\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Cortex\Pdf\Model\PdfTemplate;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

class Edit extends Action
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
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param PdfTemplate $pdfTemplate
     * @param Registry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PdfTemplate $pdfTemplate,
        Registry $registry,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->pdfTemplate = $pdfTemplate;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Cortex_Pdf::templates');
    }

    /**
     * Edit action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        try {
            $templateId = $this->getRequest()->getParam('id');

            // Verificar se estamos editando um template existente
            if ($templateId) {
                $template = $this->pdfTemplate->getTemplateById($templateId);

                if (!$template) {
                    $this->messageManager->addErrorMessage(__('Template não encontrado.'));
                    return $this->_redirect('*/*/');
                }

                // Registrar o template em ambos os registry keys para compatibilidade
                $this->registry->register('pdf_template', $template);
                $this->registry->register('cortex_pdf_template', $template);

                $this->logger->debug('Template carregado com sucesso: ID ' . $templateId);
            } else {
                // Novo template - criar um modelo vazio
                $template = [
                    'id' => null,
                    'name' => '',
                    'description' => '',
                    'content' => $this->getDefaultTemplateContent()
                ];

                // Registrar o modelo vazio em ambos os registry keys
                $this->registry->register('pdf_template', $template);
                $this->registry->register('cortex_pdf_template', $template);

                $this->logger->debug('Novo template inicializado');
            }

            // Criar a página de resultado
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Cortex_Pdf::templates');
            $resultPage->getConfig()->getTitle()->prepend(
                $templateId ? __('Editar Template PDF') : __('Novo Template PDF')
            );

            return $resultPage;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao editar template: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Ocorreu um erro ao editar o template: %1', $e->getMessage()));
            return $this->_redirect('*/*/');
        }
    }

    /**
     * Get default template content
     *
     * @return string
     */
    protected function getDefaultTemplateContent(): string
    {
        return '<div class="pdf-template">
    <div class="header">
        <h1>{{var name}}</h1>
        <p>SKU: {{var sku}}</p>
    </div>
    <div class="content">
        <div class="product-image">
            <img src="{{var image}}" alt="{{var name}}" />
        </div>
        <div class="product-info">
            <h2>Descrição</h2>
            <div class="description">{{var description}}</div>

            <h2>Preço</h2>
            <div class="price">{{var price}}</div>
        </div>
    </div>
    <div class="footer">
        <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
    </div>
</div>';
    }
}