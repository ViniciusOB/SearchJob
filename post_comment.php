<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment'];

    $stmt = $pdo->prepare('INSERT INTO comentarios (post_id, usuario_id, conteudo) VALUES (:post_id, :usuario_id, :conteudo)');
    $stmt->execute([
        'post_id' => $post_id,
        'usuario_id' => $_SESSION['user_id'],
        'conteudo' => $comment_content
    ]);

    echo 'Comentário adicionado com sucesso!';
}
?>
