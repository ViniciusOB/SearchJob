<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: home.php");
    exit();
}

// Conectar ao banco de dados
include 'conexao.php';

// Obtendo o ID da empresa a partir da sessão
$email = $_SESSION['email'];
$sql = "SELECT ID_empresas FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);
$id_empresa = $empresa['ID_empresas'];

// Variável para mensagem de erro ou sucesso
$mensagem = '';

// Verificar se o formulário foi enviado para cadastro de funcionário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar'])) {
    $nome_funcionario = $_POST['nome'];
    $sobrenome_funcionario = $_POST['sobrenome'];
    $email_funcionario = $_POST['email'];
    $senha_funcionario = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Verificar se o email já está cadastrado nas tabelas de usuários, empresas ou funcionários
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM usuarios WHERE email_usuario = :email
        UNION ALL
        SELECT COUNT(*) FROM empresas WHERE email_de_trabalho = :email
        UNION ALL
        SELECT COUNT(*) FROM funcionarios WHERE email_funcionario = :email
    ");
    $stmt->execute(['email' => $email_funcionario]);
    $emailExists = array_sum($stmt->fetchAll(PDO::FETCH_COLUMN));

    if ($emailExists > 0) {
        $mensagem = "O email já está em uso. Por favor, use outro email.";
    } else {
        // Verificar se um arquivo de imagem foi enviado
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            $imagem_nome = $_FILES['profile_pic']['name'];
            $imagem_temp = $_FILES['profile_pic']['tmp_name'];
            $imagem_extensao = pathinfo($imagem_nome, PATHINFO_EXTENSION);

            // Verifica a extensão da imagem
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($imagem_extensao), $extensoes_permitidas)) {
                // Gera um nome único para a imagem
                $imagem_nova = uniqid() . '.' . $imagem_extensao;
                $caminho_destino = 'profile_pics/' . $imagem_nova;

                // Move a imagem para a pasta de destino
                if (move_uploaded_file($imagem_temp, $caminho_destino)) {
                    // Inserir o funcionário no banco de dados com o ID da empresa e imagem de perfil
                    $sql = "INSERT INTO funcionarios (nome_funcionario, sobrenome_funcionario, email_funcionario, senha_funcionario, empresa_id, profile_pic) 
                            VALUES (:nome, :sobrenome, :email, :senha, :empresa_id, :profile_pic)";
                    $stmt = $pdo->prepare($sql);
                    $params = [
                        'nome' => $nome_funcionario,
                        'sobrenome' => $sobrenome_funcionario,
                        'email' => $email_funcionario,
                        'senha' => $senha_funcionario,
                        'empresa_id' => $id_empresa,
                        'profile_pic' => $imagem_nova
                    ];
                } else {
                    $mensagem = "Erro ao mover a imagem enviada.";
                }
            } else {
                $mensagem = "Extensão de imagem não permitida. Apenas jpg, jpeg, png e gif são aceitos.";
            }
        } else {
            // Inserir o funcionário sem imagem de perfil
            $sql = "INSERT INTO funcionarios (nome_funcionario, sobrenome_funcionario, email_funcionario, senha_funcionario, empresa_id) 
                    VALUES (:nome, :sobrenome, :email, :senha, :empresa_id)";
            $stmt = $pdo->prepare($sql);
            $params = [
                'nome' => $nome_funcionario,
                'sobrenome' => $sobrenome_funcionario,
                'email' => $email_funcionario,
                'senha' => $senha_funcionario,
                'empresa_id' => $id_empresa
            ];
        }

        // Executa a query
        if ($stmt->execute($params)) {
            $mensagem = "Funcionário cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar funcionário.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Funcionário</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            height: 100vh;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            background-color: #333;
            padding-top: 20px;
            color: #fff;
            position: fixed;
            height: 100%;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
            overflow-y: auto; /* Para garantir que o conteúdo seja rolável */
        }

        .form-group label {
            font-weight: bold;
        }

        .alert {
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include 'views/header_empresa.php'; ?>

<div class="content">
    <h1>Cadastrar Novo Funcionário</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="sobrenome">Sobrenome:</label>
            <input type="text" class="form-control" id="sobrenome" name="sobrenome" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" class="form-control" id="senha" name="senha" required>
        </div>
        <div class="form-group">
            <label for="profile_pic">Foto de Perfil:</label>
            <input type="file" class="form-control-file" id="profile_pic" name="profile_pic" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary" name="cadastrar">Cadastrar Funcionário</button>
    </form>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
