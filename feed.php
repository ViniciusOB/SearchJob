<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link rel="stylesheet" href="CSS/feed.css">
</head>
<body>
<?php
session_start();
include 'conexao.php';
include 'views/header.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare('SELECT nome_usuario, profile_pic, descricao FROM usuarios WHERE id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="tabs">
    <div class="tab active" onclick="showContent('feed')">Para você</div>
    <div class="tab" onclick="showContent('seguindo')">Seguindo</div>
</div>

<div class="post-form">
    <form id="postForm" action="post_status.php" method="POST" enctype="multipart/form-data">
        <div class="d-flex align-items-start">
            <img src="<?php echo htmlspecialchars($user['profile_pic'] ?: 'default-profile.png'); ?>" alt="Foto de perfil" style="width: 40px; height: 40px;" onclick="viewProfile(<?php echo $_SESSION['user_id']; ?>)">
            <textarea name="status" placeholder="O que está acontecendo?" rows="3" class="form-control ml-3"></textarea>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="post-icons">
                <label for="image-upload" class="icon">&#128247;</label>
                <input type="file" id="image-upload" name="image" accept="image/*" style="display:none;">
            </div>
            <button type="submit" class="btn btn-primary">Postar</button>
        </div>
    </form>
</div>

<div id="feed" class="content active">
    <?php
    // Seleciona posts do feed geral, excluindo os seguidos
    $stmt = $pdo->prepare('
        SELECT posts.*, usuarios.nome_usuario, usuarios.profile_pic 
        FROM posts 
        JOIN usuarios ON posts.user_id = usuarios.id_usuario 
        LEFT JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id AND seguidores.seguidor_id = :user_id
        WHERE seguidores.seguido_id IS NULL
        ORDER BY posts.created_at DESC
    ');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    while ($row = $stmt->fetch()) {
        include 'post_template.php';
    }

    // Seleciona projetos para exibição no feed
    $stmt = $pdo->query('
        SELECT projetos.id_projeto, projetos.empresa_id, empresas.ID_empresas, empresas.nome_empresa, empresas.profile_pic, projetos.nome_projeto, projetos.descricao, projetos.imagem_capa, projetos.nivel_especialidade, projetos.data_criacao
        FROM projetos 
        JOIN empresas ON projetos.empresa_id = empresas.ID_empresas 
        ORDER BY projetos.data_criacao DESC
    ');

    while ($row = $stmt->fetch()) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-body'>";
        echo "<div class='media'>";
        $profile_pic_url = htmlspecialchars($row['profile_pic']) ?: 'default-profile.png';
        echo "<img src='profile_pics/" . $profile_pic_url . "' class='mr-3 rounded-circle' alt='Profile Picture' style='width: 50px; height: 50px;' onclick=\"viewProfile(" . htmlspecialchars($row['empresa_id']) . ")\">";
        echo "<div class='media-body'>";
        echo "<h5 class='mt-0'>" . htmlspecialchars($row['nome_empresa']) . "</h5>";
        echo "<h4 class='mt-2'>" . htmlspecialchars($row['nome_projeto']) . "</h4>";
        if ($row['imagem_capa']) {
            echo "<img src='capa_projeto/" . htmlspecialchars($row['imagem_capa']) . "' class='img-fluid rounded mb-3' alt='Capa do Projeto'>";
        }
        echo "<p class='nivel_especialidade'> Nível de especialidade: " . htmlspecialchars($row['nivel_especialidade']) . "</p>";
        echo "<p>" . htmlspecialchars($row['descricao']) . "</p>";
        echo "<p class='text-muted'><small>" . htmlspecialchars($row['data_criacao']) . "</small></p>";
        echo "</div>"; // Fecha media-body
        echo "</div>"; // Fecha media
        echo "</div>"; // Fecha card-body
        echo "</div>"; // Fecha card
    }
    ?>
</div>

<div id="seguindo" class="content">
    <?php
    // Seleciona posts dos usuários seguidos
    $stmt = $pdo->prepare('
        SELECT posts.*, usuarios.nome_usuario, usuarios.profile_pic 
        FROM posts 
        JOIN usuarios ON posts.user_id = usuarios.id_usuario 
        JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id 
        WHERE seguidores.seguidor_id = :user_id
        ORDER BY posts.created_at DESC
    ');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    while ($row = $stmt->fetch()) {
        include 'post_template.php';
    }
    ?>
</div>

<script>
    function showContent(id) {
        document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        document.querySelector(`.tab[onclick="showContent('${id}')"]`).classList.add('active');
    }

    function viewProfile(userId) {
        window.location.href = `profile.php?id=${userId}`;
    }

    function openChat(contactId) {
        document.getElementById('chat-window').style.display = 'block';
        document.getElementById('destinatario_id').value = contactId;
        document.getElementById('chat-contact-name').textContent = contactId;
        document.getElementById('chat-profile-pic').src = document.querySelector(`.contact[data-id="${contactId}"] .contact-pic`).src;
    }

    document.addEventListener('DOMContentLoaded', () => {
        function postComment(postId) {
            const form = document.querySelector(`#post-${postId} .comment-form`);
            const comment = form.querySelector('textarea').value;

            fetch('post_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    post_id: postId,
                    comment: comment
                })
            })
            .then(response => response.text())
            .then(() => {
                loadComments(postId);
                form.reset();
            })
            .catch(error => console.error('Erro:', error));

            return false; // Previne o envio do formulário padrão
        }

        function loadComments(postId) {
            fetch(`load_comments.php?post_id=${postId}`)
            .then(response => response.text())
            .then(data => {
                document.querySelector(`#comments-${postId}`).innerHTML = data;
            })
            .catch(error => console.error('Erro:', error));
        }

        function toggleComments(postId) {
            const commentsSection = document.querySelector(`#comments-${postId}`);
            if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
                loadComments(postId);
                commentsSection.style.display = 'block';
            } else {
                commentsSection.style.display = 'none';
            }
        }

        // Adiciona eventos de envio para todos os formulários de comentário na página
        document.querySelectorAll('.comment-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Previne o envio do formulário padrão
                const postId = this.dataset.postId; // Obtém o ID do post do atributo data-post-id
                postComment(postId);
            });
        });

        // Adiciona eventos de clique para os botões de mostrar/esconder comentários
        document.querySelectorAll('.toggle-comments').forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.dataset.postId; // Obtém o ID do post do atributo data-post-id
                toggleComments(postId);
            });
        });

        // Trata o envio do formulário principal
        document.getElementById('postForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Previne o envio do formulário padrão
    const formData = new FormData(this);

    fetch('post_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Atualiza o feed com a nova postagem
        const feed = document.getElementById('feed');
        feed.insertAdjacentHTML('afterbegin', data); // Adiciona o novo post no início do feed
        this.reset(); // Limpa o formulário

        // Atualiza o perfil do usuário dinamicamente
        const profilePic = document.querySelector('.post-form img'); // Seleciona a imagem de perfil no form
        fetch('get_profile_info.php') // Supondo que "post_status.php" já lida com perfis
        .then(response => response.json()) // Recebe o perfil como JSON
        .then(profileData => {
            // Atualiza a imagem de perfil se necessário
            if (profileData.profile_pic) {
                profilePic.src = profileData.profile_pic;
            }
        })
        .catch(error => console.error('Erro ao atualizar perfil:', error));

        // Atualiza a seção de mensagens/notificações se houver algo novo
        const messagesSection = document.getElementById('messages-section');
        fetch('get_latest_messages.php') // Supostamente, você já tem algo similar em "post_status.php"
        .then(response => response.text())
        .then(messagesData => {
            messagesSection.innerHTML = messagesData; // Atualiza a área de mensagens
        })
        .catch(error => console.error('Erro ao atualizar mensagens:', error));
    })
    .catch(error => console.error('Erro ao enviar o post:', error));
});
    return false; // Previne o envio do formulário padrão
});

    function deleteComment(commentId, postId) {
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na exclusão do comentário.');
        }
        return response.text();
    })
    .then(data => {
        console.log(data);
        document.getElementById(`comment-${commentId}`).remove();
    })
    .catch(error => console.error('Erro:', error));
}


</script>
</body>
</html>
