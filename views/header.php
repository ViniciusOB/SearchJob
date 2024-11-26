<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="CSS/header.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="feed.php">SearchJob</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <?php 
            // Verifica se o usuário ou funcionário está logado
            if (isset($_SESSION['user_id'])): ?>
                <!-- Menu para Usuário -->
                <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="feed_projetos.php">Projetos</a></li>
                <li class="nav-item"><a class="nav-link" href="mensagens.php">Mensagens</a></li>
                <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Perfil</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php elseif (isset($_SESSION['id_funcionario'])): ?>
                <!-- Menu para Funcionário -->
                <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="projeto_empresa.php">Criar Projetos</a></li>
                <li class="nav-item"><a class="nav-link" href="enviar_relatorio.php">Relatar Produtividade</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php else: ?>
                <!-- Menu para Visitantes (não logados) -->
                <li class="nav-item"><a class="nav-link" href="registro.php">Registrar</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container mt-4">
