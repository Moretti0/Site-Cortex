<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Notification
{
    const XML_PATH_EMAIL_TEMPLATE = 'cortex_pdf/notification/email_template';
    const XML_PATH_EMAIL_SENDER = 'cortex_pdf/notification/sender_email_identity';
    const XML_PATH_EMAIL_RECIPIENT = 'cortex_pdf/notification/recipient_email';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Enviar notificação de geração de PDF
     *
     * @param string $sku
     * @param string $templateName
     * @param bool $success
     * @param string|null $errorMessage
     * @return bool
     */
    public function sendPdfGenerationNotification($sku, $templateName, $success = true, $errorMessage = null)
    {
        try {
            if (!$this->isEnabled()) {
                return false;
            }

            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $templateId = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $sender = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_SENDER,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $recipient = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_RECIPIENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $templateVars = [
                'sku' => $sku,
                'template_name' => $templateName,
                'success' => $success,
                'error_message' => $errorMessage,
                'store_name' => $this->storeManager->getStore()->getName(),
                'date' => date('Y-m-d H:i:s')
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => $storeId])
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao enviar notificação de PDF: ' . $e->getMessage());
            $this->inlineTranslation->resume();
            return false;
        }
    }

    /**
     * Enviar notificação de erro
     *
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function sendErrorNotification($message, $context = [])
    {
        try {
            if (!$this->isEnabled()) {
                return false;
            }

            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $templateId = $this->scopeConfig->getValue(
                'cortex_pdf/notification/error_email_template',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $sender = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_SENDER,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $recipient = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_RECIPIENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $templateVars = [
                'error_message' => $message,
                'context' => json_encode($context, JSON_PRETTY_PRINT),
                'store_name' => $this->storeManager->getStore()->getName(),
                'date' => date('Y-m-d H:i:s')
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => $storeId])
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao enviar notificação de erro: ' . $e->getMessage());
            $this->inlineTranslation->resume();
            return false;
        }
    }

    /**
     * Verifica se as notificações estão habilitadas
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/notification/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}