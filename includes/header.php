<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Carrega Configuração do Banco
if (!isset($pdo)) require 'config/db.php';

// Busca config (se não existir, usa padrão para não quebrar)
$stmtConfig = $pdo->query("SELECT * FROM system_config WHERE id = 1");
$sysConfig = $stmtConfig->fetch();

if (!$sysConfig) {
    $sysConfig = ['sistema_nome'=>'Dultec Soluções', 'cor_tema'=>'azul', 'logo_path'=>'assets/logo.png'];
}

// --- DEFINIÇÃO DOS TEMAS ---
$temas = [
    'azul'  => ['primary' => '#0056b3', 'primary_dark' => '#004494', 'bg' => '#f4f6f9', 'sidebar_bg' => '#ffffff', 'text' => '#333333', 'menu_text' => '#555'],
    'dark'  => ['primary' => '#6f42c1', 'primary_dark' => '#5a32a3', 'bg' => '#1e1e2f', 'sidebar_bg' => '#27293d', 'text' => '#e0e0e0', 'menu_text' => '#cfcfcf'],
    'verde' => ['primary' => '#218838', 'primary_dark' => '#1e7e34', 'bg' => '#eafbea', 'sidebar_bg' => '#ffffff', 'text' => '#2c3e50', 'menu_text' => '#555'],
];

// Seleciona as cores do tema atual
$cores = $temas[$sysConfig['cor_tema']] ?? $temas['azul'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sysConfig['sistema_nome']) ?></title>
	<link rel="shortcut icon" href="<?= $sysConfig['logo_path'] ?>?t=<?= time() ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- APLICANDO O TEMA DINÂMICO -->
    <style>
        :root {
            --primary: <?= $cores['primary'] ?>;
            --primary-dark: <?= $cores['primary_dark'] ?>;
            --bg: <?= $cores['bg'] ?>;
            --text-color: <?= $cores['text'] ?>;
            --sidebar-bg: <?= $cores['sidebar_bg'] ?>;
            --menu-text: <?= $cores['menu_text'] ?>;
            --sidebar-width: 260px;
        }

        /* Força a aplicação das cores no menu e corpo */
        body { background: var(--bg); color: var(--text-color); }
        .sidebar { background: var(--sidebar-bg); color: var(--menu-text); border-right-color: rgba(0,0,0,0.1); }
        .sidebar-header { background: var(--sidebar-bg); border-bottom-color: rgba(0,0,0,0.05); }
        .sidebar-menu a { color: var(--menu-text); }
        .user-info { color: var(--menu-text); opacity: 0.8; }
        
        /* Ajustes específicos para Dark Mode */
        <?php if($sysConfig['cor_tema'] == 'dark'): ?>
            .card, .table-container, .page-header, .chart-container-box { 
                background: #27293d !important; 
                color: #fff !important; 
                border: 1px solid #333 !important;
            }
            .table th { background: #1e1e2f !important; color: #fff !important; border-bottom: 1px solid #444; }
            .table td { border-bottom: 1px solid #444; }
            input, select, textarea { background: #1e1e2f; color: #fff; border: 1px solid #444; }
            h1, h2, h3 { color: #fff !important; }
        <?php endif; ?>
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <!-- LOGO DINÂMICO -->
            <img src="<?= $sysConfig['logo_path'] ?>?t=<?= time() ?>" alt="Logo" class="logo-img">
            
            <div class="user-info">
                Bem-vindo,
                <strong style="color: var(--primary)"><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                <span class="user-role"><?= ucfirst($_SESSION['user_role']) ?></span>
            </div>
        </div>

        <div class="sidebar-menu">
            <a href="index.php">📊 Dashboard</a>
            <?php if($_SESSION['user_role'] != 'cliente'): ?>
                <a href="clientes.php">👥 Clientes</a>
            <?php endif; ?>
            <a href="contratos.php">📄 Contratos</a>
            <?php if($_SESSION['user_role'] != 'cliente'): ?>
                <a href="usuarios.php">🪪 Usuários</a>
            <?php endif; ?>
            
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <a href="configuracoes.php">⚙️ Configurações</a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-menu">Sair do Sistema</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">