<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $empresa_id = $_GET['id'];

    try {
        // Detalhes da empresa
        $stmtEmpresa = $pdo->prepare("SELECT * FROM empresas WHERE ID_empresas = :id");
        $stmtEmpresa->bindParam(':id', $empresa_id);
        $stmtEmpresa->execute();
        $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

        // Funcionários da empresa: Selecionar os funcionários associados à empresa usando o campo empresa_id
        $stmtFuncionarios = $pdo->prepare("SELECT * FROM funcionarios WHERE empresa_id = :empresa_id");
        $stmtFuncionarios->bindParam(':empresa_id', $empresa_id);
        $stmtFuncionarios->execute();
        $funcionarios = $stmtFuncionarios->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao acessar o banco de dados: " . $e->getMessage();
    }
} else {
    echo "ID da empresa não fornecido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Empresa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        h2 {
            font-size: 1.6rem;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            color: #2980b9;
        }

        button {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #c0392b;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
        }

        .btn-voltar {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalhes da Empresa: <?php echo $empresa['nome_empresa']; ?></h1>

        <h2>Funcionários:</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Sobrenome</th>
                    <th>Email</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($funcionarios)) { ?>
                    <?php foreach ($funcionarios as $funcionario) { ?>
                        <tr>
                            <td><a href="detalhes_funcionario.php?id=<?php echo $funcionario['id_funcionario']; ?>"><?php echo $funcionario['nome_funcionario']; ?></a></td>
                            <td><?php echo $funcionario['sobrenome_funcionario']; ?></td>
                            <td><?php echo $funcionario['email_funcionario']; ?></td>
                            <td>
                                <form method="POST" action="detalhes_empresa.php?id=<?php echo $empresa_id; ?>" onsubmit="return confirm('Tem certeza que deseja excluir este funcionário e tudo o que está relacionado a ele?');">
                                    <input type="hidden" name="id_funcionario" value="<?php echo $funcionario['id_funcionario']; ?>">
                                    <button type="submit" name="excluir_funcionario">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="no-data">Nenhum funcionário encontrado para esta empresa.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="dashboard_admin.php" class="btn-voltar">Voltar ao Dashboard</a>
    </div>
</body>
</html>

<?php
// Lógica para excluir o funcionário e tudo que está ligado a ele
if (isset($_POST['excluir_funcionario'])) {
    $id_funcionario = $_POST['id_funcionario'];

    try {
        // Excluir registros dependentes relacionados ao funcionário
        $stmtDeleteProjetos = $pdo->prepare("DELETE FROM projetos WHERE id_funcionario = :id_funcionario");
        $stmtDeleteProjetos->bindParam(':id_funcionario', $id_funcionario);
        $stmtDeleteProjetos->execute();

        $stmtDeletePosts = $pdo->prepare("DELETE FROM posts WHERE funcionario_id = :id_funcionario");
        $stmtDeletePosts->bindParam(':id_funcionario', $id_funcionario);
        $stmtDeletePosts->execute();

        $stmtDeleteComentarios = $pdo->prepare("DELETE FROM comentarios WHERE funcionario_id = :id_funcionario");
        $stmtDeleteComentarios->bindParam(':id_funcionario', $id_funcionario);
        $stmtDeleteComentarios->execute();

        $stmtDeleteRelatorios = $pdo->prepare("DELETE FROM relatorios WHERE id_funcionario = :id_funcionario");
        $stmtDeleteRelatorios->bindParam(':id_funcionario', $id_funcionario);
        $stmtDeleteRelatorios->execute();

        // Deletar o funcionário
        $stmtDeleteFuncionario = $pdo->prepare("DELETE FROM funcionarios WHERE id_funcionario = :id_funcionario");
        $stmtDeleteFuncionario->bindParam(':id_funcionario', $id_funcionario);
        $stmtDeleteFuncionario->execute();

        // Redirecionar para a mesma página após a exclusão
        header("Location: detalhes_empresa.php?id=" . $empresa_id);
        exit();
    } catch (PDOException $e) {
        echo "Erro ao excluir funcionário: " . $e->getMessage();
    }
}
?>
