<?php
session_start();

include 'conexao.php';  // Conexão com o banco de dados

$config = include 'config.php';  // Carrega a configuração segura

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Função para sanitizar os dados de entrada
    function sanitize_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Sanitiza os campos do formulário
    $name = sanitize_input($_POST['name']);
    $email = filter_var(sanitize_input($_POST['email']), FILTER_VALIDATE_EMAIL);  // Valida o e-mail
    $message = sanitize_input($_POST['message']);

    if (!$email) {
        die("Erro: E-mail inválido.");
    }

    // Configurações do e-mail
    $to = $config['support_email'];  // E-mail de suporte carregado de forma segura
    $subject = "Contato: " . $name;

    // Cabeçalhos do e-mail
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@searchjob.com" . "\r\n";  // Certifique-se de usar um e-mail válido no seu domínio
    $headers .= "Reply-To: " . $email . "\r\n";  // E-mail do usuário/empresa que foi inserido manualmente
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Corpo do e-mail
    $fullMessage = "<html><body>";
    $fullMessage .= "<h2>Mensagem de Contato</h2>";
    $fullMessage .= "<p><strong>Nome:</strong> " . $name . "</p>";
    $fullMessage .= "<p><strong>Email:</strong> " . $email . "</p>";
    $fullMessage .= "<p><strong>Mensagem:</strong><br>" . nl2br($message) . "</p>";
    $fullMessage .= "</body></html>";

    // Envia o e-mail
    if (mail($to, $subject, $fullMessage, $headers)) {
        // Redireciona após o envio bem-sucedido
        header("Location: index.php");
        exit;
    } else {
        echo "Ocorreu um erro ao enviar o e-mail.";
    }
} else {
    // Exibe o formulário caso o método não seja POST
    ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato</title>
    <link rel="stylesheet" href="CSS/contato.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="feed.php">SearchJob</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <?php 
            // Verifica se o usuário ou funcionário está logado
            if (isset($_SESSION['user_id'])): ?>
                <!-- Menu para Usuário -->
                <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
                <li class="nav-item"><a class="nav-link" href="feed_projetos.php">Projetos</a></li>
                <li class="nav-item"><a class="nav-link" href="mensagens.php">Mensagens</a></li>
                <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Perfil</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container mt-4">

    <div class="form-container">
        <h2>Contato</h2>
        <form id="contactForm" action="contato.php" method="post" onsubmit="return validateForm();">
            <label for="name">Nome</label>
            <input type="text" id="name" name="name" placeholder="Digite seu nome" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>

            <label for="message">Mensagem</label>
            <textarea id="message" name="message" rows="4" placeholder="Digite sua mensagem" required></textarea>

            <button type="submit">Enviar</button>
        </form>
    </div>
</body>
</html>

    <script>
        // Validação simples para garantir que nome e mensagem não estão vazios
        function validateForm() {
            let name = document.getElementById('name').value;
            let email = document.getElementById('email').value;
            let message = document.getElementById('message').value;

            if (name.trim() === "") {
                alert("Por favor, insira seu nome.");
                return false;
            }

            if (email.trim() === "" || !validateEmail(email)) {
                alert("Por favor, insira um e-mail válido.");
                return false;
            }

            if (message.trim() === "") {
                alert("Por favor, insira sua mensagem.");
                return false;
            }

            return true; 
        }

        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
    </script>

<?php
}
?>
