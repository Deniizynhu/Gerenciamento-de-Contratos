<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

checkRole(['admin', 'gerente']);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO users (nome, rg, cpf_cnpj, telefone, celular, email, senha, cep, logradouro, numero, complemento, bairro, cidade, estado, situacao, nivel) 
                VALUES (:nome, :rg, :cpf, :tel, :cel, :email, :senha, :cep, :rua, :num, :comp, :bairro, :cidade, :uf, :sit, :nivel)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $_POST['nome'],
            ':rg' => $_POST['rg'],
            ':cpf' => $_POST['cpf'],
            ':tel' => $_POST['telefone'],
            ':cel' => $_POST['celular'],
            ':email' => $_POST['email'],
            ':senha' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
            ':cep' => $_POST['cep'],
            ':rua' => $_POST['logradouro'],
            ':num' => $_POST['numero'],
            ':comp' => $_POST['complemento'],
            ':bairro' => $_POST['bairro'],
            ':cidade' => $_POST['cidade'],
            ':uf' => $_POST['estado'],
            ':sit' => $_POST['situacao'],
            ':nivel' => $_POST['nivel']
        ]);

        $msg = "<div class='alert success'>Usuário cadastrado com sucesso!</div>";
    } catch (PDOException $e) {
        if($e->getCode() == 23000) {
            $msg = "<div class='alert error'>Erro: E-mail já cadastrado.</div>";
        } else {
            $msg = "<div class='alert error'>Erro: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<div class="header-title">
    <h2>Cadastrar Usuário</h2>
</div>

<?= $msg ?>

<form method="POST" class="form-cadastro">
    <div class="form-grid">
        <!-- Coluna Esquerda: Dados Pessoais -->
        <div class="col-left">
            <h3 style="margin-top:0; color:#0056b3; font-size:1rem;">Dados Pessoais & Acesso</h3>
            
            <div class="input-group">
                <label>Nome Completo*</label>
                <input type="text" name="nome" required>
            </div>

            <div class="input-group">
                <label>RG</label>
                <input type="text" name="rg">
            </div>

            <div class="input-group">
                <label>CPF</label>
                <input type="text" name="cpf">
            </div>

            <div class="input-group">
                <label>Telefone</label>
                <input type="text" name="telefone">
            </div>

            <div class="input-group">
                <label>Celular</label>
                <input type="text" name="celular">
            </div>

            <div class="input-group">
                <label>E-mail (Login)*</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Senha Inicial*</label>
                <input type="password" name="senha" required>
            </div>

            <div class="input-group">
                <label>Nível de Acesso*</label>
                <select name="nivel" required>
                    <option value="admin">Administrador</option>
                    <option value="gerente">Gerente de Contrato</option>
                    <option value="cliente">Cliente</option>
                </select>
            </div>

            <div class="input-group">
                <label>Situação*</label>
                <select name="situacao" required>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
        </div>

        <!-- Coluna Direita: Endereço -->
        <div class="col-right">
            <h3 style="margin-top:0; color:#0056b3; font-size:1rem;">Endereço</h3>

            <div class="input-group">
                <label>CEP</label>
                <div class="search-box">
                    <input type="text" name="cep" id="cep" placeholder="00000-000">
                    <button type="button" onclick="buscarCEP()" class="btn-search">Buscar</button>
                </div>
            </div>

            <div class="input-group">
                <label>Rua</label>
                <input type="text" name="logradouro" id="logradouro">
            </div>

            <div class="input-group">
                <label>Número</label>
                <input type="text" name="numero">
            </div>

            <div class="input-group">
                <label>Complemento</label>
                <input type="text" name="complemento">
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
                <input type="text" name="estado" id="estado" maxlength="2">
            </div>
        </div>
    </div>

    <div class="form-footer">
        <a href="usuarios.php" class="btn-cancel">Voltar</a>
        <button type="submit" class="btn-save">Salvar Usuário</button>
    </div>
</form>

<script>
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
        } else {
            alert("CEP não encontrado.");
        }
    } else {
        alert("Digite um CEP válido.");
    }
}
</script>

<style>
    /* Estilos Reutilizados (Padrão do Sistema) */
    .form-cadastro { background: #f0f3f7; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .input-group { margin-bottom: 15px; display: flex; align-items: center; }
    .input-group label { width: 140px; font-weight: 600; color: #333; font-size: 0.9rem; flex-shrink: 0; }
    .input-group input, .input-group select { flex: 1; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; height: 38px; box-sizing: border-box; }
    
    /* Botões */
    .search-box { display: flex; flex: 1; gap: 10px; width: 100%; }
    .search-box input { flex: 1; }
    .btn-search { width: auto; background: #e9ecef; border: 1px solid #ced4da; padding: 0 15px; border-radius: 4px; cursor: pointer; }
    
    .form-footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; display: flex; justify-content: center; gap: 20px; }
    .btn-save, .btn-cancel { width: 180px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 1rem; cursor: pointer; border: none; font-weight: 500; text-decoration: none; }
    .btn-cancel { background: #6c757d; color: white; }
    .btn-save { background: #28a745; color: white; }

    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert.success { background: #d4edda; color: #155724; }
    .alert.error { background: #f8d7da; color: #721c24; }

    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; gap: 0; } .input-group { flex-direction: column; align-items: stretch; } .input-group label { width: 100%; margin-bottom: 5px; } }
</style>

<?php require 'includes/footer.php'; ?>