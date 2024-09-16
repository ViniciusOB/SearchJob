<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];
    $youtube_link = $_POST['youtube_link'];
    $image_path = '';

    // Verifique se o post pertence ao usuário logado
    $stmt = $pdo->prepare('SELECT user_id, image_path, youtube_link FROM posts WHERE id = :post_id');
    $stmt->execute(['post_id' => $post_id]);
    $post = $stmt->fetch();

    if ($post && $post['user_id'] == $user_id) {
        // Verifique se uma nova imagem foi enviada
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Caminho para a nova imagem
            $image_path = 'uploads/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

            // Exclua a imagem antiga, se houver
            if ($post['image_path'] && file_exists($post['image_path'])) {
                unlink($post['image_path']);
            }
        } else {
            // Mantém o caminho da imagem antiga se nenhuma nova imagem for enviada
            $image_path = $post['image_path'];
        }

        // Verifique se o usuário deseja excluir a imagem atual
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if ($post['image_path'] && file_exists($post['image_path'])) {
                unlink($post['image_path']);
            }
            $image_path = '';
        }

        // Verifique se o usuário deseja excluir o link do YouTube atual
        if (isset($_POST['delete_youtube_link']) && $_POST['delete_youtube_link'] == '1') {
            $youtube_link = '';
        }

        // Atualize o post no banco de dados
        $stmt = $pdo->prepare('UPDATE posts SET content = :content, image_path = :image_path, youtube_link = :youtube_link WHERE id = :post_id');
        $stmt->execute([
            'content' => $content,
            'image_path' => $image_path,
            'youtube_link' => $youtube_link,
            'post_id' => $post_id
        ]);

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
