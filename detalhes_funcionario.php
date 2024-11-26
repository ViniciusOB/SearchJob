<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $funcionario_id = $_GET['id'];

    try {
        // Detalhes do funcionário
        $stmtFuncionario = $pdo->prepare("SELECT * FROM funcionarios WHERE id_funcionario = :id");
        $stmtFuncionario->bindParam(':id', $funcionario_id);
        $stmtFuncionario->execute();
        $funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);

        // Projetos criados pelo funcionário (correção: buscar por id_funcionario)
        $stmtProjetos = $pdo->prepare("SELECT * FROM projetos WHERE id_funcionario = :funcionario_id");
        $stmtProjetos->bindParam(':funcionario_id', $funcionario_id);
        $stmtProjetos->execute();
        $projetos = $stmtProjetos->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao acessar o banco de dados: " . $e->getMessage();
    }
} else {
    echo "ID do funcionário não fornecido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Funcionário</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #555;
        }

        h2 {
            font-size: 1.5rem;
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

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalhes do Funcionário</h1>

        <p><strong>Nome:</strong> <?php echo $funcionario['nome_funcionario'] . ' ' . $funcionario['sobrenome_funcionario']; ?></p>
        <p><strong>Email:</strong> <?php echo $funcionario['email_funcionario']; ?></p>

        <h2>Projetos Criados:</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome do Projeto</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($projetos) > 0) { ?>
                    <?php foreach ($projetos as $projeto) { ?>
                        <tr>
                            <td><a href="detalhes_projeto.php?id=<?php echo $projeto['id_projeto']; ?>"><?php echo $projeto['nome_projeto']; ?></a></td>
                            <td><?php echo $projeto['descricao']; ?></td>
                            <td>
                                <a href="detalhes_projeto.php?id=<?php echo $projeto['id_projeto']; ?>">Ver Detalhes</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3">Nenhum projeto criado por este funcionário.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <a href="javascript:history.back()" class="back-link">Voltar</a>
    </div>
</body>
</html>
