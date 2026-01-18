<?php
require 'config/db.php';
session_start();

// 1. Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. SEGURANÇA CRÍTICA: Apenas ADMIN pode excluir
// Se for Gerente ou Cliente, encerra a execução.
if ($_SESSION['user_role'] != 'admin') {
    die("Acesso Negado: Você não tem permissão para excluir usuários.");
}

// 3. Verifica se passou o ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Evita que o Admin exclua a si mesmo
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Você não pode excluir seu próprio usuário!'); window.location='usuarios.php';</script>";
        exit;
    }

    try {
        // Primeiro deleta contratos vinculados (se for um cliente) para não dar erro de chave estrangeira
        // Se quiser manter os contratos, teria que mudar a lógica, mas aqui vamos limpar tudo.
        $pdo->prepare("DELETE FROM contratos WHERE cliente_id = ?")->execute([$id]);

        // Deleta o usuário
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: usuarios.php");
    } catch (PDOException $e) {
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: usuarios.php");
}
?>