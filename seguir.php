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
    // Verificar se já está seguindo
    $stmt = $pdo->prepare('SELECT * FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
    $stmt->execute([
        'seguidor_id' => $user_id,
        'seguido_id' => $seguido_id
    ]);
    if ($stmt->rowCount() == 0) {
        // Inserir registro de seguir
        $stmt = $pdo->prepare('INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (:seguidor_id, :seguido_id)');
        $stmt->execute([
            'seguidor_id' => $user_id,
            'seguido_id' => $seguido_id
        ]);

        // Inserir notificação de que o usuário foi seguido
        $sql_notificacao = "INSERT INTO notificacoes (tipo, usuario_id, seguidor_id, visto) 
                            VALUES ('seguindo', :usuario_id, :seguidor_id, 0)";
        $stmt_notificacao = $pdo->prepare($sql_notificacao);
        $stmt_notificacao->bindParam(':usuario_id', $seguido_id); // Usuário que foi seguido
        $stmt_notificacao->bindParam(':seguidor_id', $user_id); // Quem seguiu
        $stmt_notificacao->execute();
    }
}
header('Location: profile.php?user_id=' . $seguido_id);
exit();
?>
