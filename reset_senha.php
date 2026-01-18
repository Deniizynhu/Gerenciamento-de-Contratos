<?php
require 'config/db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $nova_senha = trim($_POST['senha']);

    if (!empty($email) && !empty($nova_senha)) {
        // 1. Gera o Hash seguro da senha
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        try {
            // 2. Tenta atualizar no banco
            $stmt = $pdo->prepare("UPDATE users SET senha = :senha WHERE email = :email");
            $stmt->execute([
                ':senha' => $hash,
                ':email' => $email
            ]);

            // 3. Verifica se alguma linha foi alterada
            if ($stmt->rowCount() > 0) {
                $msg = "<div class='alert success'>
                            ✅ Sucesso!<br>
                            A senha de <strong>$email</strong> foi alterada para: <strong>$nova_senha</strong><br>
                            <a href='login.php'>Clique aqui para Logar</a>
                        </div>";
            } else {
                $msg = "<div class='alert error'>
                            ❌ Erro: E-mail não encontrado no banco de dados.<br>
                            Verifique se digitou corretamente.
                        </div>";
            }

        } catch (PDOException $e) {
            $msg = "<div class='alert error'>Erro de Banco de Dados: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='alert error'>Preencha todos os campos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Reset de Senha - Dultec</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        h2 { color: #333; margin-top: 0; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #c82333; }
        .alert { padding: 15px; margin-bottom: 20px; text-align: left; border-radius: 4px; font-size: 0.9rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        a { color: #0056b3; text-decoration: none; font-weight: bold; }
        .warning { color: red; font-size: 0.8rem; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Resetar Senha</h2>
        <p>Ferramenta Administrativa</p>

        <?= $msg ?>

        <form method="POST">
            <label style="float:left; font-size:0.9rem; font-weight:bold;">E-mail do Usuário:</label>
            <input type="email" name="email" placeholder="ex: admin@dultec.com.br" required>

            <label style="float:left; font-size:0.9rem; font-weight:bold;">Nova Senha:</label>
            <input type="text" name="senha" placeholder="ex: 123456" required>

            <button type="submit">Alterar Senha</button>
        </form>

        <p class="warning">⚠️ Apague este arquivo após o uso!</p>
    </div>

</body>
</html>