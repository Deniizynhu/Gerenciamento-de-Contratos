<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// Apenas Admin e Gerente podem acessar essa página
checkRole(['admin', 'gerente']);

// Busca apenas usuários que são 'clientes'
$stmt = $pdo->query("SELECT * FROM users WHERE nivel = 'cliente' ORDER BY nome ASC");
$clientes = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Gestão de Clientes</h2>
    <a href="clientes_cadastrar.php" class="btn-novo">+ Novo Cliente</a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome / Razão Social</th>
                <th>CPF/CNPJ</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Cidade/UF</th>
                <th width="100">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($clientes) > 0): ?>
                <?php foreach($clientes as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($c['nome']) ?></strong><br>
                        <small style="color:#666;"><?= htmlspecialchars($c['contato'] ?? '') ?></small>
                    </td>
                    <td><?= htmlspecialchars($c['cpf_cnpj'] ?? '--') ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['telefone'] ?? '--') ?></td>
                    <td><?= htmlspecialchars($c['cidade'] ?? '') ?> - <?= htmlspecialchars($c['estado'] ?? '') ?></td>
                    <td>
                        <a href="clientes_editar.php?id=<?= $c['id'] ?>" class="btn-sm">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 20px;">Nenhum cliente cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    /* Estilos exclusivos desta página */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }

    /* Botão de Novo Cliente */
    .btn-novo {
        background-color: #0056b3;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 500;
        transition: background 0.3s;
    }
    .btn-novo:hover {
        background-color: #004494;
    }

    /* Ajustes na tabela */
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        overflow: hidden; /* Arredonda cantos da tabela */
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table th {
        background: #f8f9fa;
        color: #333;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    .table th, .table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    .table tr:hover {
        background-color: #f1f1f1;
    }

    /* Botão Pequeno de Ação */
    .btn-sm {
        padding: 5px 10px;
        background: #e2e6ea;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    .btn-sm:hover {
        background: #dbe0e5;
    }
</style>

<?php require 'includes/footer.php'; ?>