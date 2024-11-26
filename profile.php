<?php
session_start();
include 'conexao.php'; // Inclua o arquivo de conexão com o banco de dados

// Verifica se o usuário ou funcionário está logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

// Determina o tipo de usuário logado e ajusta as variáveis de sessão e banco de dados
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
    $user_id = $_SESSION['user_id'];
} else {
    $userType = 'funcionario';
    $user_id = $_SESSION['id_funcionario'];
}

// Verifica o ID do perfil a ser visualizado
$viewing_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;
$is_own_profile = ($viewing_user_id == $user_id);

// Verifica se o perfil é do tipo cliente
$profileType = 'cliente';

$stmt = $pdo->prepare('SELECT nome_usuario, profile_pic, descricao FROM usuarios WHERE id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $viewing_user_id]);
$user = $stmt->fetch();

if (!$user) {
    // Se não encontrar o cliente, tenta buscar o funcionário
    $stmt = $pdo->prepare('SELECT CONCAT(nome_funcionario, " ", sobrenome_funcionario) AS nome_usuario, profile_pic, descricao FROM funcionarios WHERE id_funcionario = :id_funcionario');
    $stmt->execute(['id_funcionario' => $viewing_user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $profileType = 'funcionario';
    }
}

// Se nenhum usuário for encontrado, redireciona para a página inicial
if (!$user) {
    $_SESSION['error_message'] = 'Usuário não encontrado.';
    header('Location: home.php');
    exit();
}

// Verificar se o usuário logado já segue o perfil visualizado
$stmt = $pdo->prepare('SELECT * FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
$stmt->execute([
    'seguidor_id' => $user_id,
    'seguido_id' => $viewing_user_id
]);
$is_following = $stmt->rowCount() > 0;

// Processar upload de arquivos de projetos e edições de perfil (somente para clientes)
if ($userType === 'cliente' && $is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['nome_usuario'], $_POST['descricao'])) {
        // Atualização de Perfil
        $nome_usuario = $_POST['nome_usuario'];
        $descricao = $_POST['descricao'];
        $profile_pic = $_FILES['profile_pic'];

        if (!empty($nome_usuario) && !empty($descricao)) {
            $stmt = $pdo->prepare('UPDATE usuarios SET nome_usuario = :nome_usuario, descricao = :descricao WHERE id_usuario = :id_usuario');
            $stmt->execute([
                'nome_usuario' => $nome_usuario,
                'descricao' => $descricao,
                'id_usuario' => $user_id
            ]);

            // Processar upload de nova foto de perfil
            if ($profile_pic['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Definir o caminho completo da nova imagem
                $new_profile_pic_name = time() . '_' . basename($profile_pic['name']);
                $new_profile_pic_path = $upload_dir . $new_profile_pic_name;

                // Remover a imagem antiga (se existir)
                $stmt = $pdo->prepare('SELECT profile_pic FROM usuarios WHERE id_usuario = :id_usuario');
                $stmt->execute(['id_usuario' => $user_id]);
                $old_profile_pic = $stmt->fetchColumn();

                if ($old_profile_pic && file_exists($old_profile_pic)) {
                    unlink($old_profile_pic); // Remove a imagem antiga
                }

                // Mover a nova imagem para o diretório correto
                if (move_uploaded_file($profile_pic['tmp_name'], $new_profile_pic_path)) {
                    // Atualizar o caminho da nova imagem no banco de dados
                    $stmt = $pdo->prepare('UPDATE usuarios SET profile_pic = :profile_pic WHERE id_usuario = :id_usuario');
                    $stmt->execute([
                        'profile_pic' => $new_profile_pic_path,
                        'id_usuario' => $user_id
                    ]);
                } else {
                    $_SESSION['error_message'] = 'Erro ao fazer upload da nova imagem de perfil.';
                }
            }

            $_SESSION['success_message'] = 'Perfil atualizado com sucesso!';
        } else {
            $_SESSION['error_message'] = 'Todos os campos devem ser preenchidos.';
        }

        header('Location: profile.php?user_id=' . $user_id);
        exit();
    } elseif (isset($_POST['nome_projeto'], $_POST['descricao_projeto'])) {
        // Upload de Projeto
        $nome_projeto = $_POST['nome_projeto'];
        $descricao_projeto = $_POST['descricao_projeto'];
        $link_projeto = $_POST['link_projeto'] ?: null;
        $project_file = $_FILES['project_file']['name'];
        $caminho_arquivo = null;

        // Verifica se um arquivo foi enviado e faz o upload
        if (!empty($project_file) && $_FILES['project_file']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'project_files/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $caminho_arquivo = $upload_dir . basename($project_file);
            if (!move_uploaded_file($_FILES['project_file']['tmp_name'], $caminho_arquivo)) {
                $caminho_arquivo = null;
                $_SESSION['error_message'] = 'Erro ao fazer upload do arquivo do projeto.';
            }
        }

        // Verifica se há um link ou um arquivo enviado para o projeto
        if ($caminho_arquivo || $link_projeto) {
            $stmt = $pdo->prepare('INSERT INTO arquivos_projetos (id_usuario, nome_arquivo, caminho_arquivo, descricao) VALUES (:id_usuario, :nome_arquivo, :caminho_arquivo, :descricao)');
            $stmt->execute([
                'id_usuario' => $user_id,
                'nome_arquivo' => $nome_projeto,
                'caminho_arquivo' => $caminho_arquivo ?: $link_projeto, // Insere o link se não houver arquivo
                'descricao' => $descricao_projeto
            ]);
            $_SESSION['success_message'] = 'Projeto adicionado com sucesso!';
        } else {
            $_SESSION['error_message'] = 'Você deve fornecer um link ou um arquivo para o projeto.';
        }

        header('Location: profile.php?user_id=' . $user_id);
        exit();
    }
}

// Processar exclusão de projeto
if ($is_own_profile && isset($_GET['delete_project_id'])) {
    $delete_project_id = intval($_GET['delete_project_id']);

    $stmt = $pdo->prepare('DELETE FROM arquivos_projetos WHERE id_arquivo = :id_arquivo AND id_usuario = :id_usuario');
    $stmt->execute([
        'id_arquivo' => $delete_project_id,
        'id_usuario' => $user_id
    ]);
    header('Location: profile.php?user_id=' . $user_id);
    exit();
}

// Atualizar contagem de seguidores e seguidos
$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguido_id = :seguido_id');
$stmt->execute(['seguido_id' => $viewing_user_id]);
$total_seguidores = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguidor_id = :seguidor_id');
$stmt->execute(['seguidor_id' => $viewing_user_id]);
$total_seguidos = $stmt->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="CSS/profile.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href= "Img/SearchJob.png">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="feed.php">SearchJob</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
            <li class="nav-item"><a class="nav-link" href="mensagens.php">Mensagens</a></li>
            <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <a href="profile.php?user_id=<?php echo htmlspecialchars($viewing_user_id); ?>">
                        <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
                             <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Foto de perfil" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                        <?php else: ?>
                             <img src="default-profile.png" alt="Foto de perfil padrão" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                        <?php endif; ?>
                    </a>

                    <p class="card-text"><?php echo htmlspecialchars($user['nome_usuario']); ?>!</p>
                    <?php if (!empty($user['descricao'])): ?>
                        <p class="card-text"><?php echo htmlspecialchars($user['descricao']); ?></p>
                    <?php else: ?>
                        <p class="card-text">Nenhuma descrição definida.</p>
                    <?php endif; ?>

                    <p class="card-text">Seguidores: <?php echo $total_seguidores; ?></p>
                    <p class="card-text">Seguindo: <?php echo $total_seguidos; ?></p>

                   <!-- Botões removidos para funcionários, mantendo apenas a visualização de perfis -->
<?php if ($userType === 'cliente' && !$is_own_profile): ?>
    <div class="follow-button">
        <form action="<?php echo $is_following ? 'parar_de_seguir.php' : 'seguir.php'; ?>" method="POST">
            <input type="hidden" name="seguido_id" value="<?php echo $viewing_user_id; ?>">
            <button type="submit" class="btn <?php echo $is_following ? 'btn-danger' : 'btn-success'; ?>">
                <?php echo $is_following ? 'Deixar de Seguir' : 'Seguir'; ?>
            </button>
        </form>
    </div>
<?php endif; ?>

<?php if ($userType === 'cliente' && !$is_own_profile): ?>
    <a href="mensagens.php?user_id=<?php echo $viewing_user_id; ?>" class="btn btn-info">Enviar Mensagem</a>
<?php elseif ($userType === 'cliente' && $is_own_profile): ?>
    <a href="mensagens.php" class="btn btn-info">Ver Mensagens</a>
<?php endif; ?>


                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <?php if ($userType === 'cliente' && $is_own_profile): ?>
                        <button class="btn btn-primary" onclick="toggleEditSection()">Editar Perfil</button>
                        <button class="btn btn-secondary" onclick="toggleAddProjectSection()">Adicionar Projeto</button>

                        
                        <div id="editSection" class="edit-section mt-3">
                            <!-- Formulários de Atualização de Perfil -->
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nome_usuario">Nome:</label>
                                    <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" value="<?php echo htmlspecialchars($user['nome_usuario']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="descricao">Descrição:</label>
                                    <textarea class="form-control" id="descricao" name="descricao"><?php echo htmlspecialchars($user['descricao']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="profile_pic">Foto de Perfil:</label>
                                    <input type="file" class="form-control-file" id="profile_pic" name="profile_pic">
                                </div>
                                <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                            </form>
                </div>
                        <div id="addProjectSection" class="add-project-section mt-3">
                            <!-- Formulário de Adição de Projeto -->
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nome_projeto">Nome do Projeto:</label>
                                    <input type="text" class="form-control" id="nome_projeto" name="nome_projeto" required>
                                </div>
                                <div class="form-group">
                                    <label for="descricao_projeto">Descrição do Projeto:</label>
                                    <textarea class="form-control" id="descricao_projeto" name="descricao_projeto" ></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="link_projeto">Link do Projeto (opcional):</label>
                                    <input type="url" class="form-control" id="link_projeto" name="link_projeto">
                                </div>
                                <div class="form-group">
                                    <label for="project_file">Arquivo do Projeto (opcional):</label>
                                    <input type="file" class="form-control-file" id="project_file" name="project_file">
                                </div>
                                <button type="submit" class="btn btn-primary">Adicionar Projeto</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="buttom-container-search-projects">
                <input type ="submit"  class="btn-search-projects" value="Ver meus projetos">
            </div>

            <h3 class="mt-4">Meus Projetos</h3>
            <div class="projects-list">
                <?php
                $stmt = $pdo->prepare('SELECT * FROM arquivos_projetos WHERE id_usuario = :id_usuario');
                $stmt->execute(['id_usuario' => $viewing_user_id]);
                $projects = $stmt->fetchAll();
                foreach ($projects as $project):
                    ?>
                    <div class="project-item" id = "projetos">
                        <div class="project-info">
                            <h4><?php echo htmlspecialchars($project['nome_arquivo']); ?></h4>
                            <p><?php echo htmlspecialchars($project['descricao']); ?></p>
                            <?php if ($project['caminho_arquivo']): ?>
                                <p><a href="<?php echo htmlspecialchars($project['caminho_arquivo']); ?>" download>Baixar Arquivo</a></p>
                            <?php endif; ?>
                            <?php if ($project['caminho_arquivo'] && filter_var($project['caminho_arquivo'], FILTER_VALIDATE_URL)): ?>
                                <p><a href="<?php echo htmlspecialchars($project['caminho_arquivo']); ?>" target="_blank">Visitar Projeto</a></p>
                            <?php endif; ?>
                        </div>
                        
            <!-- Botões de edição e exclusão visíveis apenas para o próprio cliente -->
            <?php if ($userType === 'cliente' && $is_own_profile): ?>
                <div id="confirmModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <p>Você tem certeza que deseja excluir esse projeto?</p>
                        <div class="modal-buttons">
                            <button id="confirmDelete" class="btn btn-danger">Excluir</button>
                            <button id="cancelDelete" class="btn btn-secondary">Cancelar</button>
                        </div>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="editar_projeto_usuario.php?id=<?php echo $project['id_arquivo']; ?>" class="btn btn-primary">Editar</a>
                    <a href="profile.php?delete_project_id=<?php echo $project['id_arquivo']; ?>" class="btn btn-danger delete-project-btn">Excluir</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
        </div>
    </div>
</div>

<script>
// Função para alternar entre seções e fechar as outras
function toggleSection(sectionId) {
    var sections = document.querySelectorAll('.edit-section, .add-project-section');
    var section = document.getElementById(sectionId);
    var isCurrentlyVisible = section.classList.contains('active');

    // Fecha todas as seções removendo a classe 'active'
    sections.forEach(function(sec) {
        sec.classList.remove('active');
        sec.style.maxHeight = '0';  // Reseta a altura
    });

    // Se a seção clicada não estava visível, abre ela
    if (!isCurrentlyVisible) {
        section.classList.add('active');
        section.style.maxHeight = section.scrollHeight + 'px'; // Definir a altura exata do conteúdo
    }
}

// Funções antigas adaptadas para usar a nova função toggleSection
function toggleEditSection() {
    toggleSection('editSection'); // Passa o ID da seção de edição para a função principal
}

function toggleAddProjectSection() {
    toggleSection('addProjectSection'); // Passa o ID da seção de adição de projetos para a função principal
}

// Inicializa as seções escondidas ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    var sections = document.querySelectorAll('.edit-section, .add-project-section');
    sections.forEach(function(section) {
        section.classList.remove('active');
        section.style.maxHeight = '0'; // Garante que o maxHeight esteja zerado ao iniciar
    });
});
</script>
<script>

document.querySelector('.btn-search-projects').addEventListener('click', function() {
   
    const projetosSection = document.getElementById('projetos');

    // Rola até a seção de projetos suavemente
    projetosSection.scrollIntoView({ behavior: 'smooth' });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-project-btn');
        const confirmModal = document.getElementById('confirmModal');
        const confirmDelete = document.getElementById('confirmDelete');
        const cancelDelete = document.getElementById('cancelDelete');
        let projectIdToDelete = null;

        // Função para abrir o modal
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                projectIdToDelete = this.getAttribute('data-id');
                confirmModal.style.display = 'flex';
            });
        });

        // Confirmar exclusão
        confirmDelete.addEventListener('click', function () {
            if (projectIdToDelete) {
                window.location.href = `profile.php?delete_project_id=${projectIdToDelete}`;
            }
        });

        // Cancelar exclusão
        cancelDelete.addEventListener('click', function () {
            confirmModal.style.display = 'none';
            projectIdToDelete = null;
        });
    });
</script>

</body>
</html>