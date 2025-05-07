# Manual do Módulo Cortex PDF

## Comandos de Console

### 1. Geração de PDFs
```bash
php bin/magento cortex:pdf:generate [opções]
```

**Opções disponíveis:**
- `--sku=SKU` - Gera PDF para um produto específico
- `--all` - Gera PDFs para todos os produtos

**Exemplos:**
```bash
# Gerar PDF para um produto específico
php bin/magento cortex:pdf:generate --sku=PRODUTO-001

# Gerar PDFs para todos os produtos
php bin/magento cortex:pdf:generate --all
```

### 2. Estatísticas
```bash
php bin/magento cortex:pdf:statistics [opções]
```

**Opções disponíveis:**
- `--start-date=YYYY-MM-DD` - Data inicial do período
- `--end-date=YYYY-MM-DD` - Data final do período
- `--clean` - Limpa estatísticas antigas
- `--days=N` - Número de dias para manter (padrão: 30)

**Exemplos:**
```bash
# Ver estatísticas de um período específico
php bin/magento cortex:pdf:statistics --start-date=2024-01-01 --end-date=2024-03-21

# Limpar estatísticas antigas (mantendo últimos 30 dias)
php bin/magento cortex:pdf:statistics --clean --days=30
```

### 3. Limpeza de Cache
```bash
php bin/magento cortex:pdf:clean-cache [opções]
```

**Opções disponíveis:**
- `--sku=SKU` - Limpa cache de um produto específico
- `--all` - Limpa todo o cache de PDFs

**Exemplos:**
```bash
# Limpar cache de um produto específico
php bin/magento cortex:pdf:clean-cache --sku=PRODUTO-001

# Limpar todo o cache
php bin/magento cortex:pdf:clean-cache --all
```

### 4. Gerenciamento de Templates
```bash
php bin/magento cortex:pdf:manage-templates [opções]
```

**Opções disponíveis:**
- `--list` - Lista todos os templates disponíveis
- `--create=nome` - Cria um novo template
- `--delete=nome` - Remove um template
- `--content="conteúdo"` - Conteúdo do template (usado com --create)

**Exemplos:**
```bash
# Listar todos os templates
php bin/magento cortex:pdf:manage-templates --list

# Criar novo template
php bin/magento cortex:pdf:manage-templates --create=template-padrao --content="<html>...</html>"

# Remover template
php bin/magento cortex:pdf:manage-templates --delete=template-padrao
```

## Dicas de Uso

### 1. Geração em Lote
Para gerar PDFs em lote, você pode combinar os comandos:
```bash
# Gerar PDFs e limpar cache antigo
php bin/magento cortex:pdf:generate --all && php bin/magento cortex:pdf:clean-cache --all
```

### 2. Manutenção Regular
Recomenda-se executar periodicamente:
```bash
# Limpar cache antigo
php bin/magento cortex:pdf:clean-cache --all

# Limpar estatísticas antigas
php bin/magento cortex:pdf:statistics --clean --days=30
```

### 3. Monitoramento
Para monitorar o uso do módulo:
```bash
# Ver estatísticas do último mês
php bin/magento cortex:pdf:statistics --start-date=$(date -d "1 month ago" +%Y-%m-%d) --end-date=$(date +%Y-%m-%d)
```

## Solução de Problemas

### 1. Erro ao Gerar PDF
Se encontrar erros ao gerar PDFs:
1. Limpe o cache:
```bash
php bin/magento cortex:pdf:clean-cache --all
```
2. Verifique os logs:
```bash
tail -f var/log/cortex_pdf.log
```

### 2. Templates Corrompidos
Se suspeitar de templates corrompidos:
1. Liste os templates:
```bash
php bin/magento cortex:pdf:manage-templates --list
```
2. Remova o template problemático:
```bash
php bin/magento cortex:pdf:manage-templates --delete=nome-template
```
3. Crie um novo template:
```bash
php bin/magento cortex:pdf:manage-templates --create=nome-template --content="novo conteúdo"
```

### 3. Problemas de Performance
Se notar lentidão:
1. Limpe o cache:
```bash
php bin/magento cortex:pdf:clean-cache --all
```
2. Verifique estatísticas:
```bash
php bin/magento cortex:pdf:statistics --start-date=$(date -d "7 days ago" +%Y-%m-%d) --end-date=$(date +%Y-%m-%d)
```

## Boas Práticas

1. **Manutenção Regular**
   - Limpe o cache semanalmente
   - Monitore as estatísticas mensalmente
   - Faça backup dos templates importantes

2. **Geração de PDFs**
   - Evite gerar PDFs em horário de pico
   - Use o cache para produtos frequentemente acessados
   - Monitore o tamanho dos PDFs gerados

3. **Templates**
   - Mantenha templates organizados
   - Faça backup antes de modificar templates
   - Teste novos templates em ambiente de desenvolvimento

4. **Monitoramento**
   - Verifique regularmente as estatísticas
   - Monitore o uso de disco
   - Acompanhe os logs de erro

## Suporte

Para suporte adicional:
- Email: suporte@cortex.com.br
- Documentação: [URL da documentação]
- Issues: [URL do repositório]

## Atualizações

O módulo é atualizado regularmente. Para verificar novas versões:
```bash
composer show cortex/module-pdf
```

Para atualizar:
```bash
composer update cortex/module-pdf
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
``` 