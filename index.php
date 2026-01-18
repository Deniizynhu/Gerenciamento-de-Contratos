<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// --- CONFIGURAÇÃO DE SEGURANÇA (FILTRO DE CLIENTE) ---
$id_usuario = $_SESSION['user_id'];
$nivel_usuario = $_SESSION['user_role'];

// Variável que será injetada em todos os SQLs
$filtro_sql = "";

if ($nivel_usuario == 'cliente') {
    // Se for cliente, OBRIGATORIAMENTE filtra pelo ID dele
    $filtro_sql = " AND cliente_id = '$id_usuario' ";
}

// --- 1. DADOS DOS CARDS ---
$hoje = date('Y-m-d');
$dataLimite = date('Y-m-d', strtotime('+90 days'));

// Totais (Aplica o filtro)
$sqlTotal = "SELECT COUNT(*) as qtd, SUM(valor) as total FROM contratos WHERE status = 'ativo' $filtro_sql";
$stmt = $pdo->query($sqlTotal);
$stats = $stmt->fetch();

$totalContratos = $stats['qtd'] ?? 0;
$valorTotal = $stats['total'] ?? 0;

// Lista de Vencimentos (Aplica o filtro na tabela 'c' de contratos)
// Note que precisamos especificar c.cliente_id pois há join
$filtro_venc = ($nivel_usuario == 'cliente') ? " AND c.cliente_id = '$id_usuario' " : "";

$sqlVenc = "SELECT c.data_fim, u.nome 
            FROM contratos c 
            JOIN users u ON c.cliente_id = u.id 
            WHERE c.status = 'ativo' 
            AND c.data_fim BETWEEN '$hoje' AND '$dataLimite' 
            $filtro_venc 
            ORDER BY c.data_fim ASC";
$vencimentos = $pdo->query($sqlVenc)->fetchAll();

// --- 2. DADOS DO GRÁFICO (Últimos 12 Meses) ---
$labels = [];
$dataReceita = [];
$dataAtivos = [];
$dataVencem = [];

for ($i = 11; $i >= 0; $i--) {
    $mesIni = date('Y-m-01', strtotime("-$i months"));
    $mesFim = date('Y-m-t', strtotime("-$i months"));
    
    $mesNome = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    $mesNum = (int)date('m', strtotime($mesIni)) - 1;
    $labels[] = $mesNome[$mesNum] . '/' . date('y', strtotime($mesIni));

    // A. Receita e Ativos (Com Filtro)
    $sqlGrafico = "SELECT COUNT(*) as qtd, SUM(valor) as receita 
                   FROM contratos 
                   WHERE status = 'ativo' 
                   AND data_inicio <= '$mesFim' 
                   AND (data_fim IS NULL OR data_fim >= '$mesIni')
                   $filtro_sql"; // <--- Filtro aqui
                   
    $dadosMes = $pdo->query($sqlGrafico)->fetch();
    
    $dataReceita[] = (float)($dadosMes['receita'] ?? 0); 
    $dataAtivos[]  = (int)($dadosMes['qtd'] ?? 0);

    // B. Vencimentos (Com Filtro)
    $sqlVencMes = "SELECT COUNT(*) as qtd FROM contratos 
                   WHERE status = 'ativo' 
                   AND data_fim BETWEEN '$mesIni' AND '$mesFim' 
                   $filtro_sql"; // <--- Filtro aqui
                   
    $vencMes = $pdo->query($sqlVencMes)->fetch();
    $dataVencem[] = (int)($vencMes['qtd'] ?? 0);
}
?>

<h1>Dashboard</h1>
<p style="color: #666;">
    Visão geral 
    <?php if($nivel_usuario == 'cliente') echo "dos seus contratos"; else echo "do sistema"; ?>
</p>

<!-- CARDS -->
<div class="dashboard-cards">
    <div class="card">
        <h3>Contratos Ativos</h3>
        <p class="number"><?= $totalContratos ?></p>
        <small style="color: green;">Em andamento</small>
    </div>

    <div class="card">
        <h3>Valor Mensal</h3>
        <p class="number">R$ <?= number_format($valorTotal, 2, ',', '.') ?></p>
        <small style="color: #0056b3;">Total contratado</small>
    </div>

    <div class="card card-alert">
        <h3>⚠️ A Vencer (Próx. 90 dias)</h3>
        <?php if(count($vencimentos) > 0): ?>
            <ul class="list-vencimentos">
                <?php foreach($vencimentos as $v): ?>
                    <li>
                        <span class="date-badge"><?= date('d/m/Y', strtotime($v['data_fim'])) ?></span>
                        <span class="client-name"><?= htmlspecialchars($v['nome']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">✅ Nenhum vencimento próximo.</div>
        <?php endif; ?>
    </div>
</div>

<!-- GRÁFICO -->
<div class="chart-container-box">
    <h3>Histórico (Últimos 12 Meses)</h3>
    <div class="chart-wrapper">
        <canvas id="myChart"></canvas>
    </div>
</div>

<style>
    /* Estilos Dashboard */
    .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 30px; }
    .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee; }
    .card-alert h3 { color: #d9534f; margin-top: 0; font-size: 1.1rem; }
    .list-vencimentos { list-style: none; padding: 0; margin: 15px 0 0 0; max-height: 150px; overflow-y: auto; }
    .list-vencimentos li { display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
    .date-badge { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; margin-right: 10px; border: 1px solid #ffeeba; }
    .client-name { font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .empty-state { text-align: center; padding: 20px 0; color: #888; font-size: 0.9rem; }
    
    /* Gráfico */
    .chart-container-box { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee; margin-top: 30px; }
    .chart-wrapper { position: relative; height: 400px; width: 100%; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    if (typeof Chart === 'undefined') {
        document.querySelector('.chart-wrapper').innerHTML = '<p style="color:red; text-align:center;">Erro ao carregar gráfico.</p>';
    } else {
        const ctx = document.getElementById('myChart').getContext('2d');
        const labels = <?= json_encode($labels) ?>;
        const receita = <?= json_encode($dataReceita) ?>;
        const ativos = <?= json_encode($dataAtivos) ?>;
        const vencimentos = <?= json_encode($dataVencem) ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Valor (R$)', data: receita, backgroundColor: 'rgba(0, 86, 179, 0.6)', borderColor: 'rgba(0, 86, 179, 1)', borderWidth: 1, yAxisID: 'y', order: 2 },
                    { label: 'Ativos', data: ativos, type: 'line', borderColor: '#28a745', backgroundColor: '#28a745', borderWidth: 2, tension: 0.3, yAxisID: 'y1', order: 1 },
                    { label: 'Vencimentos', data: vencimentos, type: 'line', borderColor: '#dc3545', backgroundColor: '#dc3545', borderWidth: 2, borderDash: [5, 5], tension: 0.1, yAxisID: 'y1', order: 0 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', beginAtZero: true, title: { display: true, text: 'R$' } },
                    y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Qtd' } }
                }
            }
        });
    }
</script>

<?php require 'includes/footer.php'; ?>