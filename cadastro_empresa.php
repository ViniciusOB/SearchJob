<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_empresa = $_POST['nome_empresa'];
    $email_de_trabalho = $_POST['email_de_trabalho'];
    $senha_empresa = password_hash($_POST['senha_empresa'], PASSWORD_DEFAULT);
    $telefone_empresa = $_POST['telefone_empresa'];

    // Função para processar o upload de arquivos
    function processarUpload($arquivo, $diretorio_base) {
        $nome_arquivo = '';
        if (isset($_FILES[$arquivo]) && $_FILES[$arquivo]['error'] === UPLOAD_ERR_OK) {
            $nome_original = $_FILES[$arquivo]['name'];
            $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
            
            // Sanitiza o nome do arquivo
            $nome_original = preg_replace("/[^a-zA-Z0-9.]/", "_", $nome_original);
            $diretorio = __DIR__ . $diretorio_base;
            
            // Verifica se o diretório existe
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true); // Cria o diretório se não existir
            }
            
            if (move_uploaded_file($_FILES[$arquivo]['tmp_name'], $diretorio . $nome_original)) {
                $nome_arquivo = $nome_original;
            } else {
                global $msg;
                $msg = "Falha ao enviar o arquivo $arquivo.";
            }
        } else {
            global $msg;
            $msg = "Nenhum arquivo enviado ou erro no envio de $arquivo.";
        }
        return $nome_arquivo;
    }

    // Processar upload do banner da empresa e da foto de perfil
    $banner_empresa = processarUpload('banner_empresa', '/banner_empresa/');
    $profile_pic = processarUpload('profile_pic', '/profile_pics/');

    // Inserir novo registro na tabela de empresas
    if ($banner_empresa && $profile_pic) {
        $stmt = $pdo->prepare("INSERT INTO empresas (nome_empresa, email_de_trabalho, senha_empresa, telefone_empresa, profile_pic, banner_empresa, tipo) 
                               VALUES (:nome_empresa, :email_de_trabalho, :senha_empresa, :telefone_empresa, :profile_pic, :banner_empresa, :tipo)");
        $stmt->execute([
            'nome_empresa' => $nome_empresa,
            'email_de_trabalho' => $email_de_trabalho,
            'senha_empresa' => $senha_empresa,
            'telefone_empresa' => $telefone_empresa,
            'profile_pic' => $profile_pic,
            'banner_empresa' => $banner_empresa,
            'tipo' => 'empresa'
        ]);

        header("Location: index.php");
        exit();
    } else {
        $msg = "Erro ao processar o upload dos arquivos.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Empresa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/index.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="brand">
            <a href="../todos/home.php" style="background-image: url('Img/searchIcon.png');">JOB</a>
        </div>
        <ul class="nav-menu">
            <li><a href="../todos/home.php">Home</a></li>
            <li><a href="#">Sobre</a></li>
            <li><a href="#">Serviços</a></li>
            <li><a href="../todos/contato.php">Contato</a></li>
            <li><a href="index.php">Login</a></li>
        </ul>
    </div>
</nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Cadastro de Empresa
                </div>
                <div class="card-body">
                    <form action="cadastro_empresa.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nome_empresa">Nome da empresa:</label>
                            <input type="text" name="nome_empresa" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email_de_trabalho">Email corporativo:</label>
                            <input type="email" name="email_de_trabalho" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="senha_empresa">Senha:</label>
                            <input type="password" name="senha_empresa" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone_empresa">Telefone para contato:</label>
                            <input type="text" name="telefone_empresa" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="profile_pic">Foto de perfil:</label>
                            <input type="file" name="profile_pic" id="profile_pic" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="banner_empresa">Banner da empresa:</label>
                            <input type="file" name="banner_empresa" class="form-control" required>
                        </div>
                        <button type="submit" name="cadastrar" class="btn btn-primary">Cadastrar</button>
                        <a href="index.php" class="btn btn-secondary">Voltar</a>
                    </form>
                    <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
