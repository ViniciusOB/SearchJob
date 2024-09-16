<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); 
    exit();
}

$comment_id = $_POST['comment_id'];
$post_id = $_POST['post_id'];

// Verifica se o comentário pertence ao usuário ou se o usuário é o dono do post
$stmt = $pdo->prepare('
    SELECT c.usuario_id, p.user_id
    FROM comentarios c
    JOIN posts p ON c.post_id = p.id
    WHERE c.id = :comment_id
');
$stmt->execute(['comment_id' => $comment_id]);
$comment = $stmt->fetch();

if ($comment && ($comment['usuario_id'] == $_SESSION['user_id'] || $comment['user_id'] == $_SESSION['user_id'])) {
    // Permite a exclusão
    $stmt = $pdo->prepare('DELETE FROM comentarios WHERE id = :comment_id');
    $stmt->execute(['comment_id' => $comment_id]);
    echo 'Comentário excluído com sucesso.';
} else {
    http_response_code(403); // Não autorizado
}
?>
