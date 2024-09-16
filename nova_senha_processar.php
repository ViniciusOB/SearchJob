<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirm_password) {
        echo "As senhas não coincidem.";
        exit();
    }

    // Hash da nova senha
    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    // Atualizar a senha do usuário no banco de dados
    $stmt = $pdo->prepare("UPDATE usuarios SET senha_usuario = :senha WHERE email_usuario = :email");
    $stmt->execute(['senha' => $hashed_password, 'email' => $email]);

    // Redirecionar para a página de login após a atualização bem-sucedida da senha
    header("Location: index.php");
    exit();
}
?>
