<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Obter o nome de usuário da sessão
$email_usuario = $_SESSION['email'];

// Conectar ao banco de dados e buscar a imagem de perfil da empresa logada
include 'conexao.php';

// Consultar dados da empresa
$sql = "SELECT ID_empresas, profile_pic, nome_empresa FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a empresa possui uma imagem de perfil
$profile_pic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';
$id_empresa = $empresa['ID_empresas'];
$nome_empresa = $empresa['nome_empresa'];

// Consultar número total de projetos e projetos ativos
$sql_projetos = "SELECT 
                    COUNT(*) AS total_projetos, 
                    SUM(CASE WHEN status_aprovacao = 'aprovado' THEN 1 ELSE 0 END) AS projetos_ativos, 
                    SUM(CASE WHEN status_aprovacao = 'pendente' THEN 1 ELSE 0 END) AS projetos_pendentes 
                 FROM projetos WHERE empresa_id = :empresa_id";
$stmt_projetos = $pdo->prepare($sql_projetos);
$stmt_projetos->execute(['empresa_id' => $id_empresa]);
$dados_projetos = $stmt_projetos->fetch(PDO::FETCH_ASSOC);

// Consultar número de funcionários
$sql_funcionarios = "SELECT COUNT(*) AS total_funcionarios FROM funcionarios WHERE empresa_id = :empresa_id";
$stmt_funcionarios = $pdo->prepare($sql_funcionarios);
$stmt_funcionarios->execute(['empresa_id' => $id_empresa]);
$total_funcionarios = $stmt_funcionarios->fetchColumn();

// Consultar número de visitas
$sql_visitas = "SELECT 
                   COUNT(*) AS total_visitas, 
                   SUM(CASE WHEN WEEK(data_visita) = WEEK(NOW()) THEN 1 ELSE 0 END) AS visitas_semana 
                FROM visitas_empresa WHERE id_empresa = :empresa_id";
$stmt_visitas = $pdo->prepare($sql_visitas);
$stmt_visitas->execute(['empresa_id' => $id_empresa]);
$dados_visitas = $stmt_visitas->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="CSS/dash.css">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
   
</head>
<body>

<?php include 'views/header_empresa.php'; ?>

<div class="main-content">
    <h1>Dashboard</h1>

    <div class="cards">
        <!-- Card de Projetos -->
        <div class="card">
            <i class="fas fa-clipboard-list"></i>
            <h3>Projetos Totais</h3>
            <p><?php echo $dados_projetos['total_projetos']; ?></p>
            <p><span class="text-success">Ativos: <?php echo $dados_projetos['projetos_ativos']; ?></span></p>
            <p><span class="text-danger">Pendentes: <?php echo $dados_projetos['projetos_pendentes']; ?></span></p>
            <a href="visualizar_projetos.php" class="btn btn-primary">Ver projetos</a>
        </div>

        <!-- Card de Funcionários -->
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Funcionários</h3>
            <p>Total: <?php echo $total_funcionarios; ?></p>
        </div>

        <!-- Card de Visitas -->
        <div class="card" >
            <img src="<?php echo $profile_pic; ?>" alt="Logo/Foto">
            <h3><?php echo $nome_empresa; ?></h3>
            <p>Total de visitas: <?php echo $dados_visitas['total_visitas']; ?></p>
            <p>Visitas esta semana: <?php echo $dados_visitas['visitas_semana']; ?></p>
        </div>
    </div>

    <!-- Tabela de Relatórios -->
    <div class="table-container">
        <h3>Relatórios Recentes</h3>
        <div class="row">
            <?php
            // Consultar os relatórios mais recentes dos funcionários enviados nos últimos 7 dias
            $sql_relatorios = "SELECT 
                                    r.*, 
                                    f.nome_funcionario, 
                                    f.profile_pic AS foto_funcionario 
                                FROM relatorios r
                                JOIN funcionarios f ON r.id_funcionario = f.id_funcionario 
                                WHERE f.empresa_id = :empresa_id 
                                AND r.data_envio >= DATE_SUB(NOW(), INTERVAL 1 WEEK) 
                                ORDER BY r.data_envio DESC 
                                LIMIT 3";
            $stmt_relatorios = $pdo->prepare($sql_relatorios);
            $stmt_relatorios->execute(['empresa_id' => $id_empresa]);
            $relatorios = $stmt_relatorios->fetchAll(PDO::FETCH_ASSOC);

            // Verificar se há relatórios
            if ($relatorios) {
                // Exibe os relatórios recentes
                foreach ($relatorios as $relatorio) {
                    $foto_funcionario = $relatorio['foto_funcionario'] ? 'profile_pics/' . $relatorio['foto_funcionario'] : 'profile_pics/default-profile.png';
                    echo '
                    <div class="col-md-4">
                        <div class="activity-card">
                            <div>
                                <img src="' . $foto_funcionario . '" alt="Foto de perfil">
                                <div class="name">' . htmlspecialchars($relatorio['nome_funcionario']) . '</div>
                                <p>Fez um relatório</p>
                            </div>
                            <a href="atividade_funcionario.php" class="btn report-button">Ver relatório</a>
                        </div>
                    </div>';
                }

                // Exibe o botão "Ver mais relatórios" se houver relatórios
                echo '
                <div class="col-md-4">
                    <div class="card text-center" onclick="window.location.href=\'atividade_funcionario.php\'">
                        <h4>Ver mais relatórios</h4>
                        <i class="fas fa-plus-circle fa-2x"></i>
                    </div>
                </div>';
            } else {
                // Exibe mensagem de "Nenhum relatório recente" se não houver relatórios
                echo '<div class="col-md-12"><p class="text-muted">Nenhum relatório recente.</p></div>';
            }
            ?>
        </div>
    </div>


</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
