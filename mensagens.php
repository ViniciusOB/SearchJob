<?php
session_start();
include 'conexao.php';
include 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$destinatario_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Caso o destinatário não seja informado, exiba a lista de conversas
if (!$destinatario_id) {
    // Obter uma lista de conversas recentes do usuário logado
    $stmt = $pdo->prepare('
        SELECT u.id_usuario, u.nome_usuario, u.profile_pic, MAX(m.data_envio) as ultima_mensagem
        FROM mensagens m
        JOIN usuarios u ON (u.id_usuario = m.remetente_id OR u.id_usuario = m.destinatario_id)
        WHERE (m.remetente_id = :user_id OR m.destinatario_id = :user_id)
        AND u.id_usuario != :user_id
        GROUP BY u.id_usuario
        ORDER BY ultima_mensagem DESC
    ');
    $stmt->execute(['user_id' => $user_id]);
    $conversas = $stmt->fetchAll();
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
                        <a href="mensagens.php?user_id=<?php echo $conversa['id_usuario']; ?>">
                            <img src="<?php echo htmlspecialchars($conversa['profile_pic'] ?: 'default-profile.png'); ?>" alt="Foto de perfil" class="img-thumbnail" style="width: 50px; height: 50px;">
                            <?php echo htmlspecialchars($conversa['nome_usuario']); ?>
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

// Verificar se o usuário está tentando enviar uma nova mensagem
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['conteudo'])) {
    $conteudo = trim($_POST['conteudo']);
    if (!empty($conteudo)) {
        $stmt = $pdo->prepare('INSERT INTO mensagens (remetente_id, destinatario_id, conteudo) VALUES (:remetente_id, :destinatario_id, :conteudo)');
        $stmt->execute([
            'remetente_id' => $user_id,
            'destinatario_id' => $destinatario_id,
            'conteudo' => $conteudo
        ]);

        // Após enviar a mensagem, insere a notificação
        $sql_notificacao = "INSERT INTO notificacoes (tipo, usuario_id, remetente_id, mensagem_id, visto) 
                            VALUES ('mensagem', :usuario_id, :remetente_id, :mensagem_id, 0)";
        $stmt_notificacao = $pdo->prepare($sql_notificacao);
        $stmt_notificacao->bindParam(':usuario_id', $destinatario_id); // Usuário que vai receber a notificação
        $stmt_notificacao->bindParam(':remetente_id', $user_id); // Quem enviou a mensagem
        $stmt_notificacao->bindParam(':mensagem_id', $pdo->lastInsertId()); // ID da mensagem enviada
        $stmt_notificacao->execute();
    }
    header('Location: mensagens.php?user_id=' . $destinatario_id);
    exit();
}

// Obter as mensagens trocadas entre o usuário logado e o destinatário
$stmt = $pdo->prepare('
    SELECT * FROM mensagens 
    WHERE (remetente_id = :user_id AND destinatario_id = :destinatario_id) 
       OR (remetente_id = :destinatario_id AND destinatario_id = :user_id)
    ORDER BY data_envio ASC
');
$stmt->execute([
    'user_id' => $user_id,
    'destinatario_id' => $destinatario_id
]);
$mensagens = $stmt->fetchAll();

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
    </style>
</head>
<body>

<div class="container chat-container mt-4">
    <h2>Chat com <?php echo htmlspecialchars($destinatario['nome_usuario']); ?></h2>

    <div class="chat-box mb-4">
        <?php if ($mensagens): ?>
            <?php foreach ($mensagens as $mensagem): ?>
                <div class="message <?php echo $mensagem['remetente_id'] == $user_id ? 'sent' : 'received'; ?>">
                    <p><?php echo htmlspecialchars($mensagem['conteudo']); ?></p>
                    <small><?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Sem mensagens anteriores.</p>
        <?php endif; ?>
    </div>

    <form action="mensagens.php?user_id=<?php echo $destinatario_id; ?>" method="POST">
        <div class="input-group">
            <input type="text" name="conteudo" class="form-control" placeholder="Digite sua mensagem..." required>
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Enviar</button>
            </div>
        </div>
    </form>
</div>

</body>
</html>
