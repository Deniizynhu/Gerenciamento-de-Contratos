# Manual de Uso  
**Sistema de Gestão de Contratos – Dultec Soluções**  
Versão: 1.0

---

## 1. Introdução

Este manual descreve como utilizar o **Sistema de Gestão de Contratos da Dultec Soluções**, destinado a:

- Gerenciar clientes;
- Gerenciar contratos de serviços de TI;
- Controlar usuários e níveis de acesso;
- Acompanhar indicadores no dashboard.

O sistema é dividido em módulos, acessados através do **menu lateral**.

---

## 2. Perfis de Acesso

O sistema trabalha com três níveis de acesso:

### 2.1. Administrador

- Acesso total.
- Pode:
  - Cadastrar / editar / inativar / excluir **usuários** (funcionários e clientes);
  - Cadastrar / editar / excluir **contratos**;
  - Cadastrar / editar **clientes**;
  - Acessar a tela de **Configurações** (tema, logotipo, nome do sistema).

### 2.2. Gerente de Contrato

- Focado na operação diária.
- Pode:
  - Cadastrar / editar **contratos**;
  - Cadastrar / editar **clientes**;
  - Cadastrar / editar **usuários**, mas **não pode excluir**;
- Não acessa a tela de configurações gerais do sistema.

### 2.3. Cliente

- Acesso restrito aos **seus próprios dados**:
  - Seus contratos;
  - Seu dashboard, com estatísticas apenas dos contratos dele.
- Não vê outros clientes, outros usuários, nem configurações.

---

## 3. Acesso ao Sistema (Login)

1. Abra o navegador e acesse o endereço do sistema, por exemplo:
   - Ambiente local: `http://localhost/contratos/login.php`
   - Ambiente produção: `https://seudominio.com/contratos/login.php`
2. Informe:
   - **E-mail**: cadastrado no sistema;
   - **Senha**: definida pelo administrador ou gerente.
3. Clique em **Entrar**.

> Caso a mensagem “E-mail, senha inválidos ou usuário inativo” apareça, verifique:
> - Se o e-mail foi digitado corretamente;
> - Se a senha está correta;
> - Se o usuário está marcado como **ATIVO**.

---

## 4. Navegação Geral

Após o login, o sistema apresenta:

- **Menu Lateral (esquerda)**:
  - Dashboard
  - Clientes (exceto cliente final)
  - Contratos
  - Usuários (exceto cliente final)
  - Configurações (somente Administrador)
  - Sair do Sistema

- **Área Central**:
  - Conteúdo de cada tela (lista, formulários, gráficos, etc.).

---

## 5. Dashboard

O **Dashboard** é a tela inicial após o login.

### 5.1. Cards Superiores

- **Contratos Ativos**  
  Total de contratos com status “Ativo”.

- **Valor Mensal**  
  Soma dos valores mensais de todos os contratos ativos (ou apenas do cliente logado, se o usuário for cliente).

- **A Vencer (Próx. 90 dias)**  
  Lista dos contratos que possuem `data_fim` dentro dos próximos 90 dias:
  - Exibe **data de vencimento** e **nome do cliente** (ou apenas os do próprio cliente, se logado como cliente).

### 5.2. Gráfico – Desempenho Anual

Gráfico com os **últimos 12 meses**:

- **Barras Azuis – Receita (R$)**  
  Soma dos valores de contratos ativos no mês.

- **Linha Verde – Contratos Ativos**  
  Quantidade de contratos ativos em cada mês.

- **Linha Vermelha – Vencimentos**  
  Número de contratos com término (`data_fim`) naquele mês.

> Para usuários do tipo **Cliente**, todas as informações do dashboard são filtradas apenas para os contratos daquele cliente.

---

## 6. Gestão de Clientes

### 6.1. Listagem de Clientes

Menu: **Clientes**

Disponível para **Administrador** e **Gerente de Contrato**.

A lista mostra:

- Nome / Razão Social
- CPF/CNPJ
- E-mail
- Telefone
- Cidade / UF
- Situação (se for exibida)

Ações:

- **Editar** – altera cadastro do cliente.

### 6.2. Cadastro de Cliente

Menu: **Clientes → Novo Cliente** (ou botão equivalente).

Campos principais:

- **CPF/CNPJ**  
  - Campo para digitar;
  - Botão **Buscar (CNPJ)**:
    - Consulta dados na Receita (via BrasilAPI);
    - Preenche automaticamente:
      - Razão Social;
      - Endereço;
      - Telefone (se disponível).

- **Nome/Razão Social**
- **Contato (Pessoa)** – pessoa responsável na empresa.
- **Telefone e Celular**
- **E-mail (Login)** – será o login do cliente no sistema.
- **Senha** – senha inicial do acesso do cliente.
- **Endereço**:
  - **CEP** – ao informar, o sistema consulta o endereço na BrasilAPI;
  - Rua, Número, Complemento, Bairro, Cidade, Estado.

Ao salvar:

- Cria um registro na tabela `users` com nível **cliente**.
- Esse cliente poderá acessar o painel com o e-mail e senha cadastrados.

### 6.3. Edição de Cliente

Menu: **Clientes → Editar** (linha do cliente).

Permite alterar:

- Nome/Razão Social
- Contato
- Telefones
- E-mail
- Senha (se preenchido um novo valor)
- Endereço (com busca de CEP)
- **CNPJ**:
  - Somente **Administrador** pode alterar.
  - Gerente vê o CNPJ, mas não consegue editar.

---

## 7. Gestão de Contratos

### 7.1. Listagem de Contratos

Menu: **Contratos**

- **Administrador / Gerente**
  - Vê todos os contratos.
  - Botão **+ Novo Contrato**.
  - Colunas:
    - ID
    - Título
    - Cliente
    - Vigência (Data Início / Data Fim)
    - Valor
    - Status (Ativo, Pendente, Encerrado)
    - Ações (Detalhes / Editar)

- **Cliente**
  - Vê apenas os seus próprios contratos.
  - Não vê botão de criação.
  - A coluna “Cliente” pode estar oculta, já que sempre será o próprio.

---

### 7.2. Cadastro de Contrato

Menu: **Contratos → Novo Contrato**

#### 7.2.1. Selecionar Cliente

- Campo **Cliente** (select).
- Ao escolher um cliente:
  - O sistema exibe texto com:
    - Nome do cliente (Razão Social);
    - CNPJ;
    - Endereço.

#### 7.2.2. Serviços Contratados

- Campo de texto múltiplas linhas (textarea), com lista padrão:
  - Montagem e Manutenção de Computadores (incluindo Servidor).
  - Acesso Remoto quando possível.
  - Impressoras (Sistema).
  - Infraestrutura e Cabeamento (Mão de obra).
  - Sistema de Monitoramento (CFTV).
  - Sistema de telefonia PABX Analógico.

- Este campo é **totalmente editável**:
  - Pode adicionar / remover itens;
  - Cada linha se torna um item na cláusula de “Objeto” do contrato.

#### 7.2.3. Dados Comerciais

- **Título do Contrato**  
  Ex.: “Manutenção em TI – Plano Mensal”.

- **Valor Mensal (R$)**  
  Valor da mensalidade.

- **Data Início**  
  Data de início da vigência do contrato.

- **Data Fim (Opcional)**  
  Data final da vigência, se houver.

#### 7.2.4. Multa de Cancelamento

- Campo **Multa de Cancelamento**:
  - Opções:
    - **30% do valor restante**
    - **40% do valor restante**
    - **50% do valor restante**
  - Esse valor é salvo no campo `multa_rescisao_percent` e pode ser usado na cláusula de rescisão do contrato.

#### 7.2.5. Observações / Complementos

- Campo livre para:
  - Observações adicionais;
  - Anotações internas;
  - Cláusulas específicas complementares.

---

### 7.3. Edição de Contrato

Menu: **Contratos → Detalhes / Editar**

#### 7.3.1. Para Administrador / Gerente

Na tela de edição:

- **O contrato em si (texto jurídico) fica oculto** na tela.
- São exibidos apenas os campos de edição:

  - Título;
  - Cliente vinculado;
  - Valor mensal;
  - Data Início e Data Fim;
  - Status (Ativo/Pendente/Encerrado);
  - Serviços Contratados (textarea);
  - Observações/Complementos.

- Botões:
  - **Voltar** – retorna à lista de contratos;
  - **Salvar** – grava alterações;
  - **Imprimir** – gera o contrato completo e “seco” para impressão/PDF.

> Em algumas implementações, somente o **Administrador** tem acesso ao botão de exclusão definitiva do contrato.

#### 7.3.2. Para Cliente

- A mesma tela é usada, porém:
  - Todos os campos aparecem em modo **somente leitura**;
  - O cliente **não pode editar**;
  - Ele pode apenas:
    - Visualizar os dados;
    - Clicar em **Imprimir** para gerar o contrato em PDF/papel.

---

### 7.4. Impressão do Contrato

Ao clicar em **Imprimir** na tela de edição:

- O sistema aplica um estilo de impressão (`@media print`) que:
  - Esconde:
    - Menu lateral;
    - Títulos do sistema;
    - Botões;
    - Campos do formulário;
    - Qualquer informação do sistema.
  - Mantém visível apenas o bloco de conteúdo do contrato (**contrato seco**).

O contrato impresso conterá:

- Cabeçalho jurídico completo;
- Cláusula de Objeto com a lista de serviços do campo **Serviços Contratados**;
- Cláusulas de prazo, remuneração, atraso, obrigações, disposições gerais, rescisão, prejuízos e foro;
- Linhas para assinatura da **Dultec Soluções** e da **Contratante**;
- Local e data de assinatura.

---

## 8. Gestão de Usuários

### 8.1. Listagem de Usuários

Menu: **Usuários**

Disponível para:

- Administrador
- Gerente de Contrato

Mostra:

- Nome
- E-mail (login)
- Nível de acesso (Admin / Gerente / Cliente)
- Situação (Ativo / Inativo)

Ações:

- **Editar** – altera os dados;
- **Excluir** – apenas Administrador.

### 8.2. Cadastro de Usuário

Menu: **Usuários → Novo Usuário**

Campos:

- Nome completo
- RG, CPF
- Telefone, Celular
- E-mail (usado como login)
- Senha inicial
- Nível de acesso:
  - Administrador
  - Gerente de Contrato
  - Cliente
- Situação:
  - Ativo
  - Inativo
- Endereço (com busca de CEP)

### 8.3. Edição de Usuário

- Permite alterar qualquer dado cadastral.
- Campo de **Senha**:
  - Se vazio: senha atual é mantida;
  - Se preenchido: senha é atualizada.

### 8.4. Exclusão de Usuário

- Apenas Administrador pode excluir.
- Recomenda-se, em produção, preferir marcar como **Inativo**, para preservar histórico.

---

## 9. Configurações do Sistema

Menu: **Configurações** (somente Administrador)

### 9.1. Identidade Visual

- **Nome do Sistema**  
  - Aparece no título da aba, login e em outros pontos.

- **Logotipo**  
  - Upload de arquivo PNG/JPG;
  - Atualiza:
    - Logo do menu lateral;
    - Logo da tela de login;
    - Ícone da aba do navegador (favicon).

### 9.2. Tema de Cores

Três opções de tema:

- **Azul (padrão)**
- **Dark (escuro)**
- **Verde (natureza)**

Ao escolher um tema e salvar:

- As cores do menu lateral, botões e áreas principais são atualizadas automaticamente.

---

## 10. Boas Práticas de Uso

- Manter **usuários e clientes inativos** em vez de excluí-los, quando houver histórico de contratos.
- Atualizar periodicamente:
  - Razão social, CNPJ, endereço dos clientes;
  - Contatos responsáveis.
- Sempre revisar:
  - Lista de **Serviços Contratados**;
  - Valor mensal;
  - Multa de cancelamento (30/40/50%);
  - Datas de início e fim antes de gerar o contrato para assinatura.
- Após qualquer mudança na identidade visual (logotipo, cores), conferir:
  - Tela de login;
  - Menu lateral;
  - Ícone da aba do navegador.

---

**FIM DO MANUAL**