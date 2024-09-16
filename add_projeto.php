<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = ""; 


$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Obtendo informações da empresa logada
$email = $_SESSION['email'];
$sql = "SELECT * FROM empresas WHERE email_de_trabalho = '$email'";
$result = $conn->query($sql);
$empresa = $result->fetch_assoc();
$id_empresa = $empresa['ID_empresas'];

// Verifica se o formulário de cadastro foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    // Recebendo os dados do formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $nivel_especialidade = $_POST['nivel_especialidade'];

    // Processamento do upload da imagem de capa
    $imagem_capa = '';
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $nome_original = $_FILES['imagem_capa']['name'];
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

        
        $nome_original = preg_replace("/[^a-zA-Z0-9.]/", "_", $nome_original);
        $diretorio = __DIR__ . '/capa_projeto/';
        
        // Verifica se o diretório existe
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true); // Cria o diretório se não existir
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
    $sql = "INSERT INTO projetos (nome_projeto, descricao, nivel_especialidade, imagem_capa, empresa_id) 
            VALUES ('$nome', '$descricao', '$nivel_especialidade', '$imagem_capa', '$id_empresa')";

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
