<?php
namespace Cortex\Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Cortex\Pdf\Model\PdfCache;
use Magento\Framework\Exception\LocalizedException;

class CleanCache extends Command
{
    /**
     * @var PdfCache
     */
    protected $pdfCache;

    /**
     * @param PdfCache $pdfCache
     */
    public function __construct(
        PdfCache $pdfCache
    ) {
        $this->pdfCache = $pdfCache;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cortex:pdf:clean-cache')
            ->setDescription('Limpa o cache de PDFs gerados')
            ->addOption(
                'sku',
                null,
                InputOption::VALUE_OPTIONAL,
                'SKU específico do produto para limpar cache'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Limpa todo o cache de PDFs'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getOption('sku');
            $all = $input->getOption('all');

            if ($all) {
                $this->pdfCache->clean();
                $output->writeln('<info>Cache de PDFs limpo com sucesso.</info>');
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            if ($sku) {
                if ($this->pdfCache->remove($sku)) {
                    $output->writeln(sprintf('<info>Cache do produto %s limpo com sucesso.</info>', $sku));
                } else {
                    $output->writeln(sprintf('<error>Erro ao limpar cache do produto %s.</error>', $sku));
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            throw new LocalizedException(__('É necessário fornecer um SKU ou usar a opção --all.'));
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro inesperado: %s</error>', $e->getMessage()));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}