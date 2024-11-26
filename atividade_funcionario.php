<?php
session_start();
include 'conexao.php';

// Verificar se a empresa está logada
if (!isset($_SESSION['id_empresa'])) {
    header("Location: login.php");
    exit();
}

// Obter os relatórios enviados pelos funcionários para a empresa logada
$id_empresa = $_SESSION['id_empresa'];
$stmt = $pdo->prepare('
    SELECT r.*, f.nome_funcionario, f.sobrenome_funcionario, p.nome_projeto 
    FROM relatorios r
    JOIN funcionarios f ON r.id_funcionario = f.id_funcionario
    JOIN projetos p ON r.id_projeto = p.id_projeto
    WHERE f.empresa_id = :id_empresa
    ORDER BY r.data_envio DESC
');
$stmt->execute(['id_empresa' => $id_empresa]);
$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade dos Funcionários</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
</head>
<body>
<?php include 'views/header_empresa.php'; ?>
<div class="container mt-5">
    <h1>Relatórios dos Funcionários</h1>
    <?php if (empty($relatorios)): ?>
        <div class="alert alert-info">Nenhum relatório foi enviado até o momento.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Funcionário</th>
                    <th>Projeto</th>
                    <th>Data de Envio</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($relatorios as $relatorio): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($relatorio['nome_funcionario'] . ' ' . $relatorio['sobrenome_funcionario']); ?></td>
                        <td><?php echo htmlspecialchars($relatorio['nome_projeto']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($relatorio['data_envio'])); ?></td>
                        <td>
                            <a href="ver_relatorio.php?id=<?php echo $relatorio['id_relatorio']; ?>" class="btn btn-info">Ver Relatório</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
