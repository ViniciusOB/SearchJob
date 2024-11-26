<?php
session_start();
include 'conexao.php'; 


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Verifica se o ID do projeto foi passado e é um número válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID do projeto inválido.');
}

$projeto_id = intval($_GET['id']);


try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o projeto pertence ao usuário
    $stmt = $pdo->prepare('SELECT * FROM arquivos_projetos WHERE id_arquivo = :id_arquivo AND id_usuario = :id_usuario');
    $stmt->execute(['id_arquivo' => $projeto_id, 'id_usuario' => $user_id]);
    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        die('Projeto não encontrado ou você não tem permissão para editar este projeto.');
    }

    // Processar atualização do projeto
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome_projeto = $_POST['nome_projeto'] ?? '';
        $descricao_projeto = $_POST['descricao_projeto'] ?? '';
        $link_projeto = $_POST['link_projeto'] ?? '';

        // Manter o arquivo existente, se nenhum novo for enviado
        $caminho_arquivo = $projeto['caminho_arquivo'];

        if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'project_files/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $caminho_arquivo = $upload_dir . basename($_FILES['project_file']['name']);
            if (!move_uploaded_file($_FILES['project_file']['tmp_name'], $caminho_arquivo)) {
                $error = 'Erro ao fazer upload do arquivo do projeto.';
                echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            }
        }

        // Atualizar informações do projeto
        $stmt = $pdo->prepare('UPDATE arquivos_projetos SET nome_arquivo = :nome_projeto, caminho_arquivo = :caminho_arquivo, descricao = :descricao_projeto WHERE id_arquivo = :id_arquivo AND id_usuario = :id_usuario');
        $stmt->execute([
            'nome_projeto' => $nome_projeto,
            'caminho_arquivo' => $caminho_arquivo,
            'descricao_projeto' => $descricao_projeto,
            'id_arquivo' => $projeto_id,
            'id_usuario' => $user_id
        ]);

        // Redirecionar para o perfil após a atualização
        header('Location: profile.php?user_id=' . $user_id);
        exit();
    }
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Projeto</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Editar Projeto</h2>

    <!-- Exibe o formulário de edição com os dados do projeto -->
    <form action="editar_projeto_usuario.php?id=<?php echo htmlspecialchars($projeto_id); ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nome_projeto">Nome do Projeto:</label>
            <input type="text" class="form-control" id="nome_projeto" name="nome_projeto" value="<?php echo htmlspecialchars($projeto['nome_arquivo']); ?>" required>
        </div>
        <div class="form-group">
            <label for="descricao_projeto">Descrição:</label>
            <textarea class="form-control" id="descricao_projeto" name="descricao_projeto" required><?php echo htmlspecialchars($projeto['descricao']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="link_projeto">Link:</label>
            <input type="url" class="form-control" id="link_projeto" name="link_projeto" value="">
        </div>
        <div class="form-group">
            <label for="project_file">Arquivo:</label>
            <input type="file" class="form-control" id="project_file" name="project_file">
            <p>Arquivo atual: <?php echo htmlspecialchars($projeto['caminho_arquivo']); ?></p>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar Projeto</button>
    </form>
</div>
</body>
</html>
