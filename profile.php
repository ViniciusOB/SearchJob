<?php
session_start();
include 'conexao.php'; 
include 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$viewing_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;
$is_own_profile = ($viewing_user_id == $user_id);


if ($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['descricao'])) {
    $descricao = $_POST['descricao'];

    if (!empty($descricao)) {
        $stmt = $pdo->prepare('UPDATE usuarios SET descricao = :descricao WHERE id_usuario = :id_usuario');
        if ($stmt->execute([
            'descricao' => $descricao,
            'id_usuario' => $user_id
        ])) {
            $_SESSION['success_message'] = 'Descrição atualizada com sucesso!';
        } else {
            $_SESSION['error_message'] = 'Erro ao atualizar a descrição.';
        }
    } else {
        $_SESSION['error_message'] = 'Descrição não pode estar vazia.';
    }
    header('Location: profile.php?user_id=' . $user_id);
    exit();
}

// Verificar se o usuário logado já segue o perfil visualizado
$stmt = $pdo->prepare('SELECT * FROM seguidores WHERE seguidor_id = :seguidor_id AND seguido_id = :seguido_id');
$stmt->execute([
    'seguidor_id' => $user_id,
    'seguido_id' => $viewing_user_id
]);
$is_following = $stmt->rowCount() > 0;

// Processar upload de arquivos de projetos
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['project_file'])) {
    $upload_dir = '../project_files/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $project_file = $upload_dir . basename($_FILES['project_file']['name']);
    if (move_uploaded_file($_FILES['project_file']['tmp_name'], $project_file)) {
        $stmt = $pdo->prepare('INSERT INTO arquivos_projetos (id_usuario, nome_arquivo, caminho_arquivo, descricao) VALUES (:id_usuario, :nome_arquivo, :caminho_arquivo, :descricao)');
        $stmt->execute([
            'id_usuario' => $user_id,
            'nome_arquivo' => $_POST['nome_projeto'],
            'caminho_arquivo' => $project_file,
            'descricao' => $_POST['descricao_projeto']
        ]);
        header('Location: profile.php?user_id=' . $user_id);
        exit();
    } else {
        $error = 'Erro ao fazer upload do arquivo do projeto.';
    }
}

// Processar adição de novo projeto
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_project']) && !isset($_FILES['project_file'])) {
    $nome_projeto = $_POST['nome_projeto'];
    $descricao_projeto = $_POST['descricao_projeto'];
    $link_projeto = $_POST['link_projeto'];
    $project_file = $_FILES['project_file']['name'];

    if ($project_file) {
        $upload_dir = '../project_files/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $caminho_arquivo = $upload_dir . basename($project_file);
        if (!move_uploaded_file($_FILES['project_file']['tmp_name'], $caminho_arquivo)) {
            $error = 'Erro ao fazer upload do arquivo do projeto.';
        }
    } else {
        $caminho_arquivo = null;
    }

    $stmt = $pdo->prepare('INSERT INTO arquivos_projetos (id_usuario, nome_arquivo, caminho_arquivo, descricao) VALUES (:id_usuario, :nome_projeto, :caminho_arquivo, :descricao_projeto)');
    $stmt->execute([
        'id_usuario' => $user_id,
        'nome_projeto' => $nome_projeto,
        'caminho_arquivo' => $caminho_arquivo,
        'descricao_projeto' => $descricao_projeto
    ]);
    header('Location: profile.php?user_id=' . $user_id);
    exit();
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

 // Contar quantos seguem o usuário visualizado
$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguido_id = :seguido_id');
$stmt->execute(['seguido_id' => $viewing_user_id]);
$total_seguidores = $stmt->fetchColumn();
var_dump($total_seguidores); // Adicionando para debug

// Contar quantos o usuário visualizado está seguindo
$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguidor_id = :seguidor_id');
$stmt->execute(['seguidor_id' => $viewing_user_id]);
$total_seguidos = $stmt->fetchColumn();
var_dump($total_seguidos); // Adicionando para debug


}

// Obter dados do usuário
$stmt = $pdo->prepare('SELECT nome_usuario, profile_pic, descricao FROM usuarios WHERE id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $viewing_user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        .edit-section, .add-project-section {
            display: none;
        }
        .project-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .project-item .project-info {
            flex-grow: 1;
            text-align: center;
        }
        .project-item .btn-group {
            display: flex;
            flex-direction: column;
        }
        .project-item img {
            max-width: 100px;
            height: auto;
        }
        .follow-button {
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <a href="profile.php?user_id=<?php echo htmlspecialchars($viewing_user_id); ?>">
                        <?php if ($user['profile_pic']): ?>
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

                    <p class="card-text">Seguidores: <?php echo $total_seguidores ?: ''; ?></p>
                    <p class="card-text">Seguindo: <?php echo $total_seguidos ?: ''; ?></p>


                    <!-- Botão de seguir/parar de seguir -->
                    <?php if (!$is_own_profile): ?>
                        <div class="follow-button">
                            <form action="<?php echo $is_following ? 'parar_de_seguir.php' : 'seguir.php'; ?>" method="POST">
                                <input type="hidden" name="seguido_id" value="<?php echo $viewing_user_id; ?>">
                                <button type="submit" class="btn <?php echo $is_following ? 'btn-danger' : 'btn-success'; ?>">
                                    <?php echo $is_following ? 'Deixar de Seguir' : 'Seguir'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (!$is_own_profile): ?>
                        <a href="mensagens.php?user_id=<?php echo $viewing_user_id; ?>" class="btn btn-info">Enviar Mensagem</a>
                    <?php else: ?>
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

                    <?php if ($is_own_profile): ?>
                        <button class="btn btn-primary" onclick="toggleEditSection()">Editar Perfil</button>
                        <button class="btn btn-secondary" onclick="toggleAddProjectSection()">Adicionar Projeto</button>
                        <div id="editSection" class="edit-section mt-3">
                            <!-- Formulários de Atualização de Perfil -->
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="descricao">Descrição:</label>
                                    <textarea class="form-control" id="descricao" name="descricao"><?php echo htmlspecialchars($user['descricao']); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Atualizar Descrição</button>
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
                                    <textarea class="form-control" id="descricao_projeto" name="descricao_projeto" required></textarea>
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

            <h3 class="mt-4">Projetos</h3>
            <div class="projects-list">
                <?php
                $stmt = $pdo->prepare('SELECT * FROM arquivos_projetos WHERE id_usuario = :id_usuario');
                $stmt->execute(['id_usuario' => $viewing_user_id]);
                $projects = $stmt->fetchAll();
                foreach ($projects as $project):
                    ?>
                    <div class="project-item">
                        <div class="project-info">
                            <h4><?php echo htmlspecialchars($project['nome_arquivo']); ?></h4>
                            <p><?php echo htmlspecialchars($project['descricao']); ?></p>
                            <?php if ($project['caminho_arquivo']): ?>
                                <p><a href="<?php echo htmlspecialchars($project['caminho_arquivo']); ?>" download>Baixar Arquivo</a></p>
                            <?php endif; ?>
                            <?php if ($project['link_projeto']): ?>
                                <p><a href="<?php echo htmlspecialchars($project['link_projeto']); ?>" target="_blank">Visitar Projeto</a></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($is_own_profile): ?>
                            <div class="btn-group">
                                <a href="editar_projeto_usuario.php?id=<?php echo $project['id_arquivo']; ?>" class="btn btn-primary">Editar</a>
                                <a href="profile.php?delete_project_id=<?php echo $project['id_arquivo']; ?>" class="btn btn-danger">Excluir</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleEditSection() {
    var editSection = document.getElementById('editSection');
    editSection.style.display = (editSection.style.display === 'none') ? 'block' : 'none';
}

function toggleAddProjectSection() {
    var addProjectSection = document.getElementById('addProjectSection');
    addProjectSection.style.display = (addProjectSection.style.display === 'none') ? 'block' : 'none';
}
</script>
</body>
</html>
