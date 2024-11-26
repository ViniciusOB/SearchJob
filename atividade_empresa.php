<?php
session_start();
include 'conexao.php';

// Verificar se a empresa está logada
if (!isset($_SESSION['id_empresa'])) {
    header('Location: login.php');
    exit();
}

// Obter o ID da empresa logada
$id_empresa = $_SESSION['id_empresa'];

// Obter o número total de visitas
$sql_visitas_total = "SELECT COUNT(*) as total_visitas FROM visitas_empresa WHERE id_empresa = :id_empresa";
$stmt = $pdo->prepare($sql_visitas_total);
$stmt->execute(['id_empresa' => $id_empresa]);
$total_visitas = $stmt->fetch(PDO::FETCH_ASSOC)['total_visitas'];

// Obter o número de visitas na última semana
$sql_visitas_semana = "
    SELECT COUNT(*) as visitas_semana 
    FROM visitas_empresa 
    WHERE id_empresa = :id_empresa 
    AND data_visita >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $pdo->prepare($sql_visitas_semana);
$stmt->execute(['id_empresa' => $id_empresa]);
$visitas_semana = $stmt->fetch(PDO::FETCH_ASSOC)['visitas_semana'];

// Obter o número de funcionários da empresa
$sql_funcionarios = "SELECT COUNT(*) as num_funcionarios FROM funcionarios WHERE empresa_id = :id_empresa";
$stmt = $pdo->prepare($sql_funcionarios);
$stmt->execute(['id_empresa' => $id_empresa]);
$num_funcionarios = $stmt->fetch(PDO::FETCH_ASSOC)['num_funcionarios'];

// Obter o número total de projetos e projetos ativos
$sql_projetos = "
    SELECT 
        COUNT(*) as total_projetos, 
        SUM(CASE WHEN status_aprovacao = 'aprovado' THEN 1 ELSE 0 END) as projetos_ativos 
    FROM projetos 
    WHERE empresa_id = :id_empresa";
$stmt = $pdo->prepare($sql_projetos);
$stmt->execute(['id_empresa' => $id_empresa]);
$projetos = $stmt->fetch(PDO::FETCH_ASSOC);
$total_projetos = $projetos['total_projetos'];
$projetos_ativos = $projetos['projetos_ativos'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade da Empresa</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            margin-left: 270px; /* Espaço para o menu lateral */
            padding: 20px;
        }
        .activity-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .activity-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .activity-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            margin-bottom: 15px;
        }
        .activity-card h5 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }
        .activity-card p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include 'views/header_empresa.php'; ?>

    <div class="container">
        <div class="activity-container">
            <div class="activity-header">Resumo da Atividade da Empresa</div>

            <div class="activity-card">
                <h5>Total de Visitas</h5>
                <p><?php echo htmlspecialchars($total_visitas); ?></p>
            </div>

            <div class="activity-card">
                <h5>Visitas na Última Semana</h5>
                <p><?php echo htmlspecialchars($visitas_semana); ?></p>
            </div>

            <div class="activity-card">
                <h5>Número de Funcionários</h5>
                <p><?php echo htmlspecialchars($num_funcionarios); ?></p>
            </div>

            <div class="activity-card">
                <h5>Total de Projetos</h5>
                <p><?php echo htmlspecialchars($total_projetos); ?></p>
            </div>

            <div class="activity-card">
                <h5>Projetos Ativos</h5>
                <p><?php echo htmlspecialchars($projetos_ativos); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
