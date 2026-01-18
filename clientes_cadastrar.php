<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// Apenas Admin e Gerente podem cadastrar clientes
checkRole(['admin', 'gerente']);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO users (nome, email, senha, nivel, cpf_cnpj, contato, telefone, celular, cep, logradouro, numero, complemento, bairro, cidade, estado) 
                VALUES (:nome, :email, :senha, 'cliente', :cpf_cnpj, :contato, :telefone, :celular, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado)";
        
        $stmt = $pdo->prepare($sql);
        
        $dados = [
            ':nome' => $_POST['nome'],
            ':email' => $_POST['email'],
            ':senha' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
            ':cpf_cnpj' => $_POST['cpf_cnpj'],
            ':contato' => $_POST['contato'],
            ':telefone' => $_POST['telefone'],
            ':celular' => $_POST['celular'],
            ':cep' => $_POST['cep'],
            ':logradouro' => $_POST['logradouro'],
            ':numero' => $_POST['numero'],
            ':complemento' => $_POST['complemento'],
            ':bairro' => $_POST['bairro'],
            ':cidade' => $_POST['cidade'],
            ':estado' => $_POST['estado']
        ];

        $stmt->execute($dados);
        $msg = "<div class='alert success'>Cliente cadastrado com sucesso!</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $msg = "<div class='alert error'>Erro: Este E-mail já está cadastrado.</div>";
        } else {
            $msg = "<div class='alert error'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<div class="header-title">
    <h2>Cadastro de Cliente</h2>
</div>

<?= $msg ?>

<form method="POST" class="form-cadastro">
    <div class="form-grid">
        <!-- Coluna Esquerda -->
        <div class="col-left">
            
    <div class="input-group">
    <label>CPF/CNPJ</label>
    <div class="search-box">
        <input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Digite apenas números">
        <button type="button" onclick="buscarCNPJ()" class="btn-search">Buscar CNPJ</button>
    </div>
</div>

            <div class="input-group">
                <label>Nome/Razão Social*</label>
                <input type="text" name="nome" id="nome" required>
            </div>

            <div class="input-group">
                <label>Contato (Pessoa):</label>
                <input type="text" name="contato">
            </div>

            <div class="input-group">
                <label>Telefone:</label>
                <input type="text" name="telefone" id="telefone">
            </div>

            <div class="input-group">
                <label>Celular:</label>
                <input type="text" name="celular">
            </div>

            <div class="input-group">
                <label>Email (Login)*</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-group">
                <label>Senha*</label>
                <div class="password-box">
                    <input type="password" name="senha" id="senha" required>
                    <span class="toggle-pass" onclick="toggleSenha()">👁️</span>
                </div>
            </div>

        </div>

        <!-- Coluna Direita -->
        <div class="col-right">
            
            <div class="input-group">
                <label>CEP</label>
                <input type="text" name="cep" id="cep" onblur="buscarCEP()">
            </div>

            <div class="input-group">
                <label>Rua</label>
                <input type="text" name="logradouro" id="logradouro">
            </div>

            <div class="input-group">
                <label>Número</label>
                <input type="text" name="numero" id="numero">
            </div>

            <div class="input-group">
                <label>Complemento</label>
                <input type="text" name="complemento" id="complemento">
            </div>

            <div class="input-group">
                <label>Bairro</label>
                <input type="text" name="bairro" id="bairro">
            </div>

            <div class="input-group">
                <label>Cidade</label>
                <input type="text" name="cidade" id="cidade">
            </div>

            <div class="input-group">
                <label>Estado</label>
                <select name="estado" id="estado">
                    <option value="">Selecione...</option>
                    <option value="SP">SP</option> <option value="RJ">RJ</option> 
                    <option value="MG">MG</option> <option value="PR">PR</option>
                    <option value="SC">SC</option> <option value="RS">RS</option>
                    <!-- Adicione outros estados conforme necessário -->
                </select>
            </div>

        </div>
    </div>

    <div class="form-footer">
        <button type="submit" class="btn-save">Salvar Cadastro</button>
    </div>
</form>

<script>
// Função para Buscar CNPJ na API pública (BrasilAPI)
async function buscarCNPJ() {
    let cnpj = document.getElementById('cpf_cnpj').value.replace(/\D/g, '');
    
    if (cnpj.length !== 14) {
        alert("Digite um CNPJ válido com 14 dígitos.");
        return;
    }

    document.getElementById('nome').value = "Buscando...";

    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error('Erro ao buscar CNPJ');
        
        const data = await response.json();

        document.getElementById('nome').value = data.razao_social;
        document.getElementById('email').value = data.email || ''; // Algumas empresas não tem email na Receita
        document.getElementById('telefone').value = data.ddd_telefone_1;
        document.getElementById('cep').value = data.cep;
        document.getElementById('logradouro').value = data.logradouro;
        document.getElementById('numero').value = data.numero;
        document.getElementById('complemento').value = data.complemento;
        document.getElementById('bairro').value = data.bairro;
        document.getElementById('cidade').value = data.municipio;
        document.getElementById('estado').value = data.uf;

    } catch (error) {
        alert("CNPJ não encontrado ou erro na API.");
        document.getElementById('nome').value = "";
    }
}

// Função para buscar CEP
async function buscarCEP() {
    let cep = document.getElementById('cep').value.replace(/\D/g, '');
    if(cep.length === 8) {
        const response = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`);
        if(response.ok) {
            const data = await response.json();
            document.getElementById('logradouro').value = data.street;
            document.getElementById('bairro').value = data.neighborhood;
            document.getElementById('cidade').value = data.city;
            document.getElementById('estado').value = data.state;
        }
    }
}

// Função para mostrar/esconder senha
function toggleSenha() {
    let input = document.getElementById('senha');
    input.type = input.type === "password" ? "text" : "password";
}
</script>

<!-- Estilos específicos desta página para ficar igual a foto -->
<style>
    /* Container principal do formulário */
    .form-cadastro {
        background: #f0f3f7; /* Fundo azulado claro */
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Grid de duas colunas */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Divide em 50% / 50% */
        gap: 40px; /* Espaço entre a coluna esquerda e direita */
    }

    /* Grupo de cada linha (Label + Input) */
    .input-group {
        margin-bottom: 15px;
        display: flex;
        align-items: center; /* Centraliza verticalmente */
    }

    /* Estilo dos Labels (Nomes dos campos) */
    .input-group label {
        width: 140px; /* Largura fixa para alinhar todos os campos */
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        flex-shrink: 0; /* Garante que o label não diminua */
    }

    /* Estilo padrão para todos os inputs e selects */
    .input-group input, 
    .input-group select {
        flex: 1; /* Ocupa todo o espaço restante */
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background: #fff;
        outline: none;
        font-size: 0.95rem;
        color: #495057;
        height: 38px; /* Altura padrão para alinhar com botões */
        box-sizing: border-box; /* Garante que padding não aumente o tamanho total */
    }

    .input-group input:focus, 
    .input-group select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* --- CORREÇÃO DO CAMPO CPF/CNPJ E BOTÃO --- */
        /* --- Correção da Caixa de Busca --- */
    .search-box {
        display: flex;       /* Coloca um ao lado do outro */
        width: 100%;         /* Garante que a caixa ocupe a linha toda */
        gap: 10px;           /* Espaço entre o campo e o botão */
    }

    /* O Campo de Texto (Input) */
    .search-box input {
        flex: 1;             /* MÁGICA: Ordena o input ocupar TODO o espaço sobrando */
        width: 100%;         /* Garante a largura */
        min-width: 0;        /* Evita quebras em telas pequenas */
    }

    /* O Botão */
    .btn-search {
        flex: 0 0 auto;      /* Ordena o botão a NÃO crescer nem diminuir */
        width: auto;         /* A largura será apenas o tamanho do texto */
        background: #e9ecef;
        border: 1px solid #ced4da;
        color: #333;
        padding: 0 20px;     /* Espaçamento interno */
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        white-space: nowrap; /* Garante que o texto "Buscar (CNPJ)" fique numa linha só */
        height: 38px;        /* Altura igual ao input */
    }
    
    .btn-search:hover {
        background: #dde0e3;
    }

    /* --- CAMPO DE SENHA --- */
    .password-box {
        position: relative;
        flex: 1;
        display: flex;
    }
    .password-box input {
        width: 100%;
        padding-right: 35px; /* Espaço para o ícone do olho não ficar em cima do texto */
    }
    .toggle-pass {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 1.1rem;
        color: #666;
        user-select: none;
    }

    /* --- RODAPÉ DO FORMULÁRIO (BOTÃO SALVAR) --- */
    .form-footer {
        margin-top: 30px;
        text-align: right;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .btn-save {
        background: #28a745; /* Verde Sucesso */
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-save:hover {
        background: #218838;
    }
    
    /* Mensagens de erro/sucesso */
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; }
    .alert.success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .alert.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

    /* RESPONSIVIDADE (Celular) */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr; /* Vira uma coluna só */
            gap: 0;
        }
        
        .input-group {
            flex-direction: column; /* Label em cima, input embaixo */
            align-items: stretch;
        }
        
        .input-group label {
            width: 100%;
            margin-bottom: 5px;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

<?php require 'includes/footer.php'; ?>