<?php
session_start();
include 'conexao.php';

// Verifica se há um usuário ou funcionário logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    http_response_code(403); 
    exit();
}

$comment_id = $_POST['comment_id'];
$post_id = $_POST['post_id'];

// Consulta para verificar se o comentário pertence ao autor ou se o autor é o dono do post
$stmt = $pdo->prepare('
    SELECT c.usuario_id, c.funcionario_id, p.user_id, p.funcionario_id AS post_funcionario_id
    FROM comentarios c
    JOIN posts p ON c.post_id = p.id
    WHERE c.id = :comment_id
');
$stmt->execute(['comment_id' => $comment_id]);
$comment = $stmt->fetch();

// Verifica se o comentário pertence ao usuário logado ou se o usuário é o dono do post
if ($comment) {
    if ((isset($_SESSION['user_id']) && ($comment['usuario_id'] == $_SESSION['user_id'] || $comment['user_id'] == $_SESSION['user_id'])) || 
        (isset($_SESSION['id_funcionario']) && ($comment['funcionario_id'] == $_SESSION['id_funcionario'] || $comment['post_funcionario_id'] == $_SESSION['id_funcionario']))) {
        
        // Permite a exclusão do comentário
        $stmt = $pdo->prepare('DELETE FROM comentarios WHERE id = :comment_id');
        $stmt->execute(['comment_id' => $comment_id]);
        echo 'Comentário excluído com sucesso.';
    } else {
        http_response_code(403); // Não autorizado
    }
} else {
    http_response_code(403); // Não autorizado
}
?>
