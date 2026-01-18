# Gerenciamento-de-Contratos
Sistema para gerenciar contratos de suporte em TI, simples, sem geração de contratos. Apenas controle interno (Controle de vencimentos)

Manual de Instalação e Uso

Passo 1: Preparar o Ambiente
Baixe e instale o XAMPP (se estiver no Windows) ou MAMP/WAMP.
Inicie os serviços Apache e MySQL no painel de controle do XAMPP.

Passo 2: Instalar os Arquivos
Vá até a pasta de instalação do XAMPP (geralmente C:\xampp\htdocs).
Crie uma pasta chamada dultec_sistema.
Coloque todos os arquivos PHP, CSS e a pasta assets dentro dela.
Importante: Certifique-se de colocar um arquivo de imagem chamado logo.png dentro da pasta assets para o sistema iniciar com um logo.

Passo 3: Configurar o Banco de Dados
Abra seu navegador e digite: http://localhost/phpmyadmin.
Clique em "Novo" no menu esquerdo e crie um banco chamado dultec_db.
Clique no banco criado, vá na aba SQL (no topo).
Copie todo o código do item 2. Banco de Dados Completo (SQL) acima, cole na caixa de texto e clique em Executar.
Nota: Se você já tinha o banco antigo, pode selecionar todas as tabelas e clicar em "Eliminar" (Drop) antes de rodar o código novo, para garantir que tudo esteja limpo.

Passo 4: Verificar Conexão
Abra o arquivo config/db.php no seu editor de código.
Verifique se as credenciais estão corretas (padrão XAMPP): PHP
$host = 'localhost';
$db   = 'dultec_db';
$user = 'root';
$pass = ''; // No XAMPP a senha padrão é vazia

Passo 5: Testar o Sistema
Acesse no navegador: http://localhost/dultec_sistema.
Você será redirecionado para o Login.
Use as credenciais de Administrador:
E-mail: admin@dultec.com.br
Senha: password

Passo 6: Usando o Sistema
Menu Configurações: Vá imediatamente em Configurações para fazer o upload do seu logotipo oficial e escolher a cor do tema.
Menu Usuários: Cadastre seus funcionários (Gerentes).
Menu Clientes: Cadastre os clientes usando a busca automática de CNPJ.
Menu Contratos: Gere contratos. O gráfico no Dashboard aparecerá automaticamente assim que houver contratos ativos.

Solução de Problemas Comuns
Gráfico não aparece: Verifique se o computador tem acesso à internet (o sistema usa Chart.js via CDN).
Erro de Permissão ao Salvar Logo: Se estiver usando Linux/Mac, dê permissão de escrita na pasta assets (chmod 777 assets). No Windows (XAMPP), isso geralmente é automático.
Senha Inválida: Se a senha password não funcionar, certifique-se de que copiou o código Hash correto no SQL ou use o script de reset de senha que forneço abaixo.
Fazendo o reset: digite no final do endereço o nome do arquivo reset_senha.php (exemplo: http://localhost/contratos/reset_senha.php)
