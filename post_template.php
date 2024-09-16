<?php
?>
<div class="card mb-3">
    <div class="card-body">
        <div class="media">
            <?php
            $profile_pic_url = htmlspecialchars($row['profile_pic']) ?: 'default-profile.png';
            $user_profile_url = 'profile.php?user_id=' . htmlspecialchars($row['user_id']);
            ?>
            <a href="<?php echo htmlspecialchars($user_profile_url); ?>">
                <img src="<?php echo $profile_pic_url; ?>" class="mr-3 rounded-circle" alt="Profile Picture" style="width: 50px; height: 50px;">
            </a>
            <div class="media-body">
                <a href="<?php echo htmlspecialchars($user_profile_url); ?>">
                    <h5 class="mt-0"><?php echo htmlspecialchars($row['nome_usuario']); ?></h5>
                </a>
                <p><?php echo htmlspecialchars($row['content']); ?></p>
                <?php if ($row['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="img-fluid rounded mb-3" alt="Post Image">
                <?php endif; ?>
                <?php if ($row['youtube_link']): ?>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo htmlspecialchars(parse_url($row['youtube_link'], PHP_URL_QUERY)); ?>" frameborder="0" allowfullscreen></iframe>
                <?php endif; ?>
                <p class="text-muted"><small><?php echo htmlspecialchars($row['created_at']); ?></small></p>

                <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                    <form action="delete_post.php" method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

      
        <div class="comments mt-3">
            <?php
           
            $stmt_comments = $pdo->prepare('
                SELECT comentarios.*, usuarios.nome_usuario, usuarios.profile_pic
                FROM comentarios
                JOIN usuarios ON comentarios.usuario_id = usuarios.id_usuario
                WHERE comentarios.post_id = :post_id
                ORDER BY comentarios.criado_em ASC
            ');
            $stmt_comments->execute(['post_id' => $row['id']]);

            while ($comment = $stmt_comments->fetch()) {
                ?>
                <div class="comment mb-2" id="comment-<?php echo htmlspecialchars($comment['id']); ?>">
                    <div class="media">
                        <?php
                        $comment_profile_pic_url = htmlspecialchars($comment['profile_pic']) ?: 'default-profile.png';
                        ?>
                        <img src="<?php echo $comment_profile_pic_url; ?>" class="mr-3 rounded-circle" alt="Commenter Profile Picture" style="width: 40px; height: 40px;">
                        <div class="media-body">
                            <h6 class="mt-0"><?php echo htmlspecialchars($comment['nome_usuario']); ?></h6>
                            <p><?php echo htmlspecialchars($comment['conteudo']); ?></p>
                            <p class="text-muted"><small><?php echo htmlspecialchars($comment['criado_em']); ?></small></p>
                            <?php if ($comment['usuario_id'] == $_SESSION['user_id'] || $row['user_id'] == $_SESSION['user_id']): ?>
                                <button onclick="deleteComment(<?php echo htmlspecialchars($comment['id']); ?>, <?php echo htmlspecialchars($row['id']); ?>)" class="btn btn-danger btn-sm">Excluir</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

       
        <form action="add_comment.php" method="POST" class="mt-3">
            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <div class="form-group">
                <textarea name="comment" class="form-control" rows="2" placeholder="Adicionar um comentário..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Comentar</button>
        </form>
    </div>
</div>

<script>
function deleteComment(commentId, postId) {
    if (confirm('Tem certeza de que deseja excluir este comentário?')) {
        fetch('delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                comment_id: commentId,
                post_id: postId
            })
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                document.getElementById('comment-' + commentId).remove();
            } else {
                alert('Erro ao excluir o comentário.');
            }
        })
        .catch(error => console.error('Erro ao excluir o comentário:', error));
    }
}
</script>
