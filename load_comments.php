<?php
include 'conexao.php';

$post_id = $_GET['post_id'];

$stmt = $pdo->prepare('
    SELECT comentarios.*, 
           CASE 
               WHEN comentarios.usuario_id IS NOT NULL THEN usuarios.nome_usuario
               WHEN comentarios.funcionario_id IS NOT NULL THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
           END AS nome_autor, 
           CASE 
               WHEN comentarios.usuario_id IS NOT NULL THEN usuarios.profile_pic
               WHEN comentarios.funcionario_id IS NOT NULL THEN funcionarios.profile_pic
           END AS profile_pic
    FROM comentarios
    LEFT JOIN usuarios ON comentarios.usuario_id = usuarios.id_usuario
    LEFT JOIN funcionarios ON comentarios.funcionario_id = funcionarios.id_funcionario
    WHERE comentarios.post_id = :post_id
    ORDER BY comentarios.criado_em DESC
');
$stmt->execute(['post_id' => $post_id]);

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($comments as $comment) {
    echo '<div class="comment">';
    echo '<img src="' . htmlspecialchars($comment['profile_pic'] ?: 'default-profile.png') . '" class="comment-profile-pic" alt="Profile Picture">';
    echo '<div class="comment-content">';
    echo '<h5>' . htmlspecialchars($comment['nome_autor']) . '</h5>';
    echo '<p>' . htmlspecialchars($comment['conteudo']) . '</p>';
    echo '<p class="comment-time">' . htmlspecialchars($comment['criado_em']) . '</p>';
    echo '</div>';
    echo '</div>';
}
?>
