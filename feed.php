<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ínicio</title>
    <link rel="stylesheet" href="feed.css">
</head>
<body>
<?php
session_start();

include 'conexao.php';
include 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query('SELECT posts.id, posts.user_id, usuarios.nome_usuario, usuarios.profile_pic, posts.content, posts.image_path, posts.youtube_link, posts.created_at 
                     FROM posts 
                     JOIN usuarios ON posts.user_id = usuarios.id_usuario 
                     ORDER BY posts.created_at DESC');

while ($row = $stmt->fetch()) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<div class='media'>";
    if ($row['profile_pic']) {
        echo "<img src='" . htmlspecialchars($row['profile_pic']) . "' class='mr-3 rounded-circle' alt='Profile Picture' style='width: 50px; height: 50px;'>";
    } else {
        echo "<img src='default-profile.png' class='mr-3 rounded-circle' alt='Default Profile Picture' style='width: 50px; height: 50px;'>";
    }
    echo "<div class='media-body'>";
    echo "<h5 class='mt-0'>" . htmlspecialchars($row['nome_usuario']) . "</h5>";
    echo "<p>" . htmlspecialchars($row['content']) . "</p>";
    if ($row['image_path']) {
        echo "<img src='" . htmlspecialchars($row['image_path']) . "' class='img-fluid rounded mb-3' alt='Post Image'>";
    }
    if ($row['youtube_link']) {
        preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $row['youtube_link'], $matches);
        if (isset($matches[1])) {
            $youtube_id = $matches[1];
            echo "<div class='embed-responsive embed-responsive-16by9 mb-3'>";
            echo "<iframe class='embed-responsive-item' src='https://www.youtube.com/embed/" . htmlspecialchars($youtube_id) . "' allowfullscreen></iframe>";
            echo "</div>";
        }
    }
    echo "<p class='text-muted'><small>" . htmlspecialchars($row['created_at']) . "</small></p>";

    if ($row['user_id'] == $_SESSION['user_id']) {
        echo " <form action='edit_post.php' method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='post_id' value='" . htmlspecialchars($row['id']) . "'>";
        echo "<button type='submit' class='btn btn-primary btn-sm'>Editar</button>";
        echo "</form> ";
        echo "<form action='delete_post.php' method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='post_id' value='" . htmlspecialchars($row['id']) . "'>";
        echo "<button type='submit' class='btn btn-danger btn-sm'>Excluir</button>";
        echo "</form>";
    }
    echo "</div>"; 
    echo "</div>"; 
    echo "</div>"; 
    echo "</div>"; 
}
include 'views/footer.php';
?>
</body>
</html>
