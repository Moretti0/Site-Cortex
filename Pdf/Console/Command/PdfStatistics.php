<?php
namespace Cortex\Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Cortex\Pdf\Model\Statistics;
use Psr\Log\LoggerInterface;

class PdfStatistics extends Command
{
    /**
     * @var Statistics
     */
    protected $statistics;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Statistics $statistics
     * @param LoggerInterface $logger
     */
    public function __construct(
        Statistics $statistics,
        LoggerInterface $logger
    ) {
        $this->statistics = $statistics;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cortex:pdf:statistics')
            ->setDescription('Exibe estatísticas do módulo de PDF')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Tipo de estatísticas (general, template, product)',
                'general'
            )
            ->addOption(
                'clear',
                'c',
                InputOption::VALUE_NONE,
                'Limpar todas as estatísticas'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($input->getOption('clear')) {
                if ($this->statistics->clearStats()) {
                    $output->writeln('<info>Estatísticas limpas com sucesso.</info>');
                } else {
                    $output->writeln('<error>Erro ao limpar estatísticas.</error>');
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            $type = $input->getOption('type');
            switch ($type) {
                case 'general':
                    $this->displayGeneralStats($output);
                    break;
                case 'template':
                    $this->displayTemplateStats($output);
                    break;
                case 'product':
                    $this->displayProductStats($output);
                    break;
                default:
                    $output->writeln('<error>Tipo de estatísticas inválido.</error>');
                    return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Erro ao exibir estatísticas: ' . $e->getMessage() . '</error>');
            $this->logger->error('Erro ao exibir estatísticas: ' . $e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * Exibir estatísticas gerais
     *
     * @param OutputInterface $output
     */
    protected function displayGeneralStats(OutputInterface $output)
    {
        $stats = $this->statistics->getGeneralStats();

        $output->writeln("\n<info>Estatísticas Gerais do Módulo PDF</info>");
        $output->writeln("----------------------------------------");
        $output->writeln(sprintf("Total de Gerações: %d", $stats['total_generations']));
        $output->writeln(sprintf("Gerações com Sucesso: %d", $stats['successful_generations']));
        $output->writeln(sprintf("Gerações com Falha: %d", $stats['failed_generations']));
        $output->writeln(sprintf("Taxa de Sucesso: %.2f%%", $stats['success_rate']));
        $output->writeln(sprintf("Tempo Médio de Geração: %.2f segundos", $stats['average_generation_time']));
        $output->writeln(sprintf("Tamanho Médio do Arquivo: %.2f MB", $stats['average_file_size'] / 1024 / 1024));
        $output->writeln(sprintf("Tamanho Total dos Arquivos: %.2f MB", $stats['total_file_size'] / 1024 / 1024));
    }

    /**
     * Exibir estatísticas por template
     *
     * @param OutputInterface $output
     */
    protected function displayTemplateStats(OutputInterface $output)
    {
        $stats = $this->statistics->getTemplateStats();

        $output->writeln("\n<info>Estatísticas por Template</info>");
        $output->writeln("----------------------------------------");

        foreach ($stats as $template => $data) {
            $output->writeln(sprintf("\n<comment>Template: %s</comment>", $template));
            $output->writeln(sprintf("Total de Gerações: %d", $data['count']));
            $output->writeln(sprintf("Gerações com Sucesso: %d", $data['success_count']));
            $output->writeln(sprintf("Gerações com Falha: %d", $data['fail_count']));
            $output->writeln(sprintf("Tempo Total de Geração: %.2f segundos", $data['total_time']));
            $output->writeln(sprintf("Tamanho Total dos Arquivos: %.2f MB", $data['total_size'] / 1024 / 1024));
        }
    }

    /**
     * Exibir estatísticas por produto
     *
     * @param OutputInterface $output
     */
    protected function displayProductStats(OutputInterface $output)
    {
        $stats = $this->statistics->getProductStats();

        $output->writeln("\n<info>Estatísticas por Produto</info>");
        $output->writeln("----------------------------------------");

        foreach ($stats as $sku => $data) {
            $output->writeln(sprintf("\n<comment>SKU: %s</comment>", $sku));
            $output->writeln(sprintf("Total de Gerações: %d", $data['count']));
            $output->writeln(sprintf("Última Geração: %s", $data['last_generation']));
        }
    }
}