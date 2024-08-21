<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $image_path = NULL;
    $youtube_link = NULL;
    $author_type = NULL;
    $user_id = NULL;
    $ID_empresas = NULL;

   
    if (isset($_SESSION['user_id'])) {
        $author_type = 'cliente';
        $user_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['ID_empresas'])) {
        $author_type = 'empresa';
        $ID_empresas = $_SESSION['ID_empresas'];
    }

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_path = $upload_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    
    if (isset($_POST['youtube_link']) && !empty($_POST['youtube_link'])) {
        $youtube_link = $_POST['youtube_link'];
    }

   
    $stmt = $pdo->prepare('INSERT INTO posts (author_type, user_id, ID_empresas, content, image_path, youtube_link) 
                           VALUES (:author_type, :user_id, :ID_empresas, :content, :image_path, :youtube_link)');
    $stmt->execute([
        'author_type' => $author_type,
        'user_id' => $user_id, 
        'ID_empresas' => $ID_empresas, 
        'content' => $status, 
        'image_path' => $image_path, 
        'youtube_link' => $youtube_link
    ]);


    header('Location: feed.php');
    exit();
}
?>
