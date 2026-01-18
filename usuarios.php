<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

// Apenas Admin e Gerente acessam
checkRole(['admin', 'gerente']);

// Busca todos os usuários
$stmt = $pdo->query("SELECT * FROM users ORDER BY nome ASC");
$usuarios = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Gestão de Usuários</h2>
    <a href="usuarios_cadastrar.php" class="btn-novo">+ Novo Usuário</a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Login (E-mail)</th>
                <th>Nível</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $u): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($u['nome']) ?></strong><br>
                    <small style="color:#666">CPF: <?= $u['cpf_cnpj'] ?? '--' ?> | RG: <?= $u['rg'] ?? '--' ?></small>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <!-- Badge de Nível -->
                    <?php 
                        $corNivel = match($u['nivel']) {
                            'admin' => '#dc3545', // Vermelho
                            'gerente' => '#007bff', // Azul
                            default => '#28a745' // Verde (Cliente)
                        };
                    ?>
                    <span class="badge" style="background: <?= $corNivel ?>">
                        <?= ucfirst($u['nivel']) == 'Gerente' ? 'Gerente de Contrato' : ucfirst($u['nivel']) ?>
                    </span>
                </td>
                <td>
                    <?php if($u['situacao'] == 'ativo'): ?>
                        <span class="badge" style="background:green">Ativo</span>
                    <?php else: ?>
                        <span class="badge" style="background:gray">Inativo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="usuarios_editar.php?id=<?= $u['id'] ?>" class="btn-sm">Editar</a>
                    
                    <!-- Lógica: Gerente NÃO pode excluir -->
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <a href="usuarios_excluir.php?id=<?= $u['id'] ?>" class="btn-sm btn-del" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    /* Estilos (Mesmos das outras telas para padrão) */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
    .btn-novo { background-color: #0056b3; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 500; transition: background 0.3s; }
    .btn-novo:hover { background-color: #004494; }
    .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; color: white; font-weight: bold; }
    .btn-sm { padding: 5px 10px; background: #e2e6ea; color: #333; text-decoration: none; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; }
    .btn-del { background: #ffeeba; color: #856404; }
    .btn-del:hover { background: #f8d7da; color: #721c24; }
</style>

<?php require 'includes/footer.php'; ?>