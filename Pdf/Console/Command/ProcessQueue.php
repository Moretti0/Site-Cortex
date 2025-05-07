<?php
namespace Cortex\Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cortex\Pdf\Model\Queue;
use Psr\Log\LoggerInterface;

class ProcessQueue extends Command
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Queue $queue
     * @param LoggerInterface $logger
     */
    public function __construct(
        Queue $queue,
        LoggerInterface $logger
    ) {
        $this->queue = $queue;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cortex:pdf:process-queue')
            ->setDescription('Processa a fila de geração de PDFs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->queue->isEnabled()) {
                $output->writeln('<error>A fila está desabilitada nas configurações.</error>');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            $output->writeln('<info>Iniciando processamento da fila...</info>');

            $processed = 0;
            while ($this->queue->processNextTask()) {
                $processed++;
                $output->writeln('<info>Tarefa processada com sucesso.</info>');
            }

            if ($processed > 0) {
                $output->writeln(sprintf(
                    '<info>Processamento concluído. %d tarefas processadas.</info>',
                    $processed
                ));
            } else {
                $output->writeln('<info>Nenhuma tarefa pendente para processamento.</info>');
            }

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Erro ao processar fila: ' . $e->getMessage() . '</error>');
            $this->logger->error('Erro ao processar fila: ' . $e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}