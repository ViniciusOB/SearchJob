<?php
session_start();
include 'conexao.php';

// Verifica se o usuário ou funcionário está logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    header('Location: login.php');
    exit();
}

// Determina o tipo de usuário logado e ajusta as variáveis de sessão e banco de dados
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
    $user_id = $_SESSION['user_id'];
} else {
    $userType = 'funcionario';
    $user_id = $_SESSION['id_funcionario'];
}

// Verifica o ID do perfil a ser visualizado
$viewing_user_id = isset($_GET['funcionario_id']) ? intval($_GET['funcionario_id']) : $user_id;
$is_own_profile = ($viewing_user_id == $user_id);

// Consulta o banco para verificar se o ID visualizado pertence a um funcionário
$stmt = $pdo->prepare('SELECT CONCAT(nome_funcionario, " ", sobrenome_funcionario) AS nome_usuario, profile_pic FROM funcionarios WHERE id_funcionario = :id_funcionario');
$stmt->execute(['id_funcionario' => $viewing_user_id]);
$user = $stmt->fetch();

// Se nenhum funcionário for encontrado, redireciona para a página inicial
if (!$user) {
    $_SESSION['error_message'] = 'Funcionário não encontrado.';
    header('Location: home.php');
    exit();
}

// Verificar se o usuário logado já segue o perfil visualizado
$stmt = $pdo->prepare('SELECT * FROM seguidores 
    WHERE (seguidor_id = :seguidor_id AND seguido_funcionario_id = :seguido_funcionario_id)
    OR (seguidor_funcionario_id = :seguidor_funcionario_id AND seguido_id = :seguido_id)
    OR (seguidor_funcionario_id = :seguidor_funcionario_id AND seguido_funcionario_id = :seguido_funcionario_id)');
$stmt->execute([
    'seguidor_id' => $userType === 'cliente' ? $user_id : null,
    'seguido_funcionario_id' => $viewing_user_id,
    'seguidor_funcionario_id' => $userType === 'funcionario' ? $user_id : null,
    'seguido_id' => $userType === 'cliente' ? $viewing_user_id : null
]);
$is_following = $stmt->rowCount() > 0;

// Atualizar contagem de seguidores e seguidos
$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguido_funcionario_id = :seguido_funcionario_id OR seguido_id = :seguido_id');
$stmt->execute([
    'seguido_funcionario_id' => $viewing_user_id,
    'seguido_id' => $viewing_user_id
]);
$total_seguidores = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM seguidores WHERE seguidor_funcionario_id = :seguidor_funcionario_id OR seguidor_id = :seguidor_id');
$stmt->execute([
    'seguidor_funcionario_id' => $viewing_user_id,
    'seguidor_id' => $viewing_user_id
]);
$total_seguidos = $stmt->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="CSS/profile.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Perfil do Funcionário</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
            <li class="nav-item"><a class="nav-link" href="mensagens.php">Mensagens</a></li>
            <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <a href="profile_funcionario.php?funcionario_id=<?php echo htmlspecialchars($viewing_user_id); ?>">
                        <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
                             <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Foto de perfil" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                        <?php else: ?>
                             <img src="default-profile.png" alt="Foto de perfil padrão" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                        <?php endif; ?>
                    </a>

                    <p class="card-text"><?php echo htmlspecialchars($user['nome_usuario']); ?></p>
                    <p class="card-text">Seguidores: <?php echo $total_seguidores; ?></p>
                    <p class="card-text">Seguindo: <?php echo $total_seguidos; ?></p>

                    <!-- Botão de seguir/parar de seguir -->
                    <?php if (!$is_own_profile): ?>
                        <div class="follow-button">
                            <form action="<?php echo $is_following ? 'parar_de_seguir.php' : 'seguir.php'; ?>" method="POST">
                                <input type="hidden" name="seguido_funcionario_id" value="<?php echo $viewing_user_id; ?>">
                                <button type="submit" class="btn <?php echo $is_following ? 'btn-danger' : 'btn-success'; ?>">
                                    <?php echo $is_following ? 'Deixar de Seguir' : 'Seguir'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (!$is_own_profile): ?>
                        <a href="mensagens.php?user_id=<?php echo $viewing_user_id; ?>" class="btn btn-info">Enviar Mensagem</a>
                    <?php else: ?>
                         <a href="mensagens.php" class="btn btn-info">Ver Mensagens</a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <script>
                        // Verifica o valor de is_following no frontend para depuração
                        var isFollowing = <?php echo $is_following ? 'true' : 'false'; ?>;
                        console.log("Estado de seguir:", isFollowing);
                        if (!isFollowing) {
                            console.warn("O botão não está mudando para 'Deixar de Seguir' porque is_following é false.");
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
