
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

// Verificar se uma ação de exclusão foi realizada
if (isset($_GET['excluir'])) {
    $id_funcionario = $_GET['excluir'];
    
    // Deletar o funcionário do banco de dados
    $sql = "DELETE FROM funcionarios WHERE id_funcionario = :id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['id' => $id_funcionario])) {
        $mensagem = "Funcionário excluído com sucesso!";
    } else {
        $mensagem = "Erro ao excluir funcionário.";
    }
}

// Buscar todos os funcionários cadastrados
$sql = "SELECT * FROM funcionarios";
if (isset($_GET['busca'])) {
    $busca = $_GET['busca'];
    $sql .= " WHERE nome_funcionario LIKE :busca OR sobrenome_funcionario LIKE :busca OR email_funcionario LIKE :busca";
}
$stmt = $pdo->prepare($sql);
if (isset($busca)) {
    $stmt->execute(['busca' => '%' . $busca . '%']);
} else {
    $stmt->execute();
}
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Funcionários</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        .form-inline {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .form-inline input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php include 'views/header_empresa2.php'; ?>

<div class="content">
    <h1>Gerenciar Funcionários</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <a href="cadastrar_funcionario.php" class="btn btn-primary mb-3">Cadastrar Novo Funcionário</a>

    <form method="get" action="" class="form-inline">
        <input type="text" class="form-control" name="busca" placeholder="Buscar funcionário">
        <button type="submit" class="btn btn-info">Buscar</button>
    </form>

    <h2>Lista de Funcionários</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Data de Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($funcionarios as $funcionario): ?>
                <tr>
                    <td>
                        <img src="profile_pics/<?php echo $funcionario['profile_pic']; ?>" alt="Profile Picture" class="profile-pic" style="width:40px; height:40px; border-radius:50%; margin-right:10px;">
                        <?php echo $funcionario['nome_funcionario'] . ' ' . $funcionario['sobrenome_funcionario']; ?>
                    </td>
                    <td><?php echo $funcionario['email_funcionario']; ?></td>
                    <td><?php echo $funcionario['data_registro']; ?></td>
                    <td>
                        <a href="?excluir=<?php echo $funcionario['id_funcionario']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
