<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <a href="home.php" style="background-image: url('Img/searchIcon.png');">JOB</a>
            </div>
            <ul class="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="#">Sobre</a></li>
                <li><a href="#">Servi&ccedil;os</a></li>
                <li><a href="#">Contato</a></li>
                <li><a href="index.php">Login</a></li>
            </ul>
        </div>
    </nav>
    <video autoplay muted loop class="video-bg-login">
        <source src="Img/background-login.mp4" type="video/mp4">
        Seu navegador não suporta a tag de vídeo.
    </video>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Login
                    </div>
                    <div class="card-body">
                        <form action="login.php" method="POST">
                            <div class="form-group">
                                <label for="email">Email do Usuário:</label>
                                <input type="text" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="senha">Senha:</label>
                                <input type="senha" name="senha" id="senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                        <div class="mt-3">
                        <a href="registro.php">Registrar novo usuário</a> | <a href="cadastro_empresa.php">Registrar nova empresa</a>
                        </div>
                        <a href="recuperar_senha.php">Esqueci minha senha</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
