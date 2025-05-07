<?php
namespace Cortex\Pdf\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class PdfFileSystem
{
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
    protected $baseDirectory;

    /**
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        File $fileDriver,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;

        $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->baseDirectory = $varDirectory->getAbsolutePath('cortex_pdf');

        $this->initializeDirectories();
    }

    /**
     * Inicializa os diretórios necessários
     *
     * @return void
     * @throws FileSystemException
     */
    protected function initializeDirectories()
    {
        try {
            // Verificar e criar diretório base
            $this->createAndVerifyDirectory($this->baseDirectory);

            // Criar subdiretórios
            $subdirectories = [
                'history',
                'cache',
                'temp',
                'logs'
            ];

            foreach ($subdirectories as $dir) {
                $path = $this->baseDirectory . '/' . $dir;
                $this->createAndVerifyDirectory($path);
            }

            // Criar arquivos iniciais
            $this->initializeFiles();

            $this->logger->info('Diretórios do sistema de arquivos inicializados com sucesso.');
        } catch (FileSystemException $e) {
            $this->logger->critical('Erro crítico ao inicializar diretórios: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao inicializar diretórios: ' . $e->getMessage());
            throw new FileSystemException(
                __('Erro ao inicializar diretórios do sistema de arquivos: %1', $e->getMessage())
            );
        }
    }

    /**
     * Cria e verifica um diretório
     *
     * @param string $directory
     * @return bool
     * @throws FileSystemException
     */
    public function createAndVerifyDirectory($directory)
    {
        // Verificar se o diretório já existe
        if ($this->fileDriver->isDirectory($directory)) {
            // Verificar permissões
            if (!$this->isDirectoryWritable($directory)) {
                $this->logger->critical("Diretório {$directory} não tem permissão de escrita.");
                throw new FileSystemException(
                    __('O diretório "%1" não tem permissão de escrita. Verifique as permissões.', $directory)
                );
            }
            return true;
        }

        // Verificar se o diretório pai existe e tem permissões
        $parentDir = dirname($directory);
        if (!$this->fileDriver->isDirectory($parentDir)) {
            $this->createAndVerifyDirectory($parentDir);
        }

        if (!$this->isDirectoryWritable($parentDir)) {
            $this->logger->critical("Diretório pai {$parentDir} não tem permissão de escrita.");
            throw new FileSystemException(
                __('O diretório pai "%1" não tem permissão de escrita. Verifique as permissões.', $parentDir)
            );
        }

        // Criar o diretório
        try {
            $this->fileDriver->createDirectory($directory, 0755);
            $this->logger->info("Diretório {$directory} criado com sucesso.");

            // Verificar se foi criado corretamente
            if (!$this->fileDriver->isDirectory($directory)) {
                throw new FileSystemException(
                    __('Não foi possível criar o diretório "%1". Verifique as permissões.', $directory)
                );
            }

            // Verificar permissões após criação
            if (!$this->isDirectoryWritable($directory)) {
                throw new FileSystemException(
                    __('O diretório "%1" foi criado mas não tem permissão de escrita.', $directory)
                );
            }

            return true;
        } catch (FileSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao criar diretório {$directory}: " . $e->getMessage());
            throw new FileSystemException(
                __('Não foi possível criar o diretório "%1": %2', $directory, $e->getMessage())
            );
        }
    }

    /**
     * Verifica se um diretório tem permissão de escrita
     *
     * @param string $directory
     * @return bool
     */
    public function isDirectoryWritable($directory)
    {
        try {
            if (!$this->fileDriver->isDirectory($directory)) {
                return false;
            }

            return $this->fileDriver->isWritable($directory);
        } catch (\Exception $e) {
            $this->logger->error("Erro ao verificar permissões do diretório {$directory}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inicializa os arquivos necessários
     *
     * @return void
     * @throws FileSystemException
     */
    protected function initializeFiles()
    {
        $files = [
            'history/history.json' => '{}',
            'cache/cache.json' => '{}',
            'logs/error.log' => '',
            'logs/access.log' => ''
        ];

        foreach ($files as $file => $content) {
            $path = $this->baseDirectory . '/' . $file;
            try {
                if (!$this->fileDriver->isExists($path)) {
                    $this->fileDriver->filePutContents($path, $content);
                    $this->logger->info("Arquivo {$file} criado com sucesso.");
                }

                // Verificar se o arquivo é gravável
                if (!$this->fileDriver->isWritable($path)) {
                    $this->logger->warning("Arquivo {$file} não tem permissão de escrita.");
                }
            } catch (\Exception $e) {
                $this->logger->error("Erro ao criar arquivo {$file}: " . $e->getMessage());
                // Não lançar exceção pois não é crítico para o funcionamento
            }
        }
    }

    /**
     * Obtém o caminho completo para um arquivo
     *
     * @param string $path
     * @return string
     */
    public function getFilePath($path)
    {
        return $this->baseDirectory . '/' . ltrim($path, '/');
    }

    /**
     * Verifica se um arquivo existe
     *
     * @param string $path
     * @return bool
     */
    public function fileExists($path)
    {
        try {
            return $this->fileDriver->isExists($this->getFilePath($path));
        } catch (\Exception $e) {
            $this->logger->error("Erro ao verificar existência do arquivo {$path}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lê o conteúdo de um arquivo
     *
     * @param string $path
     * @return string
     * @throws FileSystemException
     */
    public function readFile($path)
    {
        $filePath = $this->getFilePath($path);

        try {
            if (!$this->fileDriver->isExists($filePath)) {
                throw new FileSystemException(
                    __('O arquivo "%1" não existe.', $path)
                );
            }

            if (!$this->fileDriver->isReadable($filePath)) {
                throw new FileSystemException(
                    __('O arquivo "%1" não tem permissão de leitura.', $path)
                );
            }

            return $this->fileDriver->fileGetContents($filePath);
        } catch (FileSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao ler arquivo {$path}: " . $e->getMessage());
            throw new FileSystemException(
                __('Não foi possível ler o arquivo "%1": %2', $path, $e->getMessage())
            );
        }
    }

    /**
     * Escreve conteúdo em um arquivo
     *
     * @param string $path
     * @param string $content
     * @return bool
     * @throws FileSystemException
     */
    public function writeFile($path, $content)
    {
        $filePath = $this->getFilePath($path);

        try {
            // Se o diretório não existir, criar
            $directory = dirname($filePath);
            if (!$this->fileDriver->isDirectory($directory)) {
                $this->createAndVerifyDirectory($directory);
            }

            // Verificar se o arquivo existe e tem permissão de escrita
            if ($this->fileDriver->isExists($filePath) && !$this->fileDriver->isWritable($filePath)) {
                throw new FileSystemException(
                    __('O arquivo "%1" não tem permissão de escrita.', $path)
                );
            }

            $this->fileDriver->filePutContents($filePath, $content);
            return true;
        } catch (FileSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao escrever no arquivo {$path}: " . $e->getMessage());
            throw new FileSystemException(
                __('Não foi possível escrever no arquivo "%1": %2', $path, $e->getMessage())
            );
        }
    }

    /**
     * Deleta um arquivo
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function deleteFile($path)
    {
        $filePath = $this->getFilePath($path);

        try {
            if (!$this->fileDriver->isExists($filePath)) {
                // Se o arquivo não existe, consideramos que a operação foi bem-sucedida
                return true;
            }

            // Verificar permissões
            $directory = dirname($filePath);
            if (!$this->fileDriver->isWritable($directory)) {
                throw new FileSystemException(
                    __('O diretório "%1" não tem permissão de escrita.', dirname($path))
                );
            }

            return $this->fileDriver->deleteFile($filePath);
        } catch (FileSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao deletar arquivo {$path}: " . $e->getMessage());
            throw new FileSystemException(
                __('Não foi possível deletar o arquivo "%1": %2', $path, $e->getMessage())
            );
        }
    }

    /**
     * Lista arquivos em um diretório
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     */
    public function listFiles($path)
    {
        $dirPath = $this->getFilePath($path);

        try {
            if (!$this->fileDriver->isDirectory($dirPath)) {
                throw new FileSystemException(
                    __('O diretório "%1" não existe.', $path)
                );
            }

            if (!$this->fileDriver->isReadable($dirPath)) {
                throw new FileSystemException(
                    __('O diretório "%1" não tem permissão de leitura.', $path)
                );
            }

            return $this->fileDriver->readDirectory($dirPath);
        } catch (FileSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao listar arquivos do diretório {$path}: " . $e->getMessage());
            throw new FileSystemException(
                __('Não foi possível listar os arquivos do diretório "%1": %2', $path, $e->getMessage())
            );
        }
    }

    /**
     * Limpa arquivos antigos
     *
     * @param string $directory
     * @param int $daysToKeep
     * @return int Número de arquivos removidos
     */
    public function cleanOldFiles($directory, $daysToKeep = 30)
    {
        try {
            $path = $this->getFilePath($directory);
            if (!$this->fileDriver->isDirectory($path)) {
                $this->logger->warning("Diretório {$directory} não existe para limpeza.");
                return 0;
            }

            $files = $this->fileDriver->readDirectory($path);
            $cutoffTime = time() - ($daysToKeep * 86400);
            $filesRemoved = 0;

            foreach ($files as $file) {
                if ($this->fileDriver->isFile($file)) {
                    $fileTime = $this->fileDriver->stat($file)['mtime'];
                    if ($fileTime < $cutoffTime) {
                        try {
                            $this->fileDriver->deleteFile($file);
                            $filesRemoved++;
                        } catch (\Exception $e) {
                            $this->logger->warning("Não foi possível deletar o arquivo antigo {$file}: " . $e->getMessage());
                        }
                    }
                }
            }

            $this->logger->info("Limpeza de arquivos antigos: {$filesRemoved} arquivos removidos do diretório {$directory}.");
            return $filesRemoved;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao limpar arquivos antigos do diretório {$directory}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verifica o espaço em disco disponível (em bytes)
     *
     * @return float|bool Espaço disponível em bytes ou false se não for possível determinar
     */
    public function getAvailableDiskSpace()
    {
        try {
            if (function_exists('disk_free_space')) {
                return disk_free_space($this->baseDirectory);
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error("Erro ao verificar espaço em disco: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se o sistema tem espaço em disco suficiente
     *
     * @param int $requiredMB Espaço necessário em MB
     * @return bool
     */
    public function hasEnoughDiskSpace($requiredMB = 100)
    {
        $space = $this->getAvailableDiskSpace();
        if ($space === false) {
            // Se não for possível verificar, assumimos que há espaço suficiente
            $this->logger->warning("Não foi possível verificar o espaço em disco disponível.");
            return true;
        }

        $requiredBytes = $requiredMB * 1024 * 1024;
        $result = $space >= $requiredBytes;

        if (!$result) {
            $this->logger->warning("Espaço em disco insuficiente: " . round($space / 1024 / 1024, 2) . "MB disponível, {$requiredMB}MB necessário.");
        }

        return $result;
    }
}