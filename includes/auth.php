<?php
session_start();

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Função para verificar permissão
function checkRole($allowed_roles) {
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        die("Acesso negado. Você não tem permissão para ver esta página.");
    }
}
?>