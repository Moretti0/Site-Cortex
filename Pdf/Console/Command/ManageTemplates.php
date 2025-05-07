<?php
namespace Cortex\Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Cortex\Pdf\Model\PdfTemplate;
use Magento\Framework\Exception\LocalizedException;

class ManageTemplates extends Command
{
    /**
     * @var PdfTemplate
     */
    protected $pdfTemplate;

    /**
     * @param PdfTemplate $pdfTemplate
     */
    public function __construct(
        PdfTemplate $pdfTemplate
    ) {
        $this->pdfTemplate = $pdfTemplate;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cortex:pdf:manage-templates')
            ->setDescription('Gerencia templates de PDF')
            ->addOption(
                'list',
                null,
                InputOption::VALUE_NONE,
                'Lista todos os templates disponíveis'
            )
            ->addOption(
                'create',
                null,
                InputOption::VALUE_REQUIRED,
                'Cria um novo template (nome do template)'
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_REQUIRED,
                'Remove um template (nome do template)'
            )
            ->addOption(
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'Conteúdo do template (usado com --create)'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $list = $input->getOption('list');
            $create = $input->getOption('create');
            $delete = $input->getOption('delete');
            $content = $input->getOption('content');

            if ($list) {
                $templates = $this->pdfTemplate->getAllTemplates();
                if (empty($templates)) {
                    $output->writeln('<info>Nenhum template encontrado.</info>');
                    return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
                }

                $output->writeln('<info>Templates disponíveis:</info>');
                foreach ($templates as $template) {
                    $output->writeln(sprintf('- %s', $template['name']));
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            if ($create) {
                if (!$content) {
                    throw new LocalizedException(__('É necessário fornecer o conteúdo do template com --content.'));
                }

                if ($this->pdfTemplate->createTemplate($create, $content)) {
                    $output->writeln(sprintf('<info>Template %s criado com sucesso.</info>', $create));
                } else {
                    $output->writeln(sprintf('<error>Erro ao criar template %s.</error>', $create));
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            if ($delete) {
                if ($this->pdfTemplate->deleteTemplate($delete)) {
                    $output->writeln(sprintf('<info>Template %s removido com sucesso.</info>', $delete));
                } else {
                    $output->writeln(sprintf('<error>Erro ao remover template %s.</error>', $delete));
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            throw new LocalizedException(__('É necessário fornecer uma ação (--list, --create ou --delete).'));
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro inesperado: %s</error>', $e->getMessage()));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}