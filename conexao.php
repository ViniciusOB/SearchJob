<?php
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

if(isset($_POST['submit_usuario'])){
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome_usuario, sobrenome_usuario, email_usuario, senha_usuario, tipo, data_registro) 
                           VALUES (:nome_usuario, :sobrenome_usuario, :email_usuario, :senha_usuario, :tipo, :data_registro)");

    $nome_usuario = $_POST['nome'];
    $sobrenome_usuario = $_POST['sobrenome'];
    $email_usuario = $_POST['email'];
    $senha_usuario = password_hash($_POST['senha'], PASSWORD_BCRYPT);  // Hash da senha
    $tipo = 'cliente';
    $data_registro = date('Y-m-d H:i:s');

    $stmt->bindParam(':nome_usuario', $nome_usuario);
    $stmt->bindParam(':sobrenome_usuario', $sobrenome_usuario);
    $stmt->bindParam(':email_usuario', $email_usuario);
    $stmt->bindParam(':senha_usuario', $senha_usuario);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':data_registro', $data_registro);

    try {
        $stmt->execute();
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit(); 
    } catch(PDOException $e) {
        die("Erro ao inserir registro: " . $e->getMessage());
    }
}

if(isset($_POST['submit_empresa'])){
    $stmt = $pdo->prepare("INSERT INTO empresas (nome_empresa, email_de_trabalho, senha_empresa, telefone_empresa, banner_empresa, tipo) 
                           VALUES (:nome_empresa, :email_de_trabalho, :senha_empresa, :telefone_empresa, :banner_empresa, :tipo)");

    $nome_empresa = $_POST['nome_empresa'];
    $email_de_trabalho = $_POST['email_de_trabalho'];
    $senha_empresa = password_hash($_POST['senha_empresa'], PASSWORD_BCRYPT);  // Hash da senha
    $telefone_empresa = $_POST['telefone_empresa'];
    $banner_empresa = $_POST['banner_empresa'];
    $tipo = 'empresa';

    $stmt->bindParam(':nome_empresa', $nome_empresa);
    $stmt->bindParam(':email_de_trabalho', $email_de_trabalho);
    $stmt->bindParam(':senha_empresa', $senha_empresa);
    $stmt->bindParam(':telefone_empresa', $telefone_empresa);
    $stmt->bindParam(':banner_empresa', $banner_empresa);
    $stmt->bindParam(':tipo', $tipo);

    try {
        $stmt->execute();
        echo "Empresa inserida com sucesso!";
    } catch(PDOException $e) {
        echo "Erro ao inserir empresa: " . $e->getMessage();
    }
}
?>