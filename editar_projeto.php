<?php
session_start();

// Verificar se o funcionário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
include 'conexao.php';

// Obter o ID do projeto a partir da URL
$id_projeto = $_GET['id'];

// Verificar se o funcionário tem permissão para editar este projeto
$email_funcionario = $_SESSION['email'];
$sql = "SELECT p.*, f.id_funcionario 
        FROM projetos p 
        JOIN funcionarios f ON p.id_funcionario = f.id_funcionario 
        WHERE p.id_projeto = :id_projeto AND f.email_funcionario = :email_funcionario";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_projeto' => $id_projeto, 'email_funcionario' => $email_funcionario]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o projeto não existir ou o funcionário não tiver permissão, redireciona
if (!$projeto) {
    header("Location: projeto_empresa.php");
    exit();
}

// Verificar se o formulário foi enviado para atualizar o projeto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_projeto = $_POST['nome_projeto'];
    $descricao = $_POST['descricao'];
    $nivel_especialidade = $_POST['nivel_especialidade'];
    $max_inscricoes = $_POST['max_inscricoes'];

    // Processar a imagem de capa se for enviada
    $imagem_capa = $projeto['imagem_capa'];
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $nome_original = $_FILES['imagem_capa']['name'];
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

        // Nome do arquivo seguro para evitar problemas
        $nome_seguro = preg_replace("/[^a-zA-Z0-9.]/", "_", $nome_original);
        $diretorio = __DIR__ . '/capa_projeto/';

        // Verifica se o diretório existe e o cria se necessário
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        // Move o arquivo enviado para o diretório
        if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $diretorio . $nome_seguro)) {
            $imagem_capa = $nome_seguro;
        } else {
            echo "Erro ao enviar a imagem de capa.";
        }
    }

    // Atualizar o projeto no banco de dados
    $sql = "UPDATE projetos 
            SET nome_projeto = :nome_projeto, descricao = :descricao, nivel_especialidade = :nivel_especialidade, imagem_capa = :imagem_capa, max_inscricoes = :max_inscricoes 
            WHERE id_projeto = :id_projeto";
    $stmt = $pdo->prepare($sql);
    $params = [
        'nome_projeto' => $nome_projeto,
        'descricao' => $descricao,
        'nivel_especialidade' => $nivel_especialidade,
        'imagem_capa' => $imagem_capa,
        'max_inscricoes' => $max_inscricoes,
        'id_projeto' => $id_projeto
    ];

    if ($stmt->execute($params)) {
        echo "Projeto atualizado com sucesso!";
        header("Location: projeto_empresa.php");
        exit();
    } else {
        echo "Erro ao atualizar o projeto.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Projeto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Editar Projeto
                    </div>
                    <div class="card-body">
                        <form action="editar_projeto.php?id=<?php echo $id_projeto; ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nome_projeto">Nome do Projeto:</label>
                                <input type="text" name="nome_projeto" class="form-control" value="<?php echo htmlspecialchars($projeto['nome_projeto']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <textarea name="descricao" class="form-control" required><?php echo htmlspecialchars($projeto['descricao']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="nivel_especialidade">Nível de Especialidade:</label>
                                <select name="nivel_especialidade" class="form-control" required>
                                    <option value="Baixo" <?php if ($projeto['nivel_especialidade'] == 'Baixo') echo 'selected'; ?>>Baixo</option>
                                    <option value="Intermediário" <?php if ($projeto['nivel_especialidade'] == 'Intermediário') echo 'selected'; ?>>Intermediário</option>
                                    <option value="Avançado" <?php if ($projeto['nivel_especialidade'] == 'Avançado') echo 'selected'; ?>>Avançado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="max_inscricoes">Quantidade Máxima de Inscrições:</label>
                                <input type="number" name="max_inscricoes" class="form-control" value="<?php echo htmlspecialchars($projeto['max_inscricoes']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="imagem_capa">Imagem de Capa:</label>
                                <input type="file" name="imagem_capa" class="form-control">
                                <?php if ($projeto['imagem_capa']): ?>
                                    <img src="capa_projeto/<?php echo htmlspecialchars($projeto['imagem_capa']); ?>" alt="Capa do Projeto" class="img-fluid mt-2">
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                            <a href="projeto_empresa.php" class="btn btn-secondary">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
