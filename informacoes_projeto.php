<?php
session_start();
include 'conexao.php';

// Verifica se o projeto existe e obtém os detalhes
if (!isset($_GET['id'])) {
    header("Location: visualizar_projetos.php");
    exit();
}

$id_projeto = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM projetos WHERE id_projeto = :id_projeto');
$stmt->execute(['id_projeto' => $id_projeto]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    echo "Projeto não encontrado.";
    exit();
}

// Verifica se é uma empresa ou funcionário logado
$isEmpresa = isset($_SESSION['id_empresa']);
$isFuncionario = isset($_SESSION['id_funcionario']);

// Verifica se o usuário é a empresa responsável pelo projeto
if ($isEmpresa && $projeto['empresa_id'] != $_SESSION['id_empresa']) {
    echo "Você não tem permissão para acessar este projeto.";
    exit();
}

// Atualiza o status do projeto para 'aprovado' se a empresa aprovar
if ($isEmpresa && isset($_POST['aprovar'])) {
    $stmt = $pdo->prepare('UPDATE projetos SET status_aprovacao = "aprovado" WHERE id_projeto = :id_projeto');
    $stmt->execute(['id_projeto' => $id_projeto]);
    header("Location: informacoes_projeto.php?id=$id_projeto");
    exit();
}

// Busca usuários inscritos no projeto se o usuário for um funcionário logado
$inscritos = [];
if ($isFuncionario) {
    $stmt_inscritos = $pdo->prepare('
        SELECT u.nome_usuario, u.sobrenome_usuario, u.email_usuario, u.profile_pic 
        FROM inscricoes i 
        JOIN usuarios u ON i.id_usuario = u.id_usuario 
        WHERE i.id_projeto = :id_projeto
    ');
    $stmt_inscritos->execute(['id_projeto' => $id_projeto]);
    $inscritos = $stmt_inscritos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Projeto</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f9;
            padding: 20px;
        }
        .project-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .project-image {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .project-info h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .project-info p {
            font-size: 16px;
            color: #555;
        }
        .btn-approve {
            margin-top: 20px;
        }
        .inscritos-container {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .inscrito-card {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .inscrito-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .inscrito-card .inscrito-info {
            font-size: 16px;
        }
    </style>
</head>
<body>

<?php
// Include do header de acordo com o tipo de usuário logado
if ($isEmpresa) {
    include 'views/header_empresa.php';
} elseif ($isFuncionario) {
    include 'views/header.php';
}
?>

<div class="project-container">
    <img src="capa_projeto/<?php echo htmlspecialchars($projeto['imagem_capa']); ?>" class="project-image" alt="Imagem do Projeto">
    <div class="project-info">
        <h2><?php echo htmlspecialchars($projeto['nome_projeto']); ?></h2>
        <p><strong>Nível de Especialidade:</strong> <?php echo htmlspecialchars($projeto['nivel_especialidade']); ?></p>
        <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
        <p><strong>Status de Aprovação:</strong> <?php echo htmlspecialchars($projeto['status_aprovacao']); ?></p>
        <p><strong>Máximo de Inscrições:</strong> <?php echo htmlspecialchars($projeto['max_inscricoes']); ?></p>
    </div>

    <?php if ($isEmpresa && $projeto['status_aprovacao'] == 'pendente'): ?>
        <form method="post">
            <button type="submit" name="aprovar" class="btn btn-success btn-approve">Aprovar Projeto</button>
        </form>
    <?php endif; ?>

    <?php if ($isFuncionario && $projeto['status_aprovacao'] == 'aprovado'): ?>
        <div class="inscritos-container">
            <h4>Usuários Inscritos no Projeto</h4>
            <?php if (empty($inscritos)): ?>
                <p>Nenhum usuário inscrito neste projeto.</p>
            <?php else: ?>
                <?php foreach ($inscritos as $inscrito): ?>
                    <div class="inscrito-card">
                        <img src="profile_pics/<?php echo htmlspecialchars($inscrito['profile_pic'] ?: 'default-profile.png'); ?>" alt="Foto de Perfil">
                        <div class="inscrito-info">
                            <strong><?php echo htmlspecialchars($inscrito['nome_usuario'] . ' ' . $inscrito['sobrenome_usuario']); ?></strong><br>
                            <span><?php echo htmlspecialchars($inscrito['email_usuario']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
