<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $projeto_id = $_GET['id'];

    try {
        // Detalhes do projeto
        $stmtProjeto = $pdo->prepare("SELECT p.*, f.nome_funcionario, f.sobrenome_funcionario, e.nome_empresa
                                      FROM projetos p
                                      LEFT JOIN funcionarios f ON p.id_funcionario = f.id_funcionario
                                      LEFT JOIN empresas e ON p.empresa_id = e.ID_empresas
                                      WHERE p.id_projeto = :id");
        $stmtProjeto->bindParam(':id', $projeto_id);
        $stmtProjeto->execute();
        $projeto = $stmtProjeto->fetch(PDO::FETCH_ASSOC);

        if (!$projeto) {
            echo "Projeto não encontrado.";
            exit();
        }

    } catch (PDOException $e) {
        echo "Erro ao acessar o banco de dados: " . $e->getMessage();
        exit();
    }
} else {
    echo "ID do projeto não fornecido.";
    exit();
}

// Lógica para excluir o projeto
if (isset($_POST['excluir_projeto'])) {
    $id_projeto = $_POST['id_projeto'];

    try {
        // Deletar o projeto
        $stmtDeleteProjeto = $pdo->prepare("DELETE FROM projetos WHERE id_projeto = :id_projeto");
        $stmtDeleteProjeto->bindParam(':id_projeto', $id_projeto);
        $stmtDeleteProjeto->execute();

        // Redirecionar após a exclusão
        header("Location: dashboard_admin.php");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao excluir o projeto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Projeto</title>
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

        .delete-btn {
            background-color: red;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalhes do Projeto</h1>

        <p><strong>Nome do Projeto:</strong> <?php echo $projeto['nome_projeto']; ?></p>
        <p><strong>Descrição:</strong> <?php echo $projeto['descricao']; ?></p>
        <p><strong>Nível de Especialidade:</strong> <?php echo $projeto['nivel_especialidade']; ?></p>
        <p><strong>Data de Criação:</strong> <?php echo $projeto['data_criacao']; ?></p>
        <p><strong>Data Limite:</strong> <?php echo $projeto['data_limite'] . ' ' . $projeto['hora_limite']; ?></p>

        <?php if ($projeto['nome_funcionario']) { ?>
            <p><strong>Funcionário Responsável:</strong> <?php echo $projeto['nome_funcionario'] . ' ' . $projeto['sobrenome_funcionario']; ?></p>
        <?php } ?>

        <?php if ($projeto['nome_empresa']) { ?>
            <p><strong>Empresa:</strong> <?php echo $projeto['nome_empresa']; ?></p>
        <?php } ?>

        <form method="POST" action="detalhes_projeto.php?id=<?php echo $projeto_id; ?>" onsubmit="return confirm('Tem certeza que deseja excluir este projeto?');">
            <input type="hidden" name="id_projeto" value="<?php echo $projeto_id; ?>">
            <button type="submit" name="excluir_projeto" class="delete-btn">Excluir Projeto</button>
        </form>

        <a href="javascript:history.back()" class="back-link">Voltar</a>
    </div>
</body>
</html>
