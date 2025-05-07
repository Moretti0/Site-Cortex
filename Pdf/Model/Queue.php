<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class Queue
{
    const QUEUE_DIRECTORY = 'cortex_pdf/queue';
    const MAX_RETRIES = 3;
    const RETRY_DELAY = 300; // 5 minutos

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $queueDirectory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        File $fileDriver,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;

        $this->queueDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath(self::QUEUE_DIRECTORY);

        if (!$this->fileDriver->isDirectory($this->queueDirectory)) {
            $this->fileDriver->createDirectory($this->queueDirectory, 0755);
        }
    }

    /**
     * Adicionar tarefa à fila
     *
     * @param string $sku
     * @param string $templateName
     * @param array $options
     * @return bool
     */
    public function addToQueue($sku, $templateName, $options = [])
    {
        try {
            $taskId = uniqid();
            $taskFile = $this->queueDirectory . '/' . $taskId . '.json';

            $task = [
                'id' => $taskId,
                'sku' => $sku,
                'template_name' => $templateName,
                'options' => $options,
                'status' => 'pending',
                'retries' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->fileDriver->filePutContents($taskFile, json_encode($task));
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar tarefa à fila: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processar próxima tarefa da fila
     *
     * @return bool
     */
    public function processNextTask()
    {
        try {
            $files = $this->fileDriver->readDirectory($this->queueDirectory);
            foreach ($files as $file) {
                if ($this->fileDriver->isFile($file)) {
                    $content = $this->fileDriver->fileGetContents($file);
                    $task = json_decode($content, true);

                    if ($task && $task['status'] === 'pending') {
                        return $this->processTask($task, $file);
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao processar tarefa da fila: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processar tarefa específica
     *
     * @param array $task
     * @param string $taskFile
     * @return bool
     */
    protected function processTask($task, $taskFile)
    {
        try {
            // Atualizar status para processando
            $task['status'] = 'processing';
            $task['updated_at'] = date('Y-m-d H:i:s');
            $this->fileDriver->filePutContents($taskFile, json_encode($task));

            // Aqui você deve implementar a lógica de geração do PDF
            // Por exemplo, chamar o PdfGenerator com os parâmetros da tarefa

            // Simular processamento
            sleep(2);

            // Atualizar status para concluído
            $task['status'] = 'completed';
            $task['updated_at'] = date('Y-m-d H:i:s');
            $this->fileDriver->filePutContents($taskFile, json_encode($task));

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao processar tarefa: ' . $e->getMessage());

            // Incrementar tentativas
            $task['retries']++;
            $task['status'] = 'failed';
            $task['error'] = $e->getMessage();
            $task['updated_at'] = date('Y-m-d H:i:s');

            if ($task['retries'] < self::MAX_RETRIES) {
                $task['status'] = 'pending';
                $task['next_retry'] = date('Y-m-d H:i:s', time() + self::RETRY_DELAY);
            }

            $this->fileDriver->filePutContents($taskFile, json_encode($task));
            return false;
        }
    }

    /**
     * Obter status da tarefa
     *
     * @param string $taskId
     * @return array|null
     */
    public function getTaskStatus($taskId)
    {
        try {
            $taskFile = $this->queueDirectory . '/' . $taskId . '.json';
            if ($this->fileDriver->isExists($taskFile)) {
                $content = $this->fileDriver->fileGetContents($taskFile);
                return json_decode($content, true);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter status da tarefa: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Limpar fila
     *
     * @return bool
     */
    public function cleanQueue()
    {
        try {
            $files = $this->fileDriver->readDirectory($this->queueDirectory);
            foreach ($files as $file) {
                if ($this->fileDriver->isFile($file)) {
                    $this->fileDriver->deleteFile($file);
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao limpar fila: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se a fila está habilitada
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'cortex_pdf/advanced/queue_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}