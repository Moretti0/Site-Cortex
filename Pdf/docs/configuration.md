# Guia de Configuração do Módulo Cortex PDF

## Configurações Gerais

### 1. Habilitar Módulo
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Gerais
- **Opção**: Habilitar Módulo
- **Valores**: Sim/Não
- **Descrição**: Ativa ou desativa o módulo

### 2. Texto do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Gerais
- **Opção**: Texto do Botão
- **Valores**: Texto personalizado
- **Descrição**: Define o texto exibido no botão de visualização

### 3. Posição do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Gerais
- **Opção**: Posição do Botão
- **Valores**: 
  - Após Descrição
  - Após Imagens
  - Após Especificações
  - Personalizado
- **Descrição**: Define onde o botão será exibido na página do produto

### 4. Usar Estilo do Tema
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Gerais
- **Opção**: Usar Estilo do Tema
- **Valores**: Sim/Não
- **Descrição**: Se habilitado, o botão usará o estilo padrão do tema

## Design do Botão

### 1. Cor do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Design do Botão
- **Opção**: Cor do Botão
- **Valores**: 
  - Azul
  - Verde
  - Vermelho
  - Laranja
  - Roxo
  - Personalizado
- **Descrição**: Define a cor do botão

### 2. Tamanho do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Design do Botão
- **Opção**: Tamanho do Botão
- **Valores**: 
  - Pequeno
  - Médio
  - Grande
  - Extra Grande
- **Descrição**: Define o tamanho do botão

### 3. Animação do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Design do Botão
- **Opção**: Animação do Botão
- **Valores**: 
  - Sem Animação
  - Fade
  - Escala
  - Deslizar
  - Quicar
- **Descrição**: Define o efeito de animação ao passar o mouse

### 4. Ícone do Botão
- **Caminho**: Cortex > PDF Gerador > Configurações > Design do Botão
- **Opção**: Ícone do Botão
- **Valores**: 
  - Sem Ícone
  - Olho
  - Documento
  - Download
  - Imprimir
- **Descrição**: Define o ícone do botão

## Responsividade

### 1. Tamanho do Botão em Mobile
- **Caminho**: Cortex > PDF Gerador > Configurações > Responsividade
- **Opção**: Tamanho do Botão em Mobile
- **Valores**: 
  - Pequeno
  - Médio
  - Grande
  - Extra Grande
- **Descrição**: Define o tamanho do botão em dispositivos móveis

### 2. Posição do Botão em Mobile
- **Caminho**: Cortex > PDF Gerador > Configurações > Responsividade
- **Opção**: Posição do Botão em Mobile
- **Valores**: 
  - Após Descrição
  - Após Imagens
  - Após Especificações
  - Personalizado
- **Descrição**: Define a posição do botão em dispositivos móveis

## Acessibilidade

### 1. Rótulo ARIA
- **Caminho**: Cortex > PDF Gerador > Configurações > Acessibilidade
- **Opção**: Rótulo ARIA
- **Valores**: Texto personalizado
- **Descrição**: Define o texto descritivo para leitores de tela

### 2. Navegação por Teclado
- **Caminho**: Cortex > PDF Gerador > Configurações > Acessibilidade
- **Opção**: Navegação por Teclado
- **Valores**: Sim/Não
- **Descrição**: Habilita navegação por teclado (Tab e Enter)

## Configurações do PDF

### 1. Logo
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações do PDF
- **Opção**: Logo
- **Valores**: Arquivo de imagem
- **Descrição**: Logo para exibir no cabeçalho do PDF

### 2. Texto do Rodapé
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações do PDF
- **Opção**: Texto do Rodapé
- **Valores**: Texto personalizado
- **Descrição**: Texto a ser exibido no rodapé de cada página

### 3. Mostrar Especificações
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações do PDF
- **Opção**: Mostrar Especificações
- **Valores**: Sim/Não
- **Descrição**: Exibe tabela de especificações técnicas do produto

## Configurações Avançadas

### 1. Cache
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Avançadas
- **Opção**: Tempo de Cache
- **Valores**: Número em segundos
- **Descrição**: Tempo para armazenar PDFs em cache

### 2. Debug
- **Caminho**: Cortex > PDF Gerador > Configurações > Configurações Avançadas
- **Opção**: Modo de Debug
- **Valores**: Sim/Não
- **Descrição**: Ativa logs detalhados para solução de problemas

## Dicas de Configuração

1. **Performance**
   - Use cache para produtos frequentemente acessados
   - Ajuste o tempo de cache conforme necessário
   - Monitore o uso de disco

2. **Acessibilidade**
   - Forneça rótulos ARIA descritivos
   - Mantenha a navegação por teclado habilitada
   - Teste com leitores de tela

3. **Responsividade**
   - Ajuste o tamanho do botão para mobile
   - Teste em diferentes dispositivos
   - Verifique a usabilidade

4. **Design**
   - Mantenha consistência com o tema
   - Use cores que contrastem bem
   - Teste as animações

## Próximos Passos

Após a configuração, consulte:
- [Manual de Uso](manual.md)
- [Guia de Instalação](installation.md) 