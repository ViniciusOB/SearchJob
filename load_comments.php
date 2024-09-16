<?php
include 'conexao.php';

$post_id = $_GET['post_id'];

$stmt = $pdo->prepare('
    SELECT comentarios.*, usuarios.nome_usuario, usuarios.profile_pic
    FROM comentarios
    JOIN usuarios ON comentarios.usuario_id = usuarios.id_usuario
    WHERE comentarios.post_id = :post_id
    ORDER BY comentarios.criado_em DESC
');
$stmt->execute(['post_id' => $post_id]);

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($comments as $comment) {
    echo '<div class="comment">';
    echo '<img src="' . htmlspecialchars($comment['profile_pic'] ?: 'default-profile.png') . '" class="comment-profile-pic" alt="Profile Picture">';
    echo '<div class="comment-content">';
    echo '<h5>' . htmlspecialchars($comment['nome_usuario']) . '</h5>';
    echo '<p>' . htmlspecialchars($comment['conteudo']) . '</p>';
    echo '<p class="comment-time">' . htmlspecialchars($comment['criado_em']) . '</p>';
    echo '</div>';
    echo '</div>';
}
?>
