<?php
namespace Cortex\Pdf\Controller\Product;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class Download implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param FileFactory $fileFactory
     * @param MessageManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param FileDriver $fileDriver
     */
    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        FileFactory $fileFactory,
        MessageManagerInterface $messageManager,
        LoggerInterface $logger,
        Filesystem $filesystem,
        FileDriver $fileDriver
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->fileFactory = $fileFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Execute action para download do arquivo PDF
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            // Obter o parâmetro de arquivo da URL
            $fileParam = $this->request->getParam('file');
            if (empty($fileParam)) {
                throw new LocalizedException(__('Parâmetro de arquivo não fornecido'));
            }

            // Decodificar o caminho do arquivo
            $filePath = base64_decode($fileParam);
            if (empty($filePath)) {
                throw new LocalizedException(__('Caminho de arquivo inválido'));
            }

            // Verificar se o arquivo existe
            if (!$this->fileDriver->isExists($filePath)) {
                throw new LocalizedException(__('O arquivo solicitado não existe'));
            }

            // Verificar se é um arquivo PDF
            $fileInfo = pathinfo($filePath);
            if (!isset($fileInfo['extension']) || strtolower($fileInfo['extension']) !== 'pdf') {
                throw new LocalizedException(__('O arquivo solicitado não é um PDF válido'));
            }

            // Verificar se o arquivo é temporário e está expirado (mais de 1 hora)
            if (strpos($filePath, '/tmp/') !== false) {
                $fileStats = $this->fileDriver->stat($filePath);
                $creationTime = $fileStats['mtime'] ?? time();
                $expirationTime = 3600; // 1 hora em segundos

                if ((time() - $creationTime) > $expirationTime) {
                    throw new LocalizedException(__('Este link de download expirou'));
                }
            }

            // Obter o nome do arquivo para download
            $fileName = isset($fileInfo['basename']) ? $fileInfo['basename'] : 'download.pdf';

            // Enviar o arquivo para download
            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => $filePath,
                    'rm' => false // Não remover o arquivo após o download
                ],
                DirectoryList::ROOT,
                'application/pdf',
                null
            );

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error('Erro no download de PDF: ' . $e->getMessage());
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('Não foi possível acessar o arquivo solicitado'));
            $this->logger->error('Erro de sistema de arquivos no download de PDF: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Ocorreu um erro ao processar o download. Por favor, tente novamente.'));
            $this->logger->critical('Erro inesperado no download de PDF: ' . $e->getMessage(), ['exception' => $e]);
        }

        // Redirecionar para a página inicial em caso de erro
        return $this->resultRedirectFactory->create()->setPath('/');
    }
}