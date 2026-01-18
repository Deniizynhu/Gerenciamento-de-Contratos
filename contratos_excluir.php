<?php
require 'config/db.php';
session_start();

// Verifica login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica Permissão (GERENTE NÃO PODE EXCLUIR)
if ($_SESSION['user_role'] != 'admin') {
    die("Acesso Negado: Apenas administradores podem excluir contratos.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: contratos.php");
    } catch (PDOException $e) {
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: contratos.php");
}
?>