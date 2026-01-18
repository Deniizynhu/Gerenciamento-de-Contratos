<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// Verifica permissão geral
checkRole(['admin', 'gerente']);

// Verifica se tem ID na URL
if (!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit;
}

$id = $_GET['id'];
$msg = '';

// Buscar dados atuais do cliente
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND nivel = 'cliente'");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    echo "<div class='alert error'>Cliente não encontrado.</div>";
    require 'includes/footer.php';
    exit;
}

// --- PROCESSAMENTO DO FORMULÁRIO (SALVAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Montagem dinâmica da Query
        $sql = "UPDATE users SET 
                nome = :nome, 
                email = :email, 
                contato = :contato, 
                telefone = :telefone, 
                celular = :celular, 
                cep = :cep, 
                logradouro = :logradouro, 
                numero = :numero, 
                complemento = :complemento, 
                bairro = :bairro, 
                cidade = :cidade, 
                estado = :estado";

        $dados = [
            ':nome' => $_POST['nome'],
            ':email' => $_POST['email'],
            ':contato' => $_POST['contato'],
            ':telefone' => $_POST['telefone'],
            ':celular' => $_POST['celular'],
            ':cep' => $_POST['cep'],
            ':logradouro' => $_POST['logradouro'],
            ':numero' => $_POST['numero'],
            ':complemento' => $_POST['complemento'],
            ':bairro' => $_POST['bairro'],
            ':cidade' => $_POST['cidade'],
            ':estado' => $_POST['estado'],
            ':id' => $id
        ];

        // Regra: Apenas ADMIN pode alterar CPF/CNPJ
        if ($_SESSION['user_role'] == 'admin') {
            $sql .= ", cpf_cnpj = :cpf_cnpj";
            $dados[':cpf_cnpj'] = $_POST['cpf_cnpj'];
        }

        // Regra: Só altera senha se o campo não estiver vazio
        if (!empty($_POST['senha'])) {
            $sql .= ", senha = :senha";
            $dados[':senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($dados);
        
        // Atualiza os dados na tela
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();

        $msg = "<div class='alert success'>Dados atualizados com sucesso!</div>";

    } catch (PDOException $e) {
        $msg = "<div class='alert error'>Erro ao atualizar: " . $e->getMessage() . "</div>";
    }
}

// Define se o campo CNPJ é somente leitura (Gerente)
$readonlyCNPJ = ($_SESSION['user_role'] != 'admin') ? 'readonly style="background-color: #e9ecef; cursor: not-allowed;"' : '';
?>

<div class="header-title">
    <h2>Editar Cliente: <?= htmlspecialchars($cliente['nome']) ?></h2>
</div>

<?= $msg ?>

<form method="POST" class="form-cadastro">
    <div class="form-grid">
        <!-- Coluna Esquerda -->
        <div class="col-left">
            
            <div class="input-group">
                <label>CPF/CNPJ</label>
                <div class="search-box">
                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" value="<?= htmlspecialchars($cliente['cpf_cnpj']) ?>" <?= $readonlyCNPJ ?>>
                    
                    <!-- Botão de buscar CNPJ só aparece para Admin -->
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <button type="button" onclick="buscarCNPJ()" class="btn-search">Buscar (CNPJ)</button>
                    <?php endif; ?>
                </div>
                <?php if($_SESSION['user_role'] != 'admin'): ?>
                    <small style="display:block; margin-left:140px; font-size:0.8em; color:red;">Apenas administradores alteram CNPJ.</small>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label>Nome/Razão Social*</label>
                <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
            </div>

            <div class="input-group">
                <label>Contato (Pessoa):</label>
                <input type="text" name="contato" value="<?= htmlspecialchars($cliente['contato']) ?>">
            </div>

            <div class="input-group">
                <label>Telefone:</label>
                <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">
            </div>

            <div class="input-group">
                <label>Celular:</label>
                <input type="text" name="celular" value="<?= htmlspecialchars($cliente['celular']) ?>">
            </div>

            <div class="input-group">
                <label>Email (Login)*</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
            </div>

            <div class="input-group">
                <label>Nova Senha</label>
                <div class="password-box">
                    <input type="password" name="senha" id="senha" placeholder="Deixe em branco para manter a atual">
                    <span class="toggle-pass" onclick="toggleSenha()">👁️</span>
                </div>
            </div>

        </div>

        <!-- Coluna Direita -->
        <div class="col-right">
            
            <div class="input-group">
                <label>CEP</label>
                <input type="text" name="cep" id="cep" value="<?= htmlspecialchars($cliente['cep']) ?>" onblur="buscarCEP()">
            </div>

            <div class="input-group">
                <label>Rua</label>
                <input type="text" name="logradouro" id="logradouro" value="<?= htmlspecialchars($cliente['logradouro']) ?>">
            </div>

            <div class="input-group">
                <label>Número</label>
                <input type="text" name="numero" id="numero" value="<?= htmlspecialchars($cliente['numero']) ?>">
            </div>

            <div class="input-group">
                <label>Complemento</label>
                <input type="text" name="complemento" id="complemento" value="<?= htmlspecialchars($cliente['complemento']) ?>">
            </div>

            <div class="input-group">
                <label>Bairro</label>
                <input type="text" name="bairro" id="bairro" value="<?= htmlspecialchars($cliente['bairro']) ?>">
            </div>

            <div class="input-group">
                <label>Cidade</label>
                <input type="text" name="cidade" id="cidade" value="<?= htmlspecialchars($cliente['cidade']) ?>">
            </div>

            <div class="input-group">
                <label>Estado</label>
                <select name="estado" id="estado">
                    <option value="">Selecione...</option>
                    <?php 
                        $estados = ['SP','RJ','MG','PR','SC','RS','ES','BA','PE','CE','AM','GO','DF']; 
                        foreach($estados as $uf): 
                            $selected = ($cliente['estado'] == $uf) ? 'selected' : '';
                    ?>
                        <option value="<?= $uf ?>" <?= $selected ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>

    <div class="form-footer">
        <a href="clientes.php" class="btn-cancel">Voltar</a>
        <button type="submit" class="btn-save">Atualizar Dados</button>
    </div>
</form>

<script>
// Mesmos scripts da tela de cadastro
async function buscarCNPJ() {
    let cnpj = document.getElementById('cpf_cnpj').value.replace(/\D/g, '');
    if (cnpj.length !== 14) { alert("CNPJ inválido."); return; }
    document.getElementById('nome').value = "Buscando...";
    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error();
        const data = await response.json();
        document.getElementById('nome').value = data.razao_social;
        document.getElementById('telefone').value = data.ddd_telefone_1;
        document.getElementById('cep').value = data.cep;
        document.getElementById('logradouro').value = data.logradouro;
        document.getElementById('numero').value = data.numero;
        document.getElementById('bairro').value = data.bairro;
        document.getElementById('cidade').value = data.municipio;
        document.getElementById('estado').value = data.uf;
    } catch (e) { alert("Erro ao buscar CNPJ."); }
}

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
function toggleSenha() {
    let input = document.getElementById('senha');
    input.type = input.type === "password" ? "text" : "password";
}
</script>

<style>
    /* CSS REUTILIZADO DO CADASTRO */
    .form-cadastro { background: #f0f3f7; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .input-group { margin-bottom: 15px; display: flex; align-items: center; }
    .input-group label { width: 140px; font-weight: 600; color: #333; font-size: 0.9rem; flex-shrink: 0; }
    .input-group input, .input-group select { flex: 1; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; height: 38px; box-sizing: border-box; }
    
    /* Busca CNPJ */
    .search-box { display: flex; flex: 1; gap: 8px; width: 100%; }
    .search-box input { width: 100%; flex-grow: 1; }
    .btn-search { flex: 0 0 auto; width: auto; background: #e9ecef; border: 1px solid #ced4da; color: #333; padding: 0 15px; border-radius: 4px; cursor: pointer; height: 38px; }
    .btn-search:hover { background: #dde0e3; }

    /* Senha */
    .password-box { position: relative; flex: 1; display: flex; }
    .password-box input { width: 100%; padding-right: 35px; }
    .toggle-pass { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; }

    /* Botões */
        /* --- RODAPÉ DO FORMULÁRIO (BOTÕES) --- */
    .form-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
        
        /* Centraliza os botões */
        display: flex;
        justify-content: center; 
        gap: 20px; /* Espaço entre eles */
    }

    /* Estilo Base para AMBOS os botões (para ficarem iguais) */
    .btn-save, .btn-cancel {
        width: 180px;        /* Largura fixa igual para os dois */
        height: 45px;        /* Altura fixa igual */
        display: flex;       /* Ajuda a centralizar o texto dentro do botão */
        align-items: center;
        justify-content: center;
        
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        text-decoration: none; /* Remove sublinhado do link 'Voltar' */
        border: none;
        font-weight: 500;
        transition: background 0.2s, transform 0.1s;
    }

    /* Cores Específicas */
    .btn-cancel {
        background: #6c757d; /* Cinza */
        color: white;
    }
    
    .btn-save {
        background: #28a745; /* Verde */
        color: white;
    }

    /* Efeito Hover (passar o mouse) */
    .btn-cancel:hover { background: #5a6268; }
    .btn-save:hover { background: #218838; }

    /* Efeito de clique */
    .btn-save:active, .btn-cancel:active {
        transform: scale(0.98);
    }
    }
</style>

<?php require 'includes/footer.php'; ?>