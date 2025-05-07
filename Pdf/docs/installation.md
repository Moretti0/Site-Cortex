# Guia de Instalação do Módulo Cortex PDF

## Requisitos do Sistema

- Magento 2.4.x
- PHP 7.4 ou superior
- Extensão mPDF (`mpdf/mpdf`)
- Permissões de escrita nos diretórios:
  - `var/cache`
  - `var/log`
  - `var/tmp`
  - `pub/media`

## Métodos de Instalação

### 1. Instalação via Composer (Recomendado)

1. Execute o comando:
```bash
composer require cortex/module-pdf
```

2. Atualize o Magento:
```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

### 2. Instalação Manual

1. Crie os diretórios:
```bash
mkdir -p app/code/Cortex/Pdf
```

2. Copie os arquivos do módulo para o diretório criado

3. Execute os comandos:
```bash
php bin/magento module:enable Cortex_Pdf
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

## Verificação da Instalação

1. Verifique se o módulo está habilitado:
```bash
php bin/magento module:status Cortex_Pdf
```

2. Verifique se as dependências estão instaladas:
```bash
composer show mpdf/mpdf
```

3. Verifique os logs por erros:
```bash
tail -f var/log/system.log
```

## Configuração Inicial

1. Acesse o painel administrativo:
   - **Cortex** > **PDF Gerador** > **Configurações**

2. Configure as opções básicas:
   - Habilitar módulo
   - Texto do botão
   - Posição do botão
   - Estilo do botão

3. Configure as opções do PDF:
   - Tamanho do papel
   - Orientação
   - Logo
   - Rodapé

## Solução de Problemas

### 1. Erro de Permissão
Se encontrar erros de permissão:
```bash
chmod -R 755 app/code/Cortex/Pdf
chown -R www-data:www-data app/code/Cortex/Pdf
```

### 2. Erro de Dependência
Se a biblioteca mPDF não estiver instalada:
```bash
composer require mpdf/mpdf
```

### 3. Erro de Cache
Se encontrar problemas com cache:
```bash
php bin/magento cache:clean
php bin/magento cache:flush
```

### 4. Erro de Compilação
Se encontrar erros de compilação:
```bash
rm -rf var/cache/* var/page_cache/* var/view_preprocessed/* var/generation/*
php bin/magento setup:di:compile
```

## Próximos Passos

Após a instalação, consulte:
- [Manual de Uso](manual.md)
- [Guia de Configuração](configuration.md) 