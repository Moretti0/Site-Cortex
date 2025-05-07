<?php
namespace Cortex\Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Cortex\Pdf\Api\PdfGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Console\Cli;

class GeneratePdf extends Command
{
    /**
     * @var PdfGeneratorInterface
     */
    protected PdfGeneratorInterface $pdfGenerator;

    /**
     * @param PdfGeneratorInterface $pdfGenerator
     */
    public function __construct(
        PdfGeneratorInterface $pdfGenerator
    ) {
        $this->pdfGenerator = $pdfGenerator;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('cortex:pdf:generate')
            ->setDescription('Gera PDF para um produto específico')
            ->addOption(
                'sku',
                null,
                InputOption::VALUE_REQUIRED,
                'SKU do produto'
            )
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED,
                'ID do produto'
            )
            ->addOption(
                'template_id',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID do template a ser usado'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $sku = $input->getOption('sku');
            $id = $input->getOption('id');
            $templateId = $input->getOption('template_id');

            if (!$sku && !$id) {
                throw new LocalizedException(__('É necessário fornecer um SKU ou ID do produto.'));
            }

            if ($sku) {
                $this->pdfGenerator->generateByProductSku($sku, true, $templateId);
                $output->writeln(sprintf('<info>PDF gerado com sucesso para o produto com SKU: %s</info>', $sku));
            } else {
                $this->pdfGenerator->generateByProductId((int)$id, true, $templateId);
                $output->writeln(sprintf('<info>PDF gerado com sucesso para o produto com ID: %s</info>', $id));
            }

            return Cli::RETURN_SUCCESS;
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>Produto não encontrado: %s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>Erro ao gerar PDF: %s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro inesperado: %s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }
    }
}