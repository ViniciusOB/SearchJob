<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="recuperar_senha.css">
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Recuperar Senha
                    </div>
                    <div class="card-body">
                        <form action="recuperar_senha_processar.php" method="POST">
                            <div class="form-group">
                                <label for="email">Email do Usuário:</label>
                                <input type="text" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="pergunta">Pergunta de Segurança:</label>
                                <select name="pergunta" id="pergunta" class="form-control" required>
                                    <option value="">Selecione uma pergunta</option>
                                    <option value="1">Qual é o nome do seu animal de estimação?</option>
                                    <option value="2">Qual é a sua cidade natal?</option>
                                    <option value="3">Qual é o nome do seu melhor amigo?</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="resposta">Resposta:</label>
                                <input type="text" name="resposta" id="resposta" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Recuperar Senha</button>
                            <a class="btn btn-danger" href="index.php">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
