<?php
session_start();
include 'conexao.php';

// Verificar se há um usuário ou funcionário logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];
    $usuario_id = $_SESSION['user_id'] ?? null;
    $funcionario_id = $_SESSION['id_funcionario'] ?? null;

    // Inserir comentário no banco de dados
    $stmt = $pdo->prepare('
        INSERT INTO comentarios (post_id, usuario_id, funcionario_id, conteudo)
        VALUES (:post_id, :usuario_id, :funcionario_id, :conteudo)
    ');
    $stmt->execute([
        'post_id' => $post_id,
        'usuario_id' => $usuario_id,
        'funcionario_id' => $funcionario_id,
        'conteudo' => $comment
    ]);

    // Redirecionar para o feed apropriado
    if ($usuario_id) {
        header('Location: feed.php');
    } elseif ($funcionario_id) {
        header('Location: feed.php');
    }
    exit();
}
?>
