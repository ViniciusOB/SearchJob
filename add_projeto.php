<?php
session_start();

// Verificar se o funcionário está logado
if (!isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

// Conexão com o banco de dados MySQL
$servername = "localhost"; 
$username = "u451416913_2024grupo10"; 
$password = "Grupo10@123"; 
$dbname = "u451416913_2024grupo10"; 

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Obtendo o ID do funcionário logado e sua empresa associada
$id_funcionario = $_SESSION['id_funcionario'];

// Busca o ID da empresa do funcionário
$sql = "SELECT empresa_id FROM funcionarios WHERE id_funcionario = $id_funcionario";
$result = $conn->query($sql);
$funcionario = $result->fetch_assoc();
$id_empresa = $funcionario['empresa_id'];  // Empresa associada ao funcionário

// Verifica se o formulário de cadastro foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    // Recebendo os dados do formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $nivel_especialidade = $_POST['nivel_especialidade'];
    $max_inscricoes = $_POST['max_inscricoes'];  // Número máximo de inscrições

    // Processamento do upload da imagem de capa
    $imagem_capa = '';
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $nome_original = $_FILES['imagem_capa']['name'];
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

        // Sanitize o nome do arquivo
        $nome_original = preg_replace("/[^a-zA-Z0-9.]/", "_", $nome_original);
        $diretorio = __DIR__ . '/capa_projeto/';
        
        // Verifica se o diretório existe, se não, cria-o
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        // Move o arquivo para o diretório 'capa_projeto'
        if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $diretorio . $nome_original)) {
            $imagem_capa = $nome_original;
        } else {
            echo "Falha ao enviar o arquivo da imagem de capa.";
        }
    } else {
        echo "Nenhuma imagem de capa foi enviada ou houve um erro no envio.";
    }

    // Preparando a consulta SQL para inserir os dados na tabela do banco de dados
    $sql = "INSERT INTO projetos (nome_projeto, descricao, nivel_especialidade, imagem_capa, max_inscricoes, empresa_id, id_funcionario) 
            VALUES ('$nome', '$descricao', '$nivel_especialidade', '$imagem_capa', '$max_inscricoes', '$id_empresa', '$id_funcionario')";

    // Executando a consulta SQL
    if ($conn->query($sql) === TRUE) {
        echo "Projeto cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar o projeto: " . $conn->error;
    }
}

// Fechando a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Projeto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Cadastro de Projeto
                    </div>
                    <div class="card-body">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nome">Nome do Projeto:</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <textarea name="descricao" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="nivel_especialidade">Nível de Especialidade:</label>
                                <select name="nivel_especialidade" class="form-control" required>
                                    <option value="Baixo">Baixo</option>
                                    <option value="Intermediário">Intermediário</option>
                                    <option value="Avançado">Avançado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="max_inscricoes">Máximo de Inscrições:</label>
                                <input type="number" name="max_inscricoes" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="imagem_capa">Capa do projeto:</label>
                                <input type="file" name="imagem_capa" class="form-control">
                            </div>
                            <button type="submit" name="cadastrar" class="btn btn-primary">Cadastrar</button>
                            <a href="projeto_empresa.php" class="btn btn-secondary">Voltar</a> <!-- Botão para voltar -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
