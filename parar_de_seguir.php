<?php
session_start();
include 'conexao.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$seguido_id = isset($_POST['seguido_id']) ? intval($_POST['seguido_id']) : null;

if ($seguido_id && $seguido_id != $user_id) {
    // Deletar registro de seguir
    $stmt = $pdo->prepare('DELETE FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
    $stmt->execute([
        'seguidor_id' => $user_id,
        'seguido_id' => $seguido_id
    ]);
}
header('Location: profile.php?user_id=' . $seguido_id);
exit();
