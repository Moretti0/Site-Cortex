# Módulo Cortex PDF para Magento 2

Este módulo permite gerar e gerenciar PDFs de produtos no Magento 2.

## Documentação

A documentação completa está disponível em:
- [Manual de Uso](docs/manual.md)
- [Guia de Instalação](docs/installation.md)
- [Guia de Configuração](docs/configuration.md)

## Recursos

- Geração de PDFs de produtos
- Templates personalizáveis
- Sistema de cache
- Estatísticas de uso
- Comandos de console
- Interface administrativa

## Requisitos

- Magento 2.4.x
- PHP 7.4 ou superior
- Extensão mPDF (`mpdf/mpdf`)

## Instalação

### Via Composer
```bash
composer require cortex/module-pdf
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

### Manual
1. Crie os diretórios `app/code/Cortex/Pdf`
2. Copie os arquivos para o diretório criado
3. Execute:
```bash
php bin/magento module:enable Cortex_Pdf
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

## Suporte

Para suporte adicional:
- Email: suporte@cortex.com.br
- Documentação: [URL da documentação]
- Issues: [URL do repositório]

## Licença

Este módulo está licenciado sob a [Licença OSL-3.0](LICENSE.txt). 