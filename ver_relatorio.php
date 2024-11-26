<?php
session_start();
include 'conexao.php';

// Verifica se um ID de relatório foi fornecido
if (!isset($_GET['id'])) {
    header("Location: atividade_funcionario.php");
    exit();
}

$id_relatorio = $_GET['id'];

// Obter detalhes do relatório
$stmt = $pdo->prepare('
    SELECT r.*, f.nome_funcionario, f.sobrenome_funcionario, p.nome_projeto 
    FROM relatorios r
    JOIN funcionarios f ON r.id_funcionario = f.id_funcionario
    JOIN projetos p ON r.id_projeto = p.id_projeto
    WHERE r.id_relatorio = :id_relatorio
');
$stmt->execute(['id_relatorio' => $id_relatorio]);
$relatorio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$relatorio) {
    echo "Relatório não encontrado.";
    exit();
}

// Verifica se a empresa está logada
$isEmpresa = isset($_SESSION['id_empresa']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Relatório</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .relatorio-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .relatorio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .relatorio-header h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .relatorio-header a {
            font-size: 16px;
            text-decoration: none;
            color: #007bff;
        }
        .relatorio-info {
            margin-top: 20px;
        }
        .relatorio-info p {
            font-size: 16px;
            color: #555;
        }
        .relatorio-info a {
            color: #007bff;
            text-decoration: none;
        }
        .relatorio-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php 
// Inclui o cabeçalho correto com base no tipo de usuário logado
if ($isEmpresa) {
    include 'views/header_empresa.php';
} else {
    include 'views/header.php';
}
?>
<div class="container mt-5">
    <div class="relatorio-container">
        <div class="relatorio-header">
            <h2>Relatório: <?php echo htmlspecialchars($relatorio['assunto']); ?></h2>
            <a href="atividade_funcionario.php">Voltar para Relatórios</a>
        </div>
        <div class="relatorio-info">
            <p><strong>Funcionário:</strong> <?php echo htmlspecialchars($relatorio['nome_funcionario'] . ' ' . $relatorio['sobrenome_funcionario']); ?></p>
            <p><strong>Projeto:</strong> <?php echo htmlspecialchars($relatorio['nome_projeto']); ?></p>
            <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($relatorio['descricao'])); ?></p>
            <p><strong>Data de Envio:</strong> <?php echo date('d/m/Y H:i', strtotime($relatorio['data_envio'])); ?></p>

            <?php if ($relatorio['arquivo']): ?>
                <p><strong>Arquivo Anexado:</strong> <a href="<?php echo htmlspecialchars($relatorio['arquivo']); ?>" download>Clique aqui para baixar o arquivo</a></p>
            <?php endif; ?>

            <?php if ($relatorio['link']): ?>
                <p><strong>Link Anexado:</strong> <a href="<?php echo htmlspecialchars($relatorio['link']); ?>" target="_blank">Clique aqui para acessar o link</a></p>
            <?php endif; ?>

            <?php if (!$relatorio['arquivo'] && !$relatorio['link']): ?>
                <p><strong>Observação:</strong> Nenhum arquivo ou link foi anexado a este relatório.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
