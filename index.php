<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/index.css">
</head>
<body>

<nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <a href="home.php" style="background-image: url('Img/searchIcon.png');">JOB</a>
            </div>
            <ul class="nav-menu">
                <li><a href="todos/home.php" class="nav-link">Home</a></li>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Cadastrar-se</a>
                    <ul class="dropdown-menu">
                        <li><a href="cadastro_empresa.php" class="dropdown-item">Corporativo</a></li>
                        <li><a href="registro.php" class="dropdown-item">Pessoal</a></li>
                    </ul>
                </li>
                <li><a href="index.php" class="nav-link">Login</a></li>
            </ul>
        </div>
    </nav>

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
                        <a href="recuperar_senha.php">Esqueci minha senha</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Script para alternar a exibição do dropdown ao clicar
        document.querySelector('.dropdown-toggle').addEventListener('click', function(event) {
            event.preventDefault();
            this.parentElement.classList.toggle('show');
        });

        // Fechar o dropdown se clicar fora dele
        document.addEventListener('click', function(event) {
            var dropdown = document.querySelector('.dropdown');
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

    
    </script>
</body>
</html>
