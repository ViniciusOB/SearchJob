<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$seguidor_id = $_SESSION['user_id'];
$seguido_id = isset($_POST['seguido_id']) ? intval($_POST['seguido_id']) : null;

if ($seguido_id && $seguidor_id != $seguido_id) {
    $stmt = $pdo->prepare('SELECT * FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
    $stmt->execute(['seguidor_id' => $seguidor_id, 'seguido_id' => $seguido_id]);

    if ($stmt->rowCount() == 0) {
        // Inserir novo seguidor
        $stmt = $pdo->prepare('INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (:seguidor_id, :seguido_id)');
        $stmt->execute(['seguidor_id' => $seguidor_id, 'seguido_id' => $seguido_id]);
        
        // Inserir notificação de seguidor
        $sql_notificacao = "INSERT INTO notificacoes (tipo, usuario_id, seguidor_id, visto) 
                            VALUES ('seguindo', :usuario_id, :seguidor_id, 0)";
        $stmt_notificacao = $pdo->prepare($sql_notificacao);
        $stmt_notificacao->execute([
            'usuario_id' => $seguido_id,
            'seguidor_id' => $seguidor_id
        ]);
    }
}

header('Location: profile.php?user_id=' . $seguido_id);
exit();
?>
