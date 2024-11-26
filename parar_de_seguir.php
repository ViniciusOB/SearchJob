<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$seguidor_id = $_SESSION['user_id'];
$seguido_id = isset($_POST['seguido_id']) ? intval($_POST['seguido_id']) : null;

if ($seguido_id) {
    $stmt = $pdo->prepare('DELETE FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
    $stmt->execute(['seguidor_id' => $seguidor_id, 'seguido_id' => $seguido_id]);

    // Remover notificação de seguir
    $stmt_notificacao = $pdo->prepare('DELETE FROM notificacoes 
        WHERE tipo = "seguindo" AND usuario_id = :usuario_id AND seguidor_id = :seguidor_id');
    $stmt_notificacao->execute([
        'usuario_id' => $seguido_id,
        'seguidor_id' => $seguidor_id
    ]);
}

header('Location: profile.php?user_id=' . $seguido_id);
exit();
?>
