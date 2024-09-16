<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">SearchJob</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item"><a class="nav-link" href="profile.php">Perfil</a></li>
                <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="vagas.php">Vagas</a></li>
                <li class="nav-item"><a class="nav-link" href="mensagens.php">Mensagens</a></li>
                <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="registro.php">Registrar</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container mt-4">
