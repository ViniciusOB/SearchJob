<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Nova Senha
                    </div>
                    <div class="card-body">
                        <form action="nova_senha_processar.php" method="POST">
                            <div class="form-group">
                                <label for="senha">Nova Senha:</label>
                                <input type="senha" name="senha" id="senha" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nova Senha:</label>
                                <input type="senha" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
                            <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
                            <a class="btn btn-danger" href="index.php">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
