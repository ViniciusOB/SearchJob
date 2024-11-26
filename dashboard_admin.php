<?php
require_once 'conexao.php';

// Verifica se o admin está logado
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redireciona para o login se o admin não estiver logado
    exit();
}

$id_usuario = $_SESSION['user_id']; // A sessão já deve ser iniciada após o login
$query_admin = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario AND tipo = 'admin'";
$stmt = $pdo->prepare($query_admin);
$stmt->execute(['id_usuario' => $id_usuario]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    exit("Erro: Admin não encontrado.");
}

// Variável para mensagem de erro
$error_message = '';
$success_message = '';

// Processa o formulário de verificação de e-mail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Verifica se o e-mail já existe nas tabelas 'usuarios' ou 'empresas'
    $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM usuarios WHERE email_usuario = :email
    UNION ALL
    SELECT COUNT(*) FROM empresas WHERE email_de_trabalho = :email
    UNION ALL
    SELECT COUNT(*) FROM funcionarios WHERE email_funcionario = :email
    ");
    $stmt->execute([
        'email' => $email,
        'id_usuario' => $id_usuario
    ]);

    // Soma todos os resultados retornados
    $emailExists = array_sum($stmt->fetchAll(PDO::FETCH_COLUMN));

    // Se o email já estiver cadastrado, exibe a mensagem de erro
    if ($emailExists > 0) {
        $error_message = "O email já está em uso. Por favor, use outro email.";
    } else {
        // Atualiza o e-mail no banco de dados
        $stmt = $pdo->prepare("UPDATE usuarios SET email_usuario = :email WHERE id_usuario = :id_usuario");
        $stmt->execute([
            'email' => $email,
            'id_usuario' => $id_usuario
        ]);

        $success_message = "Informações atualizadas com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="CSS/admin.css">
</head>
<body>

    <div class="profile">
        <p><?php echo $admin['nome_usuario']; ?></p>
    </div>

    <!-- Exibe a mensagem de erro, se houver -->
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Exibe a mensagem de sucesso, se houver -->
    <?php if (!empty($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <div class="container">
        <div class="box" onclick="window.location.href='admin_usuarios.php'">Usuários</div>
        <div class="box" onclick="window.location.href='admin_empresa.php'">Empresas</div>
        <div class="box" onclick="window.location.href='admin_posts.php'">Posts</div>
        <div class="box" onclick="window.location.href='admin_mensagens.php'">Mensagens</div>
    </div>

    <div style="text-align: center;">
        <br><br><br>
        <a class="logout" href="logout.php">Logout</a>
    </div>

</body>
</html>
