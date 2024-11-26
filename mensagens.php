<?php
session_start();
include 'conexao.php';
include 'views/header.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Obter o destinatário da URL
$destinatario_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Exibir lista de conversas se nenhum destinatário for especificado
if (!$destinatario_id) {
    // Consulta para obter as conversas
    $stmt = $pdo->prepare('
        SELECT u.id_usuario AS id, u.nome_usuario AS nome, u.profile_pic, MAX(m.data_envio) as ultima_mensagem
        FROM mensagens m
        LEFT JOIN usuarios u ON (u.id_usuario = m.remetente_id OR u.id_usuario = m.destinatario_id)
        WHERE (m.remetente_id = :user_id OR m.destinatario_id = :user_id)
        AND u.id_usuario <> :user_id -- Evitar conversas com o próprio usuário
        GROUP BY u.id_usuario, u.nome_usuario, u.profile_pic
        ORDER BY ultima_mensagem DESC
    ');
    $stmt->execute(['user_id' => $user_id]);
    $conversas = $stmt->fetchAll();

    // Exibir a lista de conversas
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mensagens</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>
    <body>
    <div class="container mt-4">
        <h2>Suas Conversas</h2>
        <ul class="list-group">
            <?php if ($conversas): ?>
                <?php foreach ($conversas as $conversa): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="mensagens.php?user_id=<?php echo $conversa['id']; ?>">
                            <img src="<?php echo htmlspecialchars($conversa['profile_pic'] ?: 'default-profile.png'); ?>" alt="Foto de perfil" class="img-thumbnail" style="width: 50px; height: 50px;">
                            <?php echo htmlspecialchars($conversa['nome']); ?>
                        </a>
                        <span class="badge badge-primary badge-pill"><?php echo date('d/m/Y H:i', strtotime($conversa['ultima_mensagem'])); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Você ainda não iniciou nenhuma conversa.</p>
            <?php endif; ?>
        </ul>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Verificar se a mensagem inicial está presente e o destinatário é válido
if ($destinatario_id && isset($_GET['message'])) {
    $mensagemInicial = trim($_GET['message']);
    if (!empty($mensagemInicial)) {
        // Inserir a mensagem inicial no banco de dados
        $stmt = $pdo->prepare('INSERT INTO mensagens (remetente_id, destinatario_id, conteudo, data_envio) VALUES (:remetente_id, :destinatario_id, :conteudo, NOW())');
        $stmt->execute([
            'remetente_id' => $user_id,
            'destinatario_id' => $destinatario_id,
            'conteudo' => $mensagemInicial
        ]);
    }

    // Redirecionar sem a mensagem na URL para evitar reenvios
    header("Location: mensagens.php?user_id=$destinatario_id");
    exit();
}


// Impedir criação de conversa consigo mesmo
if ($destinatario_id == $user_id) {
    echo "<p>Você não pode iniciar uma conversa consigo mesmo.</p>";
    exit();
}

// Obter o nome do destinatário
$stmt = $pdo->prepare('SELECT nome_usuario FROM usuarios WHERE id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $destinatario_id]);
$destinatario = $stmt->fetch();

if (!$destinatario) {
    echo "Usuário não encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .chat-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .message {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .message.sent {
            background-color: #d1e7dd;
            text-align: right;
        }
        .message.received {
            background-color: #f8d7da;
            text-align: left;
        }
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="container chat-container mt-4">
    <h2>Chat com <?php echo htmlspecialchars($destinatario['nome_usuario']); ?></h2>

    <div class="chat-box mb-4" id="chat-box">
        <!-- As mensagens serão carregadas aqui pelo JavaScript -->
    </div>

    <form id="message-form">
        <div class="input-group">
            <input type="text" name="conteudo" id="conteudo" class="form-control" placeholder="Digite sua mensagem..." required>
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Enviar</button>
            </div>
        </div>
    </form>
</div>

<script>
// Função para buscar mensagens
function fetchMessages() {
    const userId = <?php echo $destinatario_id; ?>;

    fetch(`fetch_messages.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = ''; // Limpa as mensagens anteriores

            // Adiciona cada mensagem ao chat
            data.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message ' + (msg.remetente_id == <?php echo $user_id; ?> ? 'sent' : 'received');
                messageDiv.innerHTML = `
                    <p>${msg.conteudo}</p>
                    <small>${new Date(msg.data_envio).toLocaleString()}</small>
                `;
                chatBox.appendChild(messageDiv);
            });

            // Rolagem automática para a última mensagem
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(error => console.error('Erro ao buscar mensagens:', error));
}

// Função para enviar uma nova mensagem
document.getElementById('message-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const conteudo = document.getElementById('conteudo').value.trim();
    if (conteudo) {
        const formData = new FormData();
        formData.append('conteudo', conteudo);

        fetch(`send_message.php?user_id=<?php echo $destinatario_id; ?>`, {
            method: 'POST',
            body: formData
        })
        .then(() => {
            document.getElementById('conteudo').value = ''; // Limpa o campo de texto
            fetchMessages(); // Atualiza as mensagens
        })
        .catch(error => console.error('Erro ao enviar mensagem:', error));
    }
});

// Recarregar mensagens a cada 2 segundos
setInterval(fetchMessages, 2000);
fetchMessages(); // Carrega as mensagens ao abrir a página
</script>
</body>
</html>