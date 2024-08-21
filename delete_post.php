<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    
    $stmt = $pdo->prepare('SELECT user_id, image_path FROM posts WHERE id = :post_id');
    $stmt->execute(['post_id' => $post_id]);
    $post = $stmt->fetch();

    if ($post && $post['user_id'] == $user_id) {
        
        if ($post['image_path'] && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }

    
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :post_id');
        $stmt->execute(['post_id' => $post_id]);

        header('Location: feed.php');
        exit();
    } else {
        echo 'Ação não permitida.';
        exit();
    }
} else {
    header('Location: feed.php');
    exit();
}
?>
