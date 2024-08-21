<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirm_password = $_POST['confirm_password'];

   
    if ($senha !== $confirm_password) {
        echo "As senhas não coincidem.";
        exit();
    }

    
    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    
    $stmt = $pdo->prepare("UPDATE usuarios SET senha_usuario = :senha WHERE email_usuario = :email");
    $stmt->execute(['senha' => $hashed_password, 'email' => $email]);

   
    header("Location: index.php");
    exit();
}
?>
