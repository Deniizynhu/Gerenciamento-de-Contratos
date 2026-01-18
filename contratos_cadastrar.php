<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// Apenas Admin e Gerente podem criar contratos
checkRole(['admin', 'gerente']);

// Busca clientes
$stmt = $pdo->query("SELECT * FROM users WHERE nivel = 'cliente' ORDER BY nome ASC");
$clientes = $stmt->fetchAll();

// Monta JSON para o JS atualizar cabeçalho
$clientesJson = [];
foreach ($clientes as $c) {
    $endereco = trim(($c['logradouro'] ?? '') . ' ' . ($c['numero'] ?? ''));
    if (!empty($c['complemento'])) $endereco .= ' - ' . $c['complemento'];
    if (!empty($c['bairro']))      $endereco .= ' - ' . $c['bairro'];
    if (!empty($c['cidade']) || !empty($c['estado'])) $endereco .= ' - ' . $c['cidade'] . '/' . $c['estado'];
    if (!empty($c['cep']))         $endereco .= ' - CEP: ' . $c['cep'];

    $clientesJson[$c['id']] = [
        'nome'     => $c['nome'],
        'cpf_cnpj' => $c['cpf_cnpj'],
        'endereco' => $endereco
    ];
}

// Texto padrão dos serviços (EDITÁVEL por contrato)
$defaultServicos = "Montagem e Manutenção de Computadores (incluindo Servidor).\nAcesso Remoto quando possível.\nImpressoras (Sistema).\nInfraestrutura e Cabeamento (Mão de obra).\nSistema de Monitoramento (CFTV).\nSistema de telefonia PABX Analógico.";

$msg = '';

// Salvar contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO contratos 
                (cliente_id, titulo, servicos, descricao, valor, multa_rescisao_percent, data_inicio, data_fim, status)
                VALUES 
                (:cliente_id, :titulo, :servicos, :descricao, :valor, :multa, :data_inicio, :data_fim, 'ativo')";
        $stmtIns = $pdo->prepare($sql);
        $stmtIns->execute([
            ':cliente_id' => $_POST['cliente_id'],
            ':titulo'     => $_POST['titulo'],
            ':servicos'   => $_POST['servicos'] ?? '',
            ':descricao'  => $_POST['descricao'] ?? '',
            ':valor'      => str_replace(',', '.', $_POST['valor']),
            ':multa'      => (int)($_POST['multa_rescisao_percent'] ?? 30),
            ':data_inicio'=> $_POST['data_inicio'],
            ':data_fim'   => $_POST['data_fim'] ?: null
        ]);

        $msg = "<div class='alert success'>Contrato criado com sucesso. <a href='contratos.php'>Voltar para a lista</a></div>";
    } catch (PDOException $e) {
        $msg = "<div class='alert error'>Erro ao salvar contrato: " . $e->getMessage() . "</div>";
    }
}

// Valor que aparece no textarea (se der erro, mantém o que foi digitado)
$servicosForm = $_POST['servicos'] ?? $defaultServicos;
// Valor selecionado na multa (padrão 30%)
$multaForm    = $_POST['multa_rescisao_percent'] ?? 30;
?>

<div class="header-title">
    <h2>Novo Contrato</h2>
</div>

<?= $msg ?>

<form method="POST" class="form-cadastro">
    
    <!-- 1. Seleção do Cliente + Cabeçalho Resumido -->
    <div class="section-box">
        <h3>1. Selecionar Cliente</h3>
        <div class="input-group" style="max-width: 500px;">
            <label>Cliente:</label>
            <select name="cliente_id" id="clienteSelect" onchange="atualizarCabecalho()" required>
                <option value="">-- Selecione um cliente --</option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <p class="legal-text" style="margin-top:15px;">
            Pelo presente instrumento particular de um lado <strong>Dultec Soluções</strong>, pessoa Jurídica de direito privado inscrita no CNPJ sob o nº <strong>52.652.825/0001-94</strong>, com sede na Rua Altamira, 304 – Parque Novo Oratório, Santo André/SP, neste ato representado pelo proprietário Dênis Baptista da Silva, doravante denominado CONTRATADA e, de outro lado
            <br><br>
            <span id="txt_nome" class="highlight">________________________</span>, 
            pessoa jurídica de direito privado, inscrita no CNPJ sob o nº <span id="txt_cnpj" class="highlight">______________________</span>, 
            com sede em <span id="txt_endereco" class="highlight">Rua _________________________________________</span>.
        </p>
    </div>

    <!-- 2. Lista de Serviços (EDITÁVEL) -->
    <div class="section-box">
        <h3>2. Serviços Contratados (editáveis por contrato)</h3>
        <p style="font-size:0.9rem; color:#555;">
            Edite livremente a lista abaixo. Cada linha será um item de serviço no contrato:
        </p>
        <textarea name="servicos" rows="6" style="width:100%; padding:10px; border:1px solid #ced4da; border-radius:4px; font-family:inherit;"><?= htmlspecialchars($servicosForm) ?></textarea>
    </div>

    <!-- 3. Dados Comerciais -->
    <div class="section-box">
        <h3>3. Dados Comerciais</h3>
        <div class="form-grid">
            <div class="col-left">
                <div class="input-group">
                    <label>Título do Contrato</label>
                    <input type="text" name="titulo" placeholder="Ex: Manutenção Mensal TI" required>
                </div>
                <div class="input-group">
                    <label>Valor Mensal (R$)</label>
                    <input type="number" step="0.01" name="valor" placeholder="0,00" required>
                </div>
            </div>
            <div class="col-right">
                <div class="input-group">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" required>
                </div>
                <div class="input-group">
                    <label>Data Fim (Opcional)</label>
                    <input type="date" name="data_fim">
                </div>
            </div>
        </div>

        <!-- NOVO: Multa de Rescisão -->
        <div class="input-group" style="margin-top: 10px;">
            <label>Multa de Cancelamento</label>
            <select name="multa_rescisao_percent" style="max-width: 200px;">
                <option value="30" <?= $multaForm == 30 ? 'selected' : '' ?>>30% do valor restante</option>
                <option value="40" <?= $multaForm == 40 ? 'selected' : '' ?>>40% do valor restante</option>
                <option value="50" <?= $multaForm == 50 ? 'selected' : '' ?>>50% do valor restante</option>
            </select>
        </div>

        <div class="input-group" style="margin-top: 15px;">
            <label style="width: 100%; margin-bottom: 5px;">Observações / Complementos:</label>
            <textarea name="descricao" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;"></textarea>
        </div>
    </div>

    <div class="form-footer">
        <a href="contratos.php" class="btn-cancel">Voltar</a>
        <button type="submit" class="btn-save">Gerar Contrato</button>
    </div>
</form>

<script>
const dbClientes = <?= json_encode($clientesJson) ?>;

function atualizarCabecalho() {
    const id = document.getElementById('clienteSelect').value;
    const txtNome = document.getElementById('txt_nome');
    const txtCnpj = document.getElementById('txt_cnpj');
    const txtEnd  = document.getElementById('txt_endereco');

    if (id && dbClientes[id]) {
        const c = dbClientes[id];
        txtNome.innerHTML = '<strong>' + (c.nome || '').toUpperCase() + '</strong>';
        txtNome.classList.remove('highlight');

        txtCnpj.textContent = c.cpf_cnpj || 'Não informado';
        txtCnpj.classList.remove('highlight');

        txtEnd.textContent = c.endereco || 'Endereço não cadastrado';
        txtEnd.classList.remove('highlight');
    } else {
        txtNome.innerHTML = '________________________';
        txtNome.classList.add('highlight');

        txtCnpj.textContent = '______________________';
        txtCnpj.classList.add('highlight');

        txtEnd.textContent = 'Rua _________________________________________';
        txtEnd.classList.add('highlight');
    }
}
</script>

<style>
    .form-cadastro { background: #f0f3f7; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .section-box { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #e1e4e8; }
    .section-box h3 { margin-top:0; margin-bottom:10px; color:#0056b3; }
    .legal-text { font-family:'Times New Roman', serif; font-size:1rem; line-height:1.6; text-align:justify; }
    .highlight { color:#999; background:#f8f9fa; padding:0 4px; border-radius:3px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:30px; }
    .input-group { margin-bottom:15px; }
    .input-group label { display:block; font-weight:600; margin-bottom:5px; }
    .input-group input, .input-group select, .input-group textarea { width:100%; padding:8px 10px; border:1px solid #ced4da; border-radius:4px; box-sizing:border-box; }

    .form-footer { margin-top:20px; padding-top:15px; border-top:1px solid #ddd; display:flex; justify-content:center; gap:20px; }
    .btn-save, .btn-cancel { width:180px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:4px; border:none; font-size:1rem; font-weight:500; text-decoration:none; cursor:pointer; }
    .btn-save { background:#28a745; color:#fff; }
    .btn-cancel { background:#6c757d; color:#fff; }
    .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    .alert.success { background:#d4edda; color:#155724; }
    .alert.error { background:#f8d7da; color:#721c24; }

    @media(max-width:768px){
        .form-grid { grid-template-columns:1fr; }
    }
</style>

<?php require 'includes/footer.php'; ?>