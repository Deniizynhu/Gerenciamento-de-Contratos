<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// --- SEGURANÇA E FILTRO ---
$id_usuario = $_SESSION['user_id'];
$nivel_usuario = $_SESSION['user_role'];

// Define a query baseada no nível de acesso
if ($nivel_usuario == 'cliente') {
    // CLIENTE: Vê apenas os SEUS contratos
    $sql = "SELECT c.*, u.nome as cliente_nome 
            FROM contratos c 
            JOIN users u ON c.cliente_id = u.id 
            WHERE c.cliente_id = :id 
            ORDER BY c.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);

} else {
    // ADMIN/GERENTE: Vê TODOS os contratos
    $sql = "SELECT c.*, u.nome as cliente_nome 
            FROM contratos c 
            JOIN users u ON c.cliente_id = u.id 
            ORDER BY c.id DESC";
    $stmt = $pdo->query($sql);
}

$contratos = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Gestão de Contratos</h2>
    
    <!-- Botão só aparece para Admin e Gerente -->
    <?php if($nivel_usuario != 'cliente'): ?>
        <a href="contratos_cadastrar.php" class="btn-novo">+ Novo Contrato</a>
    <?php endif; ?>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <!-- Só mostra coluna 'Cliente' se quem está vendo NÃO for o cliente (redundante para ele) -->
                <?php if($nivel_usuario != 'cliente') echo "<th>Cliente</th>"; ?>
                <th>Vigência</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($contratos) > 0): ?>
                <?php foreach($contratos as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['titulo']) ?></td>
                    
                    <?php if($nivel_usuario != 'cliente'): ?>
                        <td><?= htmlspecialchars($c['cliente_nome']) ?></td>
                    <?php endif; ?>

                    <td>
                        <?= date('d/m/Y', strtotime($c['data_inicio'])) ?>
                        <?php if($c['data_fim']): ?>
                            <br><small style="color:#666">Fim: <?= date('d/m/Y', strtotime($c['data_fim'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>R$ <?= number_format($c['valor'], 2, ',', '.') ?></td>
                    <td>
                        <span class="badge <?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
                    </td>
                    <td>
                        <a href="contratos_editar.php?id=<?= $c['id'] ?>" class="btn-sm">Detalhes / Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 20px; color: #666;">
                        Nenhum contrato encontrado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
    .btn-novo { background-color: #0056b3; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 500; transition: background 0.3s; }
    .btn-novo:hover { background-color: #004494; }
    .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { background: #f8f9fa; color: #333; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
    .table th, .table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
    .table tr:hover { background-color: #f1f1f1; }
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; color: white; font-weight: bold; }
    .badge.ativo { background-color: #28a745; }
    .badge.pendente { background-color: #ffc107; color: #333; }
    .badge.encerrado { background-color: #6c757d; }
    .btn-sm { padding: 5px 10px; background: #e2e6ea; color: #333; text-decoration: none; border-radius: 4px; font-size: 0.85rem; }
    .btn-sm:hover { background: #dbe0e5; }
</style>

<?php require 'includes/footer.php'; ?>