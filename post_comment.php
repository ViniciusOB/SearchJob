<?php
session_start();
include 'conexao.php';

// Verifica se o usuário ou funcionário está logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment'];
    $usuario_id = NULL;
    $funcionario_id = NULL;

    // Determina o tipo de autor do comentário
    if (isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['id_funcionario'])) {
        $funcionario_id = $_SESSION['id_funcionario'];
    }

    // Insere o comentário na tabela com base no tipo de autor
    $stmt = $pdo->prepare('
        INSERT INTO comentarios (post_id, usuario_id, funcionario_id, conteudo) 
        VALUES (:post_id, :usuario_id, :funcionario_id, :conteudo)
    ');
    $stmt->execute([
        'post_id' => $post_id,
        'usuario_id' => $usuario_id,
        'funcionario_id' => $funcionario_id,
        'conteudo' => $comment_content
    ]);

    echo 'Comentário adicionado com sucesso!';
}
?>
