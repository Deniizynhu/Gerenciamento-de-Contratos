<?php
// ATIVAR ERROS (pode remover depois que tudo estiver OK)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Garante caminho correto para o db.php independente da pasta
require __DIR__ . '/config/db.php';

// --- CARREGA CONFIGURAÇÕES DO SISTEMA (Logo, Nome, Tema) ---
// Protegido com try/catch para NÃO quebrar se a tabela não existir
$sysConfig = [
    'sistema_nome' => 'Dultec Soluções',
    'cor_tema'     => 'azul',
    'logo_path'    => 'assets/logo.png'
];

try {
    $stmt = $pdo->query("SELECT * FROM system_config WHERE id = 1");
    $cfg = $stmt->fetch();
    if ($cfg) {
        $sysConfig = $cfg;
    }
} catch (Exception $e) {
    // Se der erro (tabela não existe, etc.), continua com o padrão
}

// Define cor base do botão conforme o tema
$primaryColor = '#0056b3'; // azul
if ($sysConfig['cor_tema'] === 'dark')  $primaryColor = '#6f42c1'; // roxo
if ($sysConfig['cor_tema'] === 'verde') $primaryColor = '#218838'; // verde

// --- SE JÁ ESTIVER LOGADO, REDIRECIONA PARA O PAINEL ---
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// --- PROCESSAMENTO DO LOGIN ---
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND situacao = 'ativo'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_role'] = $user['nivel'];
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail, senha inválidos ou usuário inativo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($sysConfig['sistema_nome']) ?></title>

    <!-- Ícone da aba -->
    <link rel="shortcut icon" href="<?= htmlspecialchars($sysConfig['logo_path']) ?>?t=<?= time() ?>" type="image/x-icon">

    <!-- ESTILO DIRETO NA PÁGINA (independente de style.css) -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-container img {
            max-width: 180px;
            height: auto;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin: 0 0 10px 0;
        }

        p {
            color: #666;
            margin: 0 0 25px 0;
            font-size: 0.95rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
        }

        input:focus {
            border-color: <?= $primaryColor ?>;
            box-shadow: 0 0 5px <?= $primaryColor ?>33;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: <?= $primaryColor ?>;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        button:hover {
            opacity: 0.9;
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #f5c6cb;
            text-align: left;
        }

        .footer-copy {
            margin-top: 25px;
            font-size: 0.8rem;
            color: #aaa;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Logotipo Dinâmico -->
        <img src="<?= htmlspecialchars($sysConfig['logo_path']) ?>?t=<?= time() ?>" alt="Logo">

        <h2>Bem-vindo</h2>
        <p>Acesse o painel <strong><?= htmlspecialchars($sysConfig['sistema_nome']) ?></strong></p>

        <?php if ($erro): ?>
            <div class="alert"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="E-mail" required autofocus>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <div class="footer-copy">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($sysConfig['sistema_nome']) ?>
        </div>
    </div>

</body>
</html>