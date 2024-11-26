<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <link rel="stylesheet" href="CSS/feed.css">
</head>
<body>
<?php
session_start();
include 'conexao.php';
include 'views/header.php';

// Verifica se o usuário ou funcionário está logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

// Verifica o tipo de usuário logado e ajusta as variáveis de sessão e banco de dados
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT nome_usuario AS nome, profile_pic, descricao FROM usuarios WHERE id_usuario = :id_usuario');
    $stmt->execute(['id_usuario' => $userId]);
} else {
    $userType = 'funcionario';
    $userId = $_SESSION['id_funcionario'];
    $stmt = $pdo->prepare('SELECT CONCAT(nome_funcionario, " ", sobrenome_funcionario) AS nome, profile_pic FROM funcionarios WHERE id_funcionario = :id_funcionario');
    $stmt->execute(['id_funcionario' => $userId]);
}

$user = $stmt->fetch();
?>

<div class="tabs">
    <div class="tab active" onclick="showContent('feed')">Para você</div>
    <div class="tab" onclick="showContent('inscrito')">Inscrito</div>
</div>

<div id="feed" class="content active">
<?php
    // Seleciona apenas os projetos aprovados para exibição no feed
    $stmt = $pdo->query('
        SELECT projetos.id_projeto, projetos.empresa_id, empresas.ID_empresas, empresas.nome_empresa, empresas.profile_pic, projetos.nome_projeto, projetos.descricao, projetos.imagem_capa, projetos.nivel_especialidade, projetos.max_inscricoes, projetos.data_criacao
        FROM projetos 
        JOIN empresas ON projetos.empresa_id = empresas.ID_empresas 
        WHERE projetos.status_aprovacao = "aprovado"  -- Apenas projetos aprovados
        ORDER BY projetos.data_criacao DESC
    ');

    while ($row = $stmt->fetch()) {
        // Verifica quantas inscrições já existem para o projeto
        $stmt_count = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE id_projeto = :id_projeto');
        $stmt_count->execute(['id_projeto' => $row['id_projeto']]);
        $numInscritos = $stmt_count->fetchColumn();

        // Verifica se o usuário já está inscrito no projeto
        $stmt_inscricao = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE id_usuario = :id_usuario AND id_projeto = :id_projeto');
        $stmt_inscricao->execute(['id_usuario' => $userId, 'id_projeto' => $row['id_projeto']]);
        $jaInscrito = $stmt_inscricao->fetchColumn() > 0;

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

        // Verifica se há vagas disponíveis e se o usuário já está inscrito
        $vagasRestantes = $row['max_inscricoes'] - $numInscritos;
        if ($jaInscrito) {
            echo "<button class='btn btn-success' disabled>Inscrito</button>";
            if ($numInscritos >= $row['max_inscricoes']) {
                echo "<span class='badge badge-secondary ml-2'>Vagas Esgotadas</span>";
            }
        } elseif ($vagasRestantes > 0) {
            echo "<button class='btn btn-primary' onclick='inscreverProjeto(" . htmlspecialchars($row['id_projeto']) . ")'>Inscrever-se</button>";
            echo "<span class='badge badge-info ml-2'>" . $vagasRestantes . " vagas disponíveis</span>";
        } else {
            echo "<button class='btn btn-secondary' disabled>Vagas Esgotadas</button>";
        }

        echo "</div>"; // Fecha media-body
        echo "</div>"; // Fecha media
        echo "</div>"; // Fecha card-body
        echo "</div>"; // Fecha card
    }
?>
</div>

<div id="inscrito" class="content">
    <?php
    // Seleciona projetos inscritos pelo usuário
    $stmt = $pdo->prepare('
        SELECT projetos.*, empresas.nome_empresa, empresas.profile_pic AS empresa_profile_pic
        FROM inscricoes 
        JOIN projetos ON inscricoes.id_projeto = projetos.id_projeto
        JOIN empresas ON projetos.empresa_id = empresas.ID_empresas
        WHERE inscricoes.id_usuario = :user_id
        ORDER BY projetos.data_criacao DESC
    ');
    $stmt->execute(['user_id' => $userId]);

    while ($row = $stmt->fetch()) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-body'>";
        echo "<div class='media'>";
        $profile_pic_url = htmlspecialchars($row['empresa_profile_pic']) ?: 'default-profile.png';
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

<script>
    function showContent(id) {
        document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        document.querySelector(`.tab[onclick="showContent('${id}')"]`).classList.add('active');

        // Carrega o conteúdo do feed correspondente
        loadFeed(id);
    }

    // Função corrigida para redirecionar para o perfil da empresa
    function viewProfile(empresaId) {
        window.location.href = `profile_empresa.php?id=${empresaId}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
    loadFeed('feed'); // Carrega o feed padrão ao iniciar

    // Função para carregar o feed dinamicamente
    function loadFeed(feedType) {
        fetch('load_feed_projetos.php') // Carrega o feed para ambos usuários e funcionários
            .then(response => response.text())
            .then(data => {
                document.getElementById(feedType).innerHTML = data;
            })
            .catch(error => console.error('Erro ao carregar o feed:', error));
    }

    document.getElementById('postForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        fetch('post_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            loadFeed('feed'); // Recarrega o feed após o post
            this.reset();
            submitButton.disabled = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Erro ao enviar o post:', error);
            submitButton.disabled = false;
        });
    });
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
        .catch(error => console.error('Erro ao postar o comentário:', error));

        return false;
    }

    function loadComments(postId) {
        fetch(`load_comments.php?post_id=${postId}`)
        .then(response => response.text())
        .then(data => {
            document.querySelector(`#comments-${postId}`).innerHTML = data;
        })
        .catch(error => console.error('Erro ao carregar comentários:', error));
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

    // Função para inscrição em projetos
    function inscreverProjeto(projetoId) {
        fetch('inscrever_projeto.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id_projeto: projetoId,
                id_usuario: <?php echo $userId; ?>
            })
        })
        .then(response => response.text())
        .then(data => {
            alert('Inscrição realizada com sucesso!');
            // Recarrega a página para atualizar o feed
            location.reload();
        })
        .catch(error => console.error('Erro ao inscrever-se no projeto:', error));
    }

    // Corrigido o redirecionamento para a página correta do perfil da empresa
    function viewProfile(empresaId) {
        window.location.href = `profile_empresa.php?id_empresa=${empresaId}`;
    }

    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const postId = this.dataset.postId;
            postComment(postId);
        });
    });

    document.querySelectorAll('.toggle-comments').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            toggleComments(postId);
        });
    });
</script>
</body>
</html>
