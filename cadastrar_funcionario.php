
<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Conectar ao banco de dados
include 'conexao.php';

// Variável para mensagem de erro ou sucesso
$mensagem = '';

// Verificar se o formulário foi enviado para cadastro de funcionário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar'])) {
    $nome_funcionario = $_POST['nome'];
    $sobrenome_funcionario = $_POST['sobrenome'];
    $email_funcionario = $_POST['email'];
    $senha_funcionario = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Inserir o funcionário no banco de dados
    $sql = "INSERT INTO funcionarios (nome_funcionario, sobrenome_funcionario, email_funcionario, senha_funcionario) VALUES (:nome, :sobrenome, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['nome' => $nome_funcionario, 'sobrenome' => $sobrenome_funcionario, 'email' => $email_funcionario, 'senha' => $senha_funcionario])) {
        $mensagem = "Funcionário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar funcionário.";
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
</head>
<body>
<?php include 'views/header_empresa2.php'; ?>

<div class="content">
    <h1>Cadastrar Novo Funcionário</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <form method="post" action="">
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
        <button type="submit" class="btn btn-primary" name="cadastrar">Cadastrar Funcionário</button>
    </form>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
