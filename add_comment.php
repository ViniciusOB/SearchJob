<?php
session_start();
include 'conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];

    // Inserir comentário no banco de dados
    $stmt = $pdo->prepare('
        INSERT INTO comentarios (post_id, usuario_id, conteudo)
        VALUES (:post_id, :usuario_id, :conteudo)
    ');
    $stmt->execute([
        'post_id' => $post_id,
        'usuario_id' => $_SESSION['user_id'],
        'conteudo' => $comment
    ]);

    // Redirecionar de volta para o feed
    header('Location: feed.php');
    exit();
}
?>
