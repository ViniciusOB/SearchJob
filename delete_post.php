<?php
session_start();
include 'conexao.php';

// Verifica se há um usuário ou funcionário logado e se o post_id foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_SESSION['user_id']) || isset($_SESSION['id_funcionario'])) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = null;
    $funcionario_id = null;

    // Determina se o autor é um usuário ou um funcionário
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT user_id, image_path FROM posts WHERE id = :post_id');
    } elseif (isset($_SESSION['id_funcionario'])) {
        $funcionario_id = $_SESSION['id_funcionario'];
        $stmt = $pdo->prepare('SELECT funcionario_id, image_path FROM posts WHERE id = :post_id');
    }

    // Executa a consulta para verificar o post
    $stmt->execute(['post_id' => $post_id]);
    $post = $stmt->fetch();

    // Verifica se o post pertence ao usuário ou funcionário logado
    if (($post && $post['user_id'] == $user_id) || ($post && $post['funcionario_id'] == $funcionario_id)) {
        // Exclui a imagem associada ao post, se existir
        if ($post['image_path'] && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }

        // Exclui o post
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :post_id');
        $stmt->execute(['post_id' => $post_id]);

        // Redireciona para o feed correspondente
        if ($user_id) {
            header('Location: feed.php');
        } else {
            header('Location: feed.php');
        }
        exit();
    } else {
        echo 'Ação não permitida.';
        exit();
    }
} else {
    // Redireciona para o feed correspondente caso o acesso não seja permitido
    if (isset($_SESSION['user_id'])) {
        header('Location: feed.php');
    } else {
        header('Location: feed.php');
    }
    exit();
}
?>
