<?php
session_start();
require_once 'conexao.php';

// Ativando a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    // Pegando lista de usuários, exceto admins
    $stmt = $pdo->query("
        SELECT id_usuario, nome_usuario, sobrenome_usuario, email_usuario, tipo, data_registro, descricao, profile_pic 
        FROM usuarios
        WHERE tipo != 'admin'
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao acessar o banco de dados: " . $e->getMessage();
    exit();
}

// Função para enviar o email após a exclusão
function enviarEmailBanimento($email, $motivo) {
    $assunto = "Conta banida no sistema";
    $mensagem = "Sua conta foi banida por causa do seguinte motivo: " . $motivo;
    
    // Configuração de envio de e-mail
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: suporte@searchjob.com" . "\r\n";  // Coloque aqui o email de suporte
    $headers .= "Reply-To: suporte@searchjob.com" . "\r\n";  // Responder para o email de suporte
    $headers .= "X-Mailer: PHP/" . phpversion();

    mail($email, $assunto, $mensagem, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $id_usuario = $_POST['excluir_id'];
    $motivo = $_POST['motivo'];

    // Excluir todas as respostas de segurança associadas ao usuário
    $stmt = $pdo->prepare("DELETE FROM respostas_seguranca WHERE usuario_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir todos os arquivos relacionados ao usuário na tabela arquivos_projetos
    $stmt = $pdo->prepare("DELETE FROM arquivos_projetos WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir todos os seguidores associados ao usuário
    $stmt = $pdo->prepare("DELETE FROM seguidores WHERE seguidor_id = :id_usuario OR seguido_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir todas as mensagens associadas ao usuário
    $stmt = $pdo->prepare("DELETE FROM mensagens WHERE remetente_id = :id_usuario OR destinatario_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir todas as notificações associadas ao usuário
    $stmt = $pdo->prepare("DELETE FROM notificacoes WHERE usuario_id = :id_usuario OR remetente_id = :id_usuario OR seguidor_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir todas as inscrições em projetos associadas ao usuário
    $stmt = $pdo->prepare("DELETE FROM inscricoes WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir os posts do usuário
    $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Excluir os comentários do usuário
    $stmt = $pdo->prepare("DELETE FROM comentarios WHERE usuario_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    // Pegando o email do usuário antes de deletar
    $stmt = $pdo->prepare("SELECT email_usuario FROM usuarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $email = $usuario['email_usuario'];

        // Excluir o usuário
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        if ($stmt->execute(['id_usuario' => $id_usuario])) {
            // Enviando email de banimento
            enviarEmailBanimento($email, $motivo);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Usuários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            background-color: red;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: darkred;
        }
        .profile_pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-voltar {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function confirmarExclusao(id_usuario) {
            var motivo = prompt("Por favor, insira o motivo do banimento:");
            if (motivo) {
                document.getElementById('excluir_id').value = id_usuario;
                document.getElementById('motivo').value = motivo;
                document.getElementById('excluir_form').submit();
            }
        }
    </script>
</head>
<body>

    <h1>Funcionários</h1>

    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Sobrenome</th>
                <th>Email</th>
                <th>Descrição</th>
                <th>Data de Registro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario) { ?>
            <tr>
                <td>
                    <img src="<?php echo $usuario['profile_pic'] ? 'profile_pics/' . $usuario['profile_pic'] : 'default-profile.png'; ?>" 
                         alt="Foto de Perfil" class="profile_pic">
                </td>
                <td><?php echo $usuario['nome_usuario']; ?></td>
                <td><?php echo $usuario['sobrenome_usuario']; ?></td>
                <td><?php echo $usuario['email_usuario']; ?></td>
                <td><?php echo $usuario['descricao']; ?></td>
                <td><?php echo $usuario['data_registro']; ?></td>
                <td>
                    <button onclick="confirmarExclusao(<?php echo $usuario['id_usuario']; ?>)">Excluir</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="dashboard_admin.php" class="btn-voltar">Voltar ao Dashboard</a>
    <!-- Formulário para exclusão com motivo -->
    <form id="excluir_form" method="POST" style="display:none;">
        <input type="hidden" name="excluir_id" id="excluir_id">
        <input type="hidden" name="motivo" id="motivo">
    </form>
</body>
</html>
