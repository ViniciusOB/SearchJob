<div class="card mb-3">
    <div class="card-body">
        <div class="media">
            <?php
            // Verifica o tipo de autor e define as variáveis adequadas
            $profile_pic_url = 'default-profile.png';
            $author_name = '';
            $author_type = $row['author_type'];
            $user_profile_url = '';

            if ($author_type === 'cliente') {
                $profile_pic_url = htmlspecialchars($row['profile_pic']) ?: 'default-profile.png';
                $user_profile_url = 'profile.php?user_id=' . htmlspecialchars($row['user_id']);
                $author_name = htmlspecialchars($row['nome_autor']);
            } elseif ($author_type === 'funcionario') {
                $profile_pic_url = htmlspecialchars($row['profile_pic']) ?: 'default-profile.png';
                $author_name = htmlspecialchars($row['nome_autor']);
            }
            ?>

            <!-- Exibe imagem com link apenas para clientes -->
            <?php if ($author_type === 'cliente'): ?>
                <a href="javascript:viewProfile(<?php echo htmlspecialchars($row['user_id']); ?>, '<?php echo $author_type; ?>');">
                    <img src="<?php echo $profile_pic_url; ?>" class="mr-3 rounded-circle" alt="Profile Picture" style="width: 50px; height: 50px;">
                </a>
            <?php else: ?>
                <img src="<?php echo $profile_pic_url; ?>" class="mr-3 rounded-circle" alt="Profile Picture" style="width: 50px; height: 50px;">
            <?php endif; ?>

            <div class="media-body">
                <!-- Exibe o nome do autor com link apenas para clientes -->
                <?php if ($author_type === 'cliente'): ?>
                    <a href="javascript:viewProfile(<?php echo htmlspecialchars($row['user_id']); ?>, '<?php echo $author_type; ?>');">
                        <h5 class="mt-0"><?php echo $author_name; ?></h5>
                    </a>
                <?php else: ?>
                    <h5 class="mt-0"><?php echo $author_name; ?></h5>
                <?php endif; ?>

                <p><?php echo htmlspecialchars($row['content']); ?></p>
                
                <?php if ($row['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="img-fluid rounded mb-3" alt="Post Image">
                <?php endif; ?>
                
                <?php if ($row['youtube_link']): ?>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo htmlspecialchars(parse_url($row['youtube_link'], PHP_URL_QUERY)); ?>" frameborder="0" allowfullscreen></iframe>
                <?php endif; ?>

                <p class="text-muted"><small><?php echo htmlspecialchars($row['created_at']); ?></small></p>

                <?php 
                // Exibe o botão de exclusão apenas para o autor do post
                if (
                    (isset($_SESSION['user_id']) && $row['author_type'] === 'cliente' && $row['user_id'] == $_SESSION['user_id']) || 
                    (isset($_SESSION['id_funcionario']) && $row['author_type'] === 'funcionario' && $row['funcionario_id'] == $_SESSION['id_funcionario'])
                ): ?>
                    <form action="delete_post.php" method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seção de comentários -->
        <div class="comments mt-3">
            <?php
            $stmt_comments = $pdo->prepare('
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
                            <h6 class="mt-0"><?php echo htmlspecialchars($comment['nome_autor']); ?></h6>
                            <p><?php echo htmlspecialchars($comment['conteudo']); ?></p>
                            <p class="text-muted"><small><?php echo htmlspecialchars($comment['criado_em']); ?></small></p>

                            <?php 
                            // Permite excluir o comentário se for do autor do comentário ou do post
                            if (
                                (isset($_SESSION['user_id']) && $comment['usuario_id'] == $_SESSION['user_id']) || 
                                (isset($_SESSION['id_funcionario']) && $comment['funcionario_id'] == $_SESSION['id_funcionario']) || 
                                (isset($_SESSION['user_id']) && $row['author_type'] === 'cliente' && $row['user_id'] == $_SESSION['user_id']) || 
                                (isset($_SESSION['id_funcionario']) && $row['author_type'] === 'funcionario' && $row['funcionario_id'] == $_SESSION['id_funcionario'])
                            ): ?>
                                <button onclick="deleteComment(<?php echo htmlspecialchars($comment['id']); ?>, <?php echo htmlspecialchars($row['id']); ?>)" class="btn btn-danger btn-sm">Excluir</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Formulário para adicionar comentários -->
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
function viewProfile(userId, userType) {
    // Determina o tipo de perfil que deve ser acessado com base no tipo de usuário
    if (userType === 'cliente') {
        window.location.href = `profile.php?user_id=${userId}`;
    } else if (userType === 'funcionario') {
        // Coloque um redirecionamento aqui, se necessário, ou deixe sem ação
    }
}

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
