<?php
session_start(); 


include 'conexao.php';  



if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    die("Erro: usuário não autenticado."); 
}

// Função para buscar o email do usuário com base no ID
function getUserEmail($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT email_usuario FROM usuarios WHERE id_usuario = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['email_usuario'] ?? null;  
    } catch(PDOException $e) {
        die("Erro ao buscar o email do usuário: " . $e->getMessage());
    }
}

$email = getUserEmail($pdo, $userId);  

if (!$email) {
    die("Erro: email do usuário não encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Função para sanitizar os dados de entrada
    function sanitize_input($data) {
        $data = trim($data); // Remove espaços em branco do início e do fim
        $data = stripslashes($data); // Remove barras invertidas
        $data = htmlspecialchars($data); // Converte caracteres especiais para evitar execução de scripts
        return $data;
    }

    // Sanitiza os campos do formulário
    $name = sanitize_input($_POST['name']);
    $message = sanitize_input($_POST['message']);

   
    $to = $supportEmail;  // Email de suporte do arquivo de configuração
    $subject = "Contato: " . $name;

   
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: contato@seudominio.com\r\n";  // Altere para um email do mesmo domínio (ex: contato@seudominio.com)
    $headers .= "Reply-To: " . $email . "\r\n";  // Email de resposta é o do usuário logado
    $headers .= "Return-Path: contato@seudominio.com\r\n";  // O mesmo email para garantir validade
    $headers .= "X-Mailer: PHP/" . phpversion();

   
    $fullMessage = "<html><body>";
    $fullMessage .= "<h2>Mensagem de Contato</h2>";
    $fullMessage .= "<p><strong>Nome:</strong> " . $name . "</p>";
    $fullMessage .= "<p><strong>Email:</strong> " . $email . "</p>";
    $fullMessage .= "<p><strong>Mensagem:</strong><br>" . nl2br($message) . "</p>";
    $fullMessage .= "</body></html>";

   
    if (mail($to, $subject, $fullMessage, $headers)) {
        // Redireciona o usuário para a home (página inicial) após o envio bem-sucedido do email
        header("Location: home.php");  // Altere para a página inicial do seu site
        exit;  // Certifique-se de sair do script após o redirecionamento
    } else {
        echo "Ocorreu um erro ao enviar o email.";
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
</head>
<body>
    <div class="form-container">
        <h2>Contato</h2>
        <form id="contactForm" action="contato.php" method="post" onsubmit="return validateForm();">
            <label for="name">Nome</label>
            <input type="text" id="name" name="name" placeholder="Digite seu nome" required>

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
            let message = document.getElementById('message').value;

            if (name.trim() === "") {
                alert("Por favor, insira seu nome.");
                return false;
            }

            if (message.trim() === "") {
                alert("Por favor, insira sua mensagem.");
                return false;
            }

            return true; 
        }
    </script>

<?php
}
?>
