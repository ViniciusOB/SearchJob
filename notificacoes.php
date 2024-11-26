<?php
session_start();
include 'conexao.php';
include 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para buscar as notificações
$sql = "SELECT n.*, 
               u.nome_usuario AS remetente_nome, 
               s.nome_usuario AS seguidor_nome, 
               m.conteudo AS mensagem_conteudo
        FROM notificacoes n
        LEFT JOIN usuarios u ON n.remetente_id = u.id_usuario
        LEFT JOIN usuarios s ON n.seguidor_id = s.id_usuario
        LEFT JOIN mensagens m ON n.mensagem_id = m.id
        WHERE n.usuario_id = :usuario_id
        ORDER BY n.data_notificacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $user_id]);
$notificacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <title>Notificações</title>
    <link rel="stylesheet" href="CSS/notificacoes.css">
    
</head>
<body>

<h1>Suas Notificações</h1>
<div class="container">
    <?php
    if (empty($notificacoes)) {
        echo '<p class="nenhuma-notificacao">Nenhuma notificação encontrada.</p>';
    } else {
        foreach ($notificacoes as $notificacao) {
            ?>
            <div class="notificacao <?= !$notificacao['visto'] ? 'novo' : ''; ?>">
                <?php if ($notificacao['tipo'] == 'mensagem'): ?>
                    <p><strong><?= htmlspecialchars($notificacao['remetente_nome']); ?></strong> enviou uma mensagem: <?= htmlspecialchars($notificacao['mensagem_conteudo']); ?></p>
                <?php elseif ($notificacao['tipo'] == 'seguindo'): ?>
                    <p><strong><?= htmlspecialchars($notificacao['seguidor_nome']); ?></strong> começou a seguir você.</p>
                <?php endif; ?>
                <small><?= date('d/m/Y H:i', strtotime($notificacao['data_notificacao'])); ?></small>
            </div>
            <?php
        }
    }
    ?>
</div>

</body>
</html>

<?php
// Marca todas as notificações como vistas após a visualização
$sql_update = "UPDATE notificacoes SET visto = 1 WHERE usuario_id = ?";
$stmt_update = $pdo->prepare($sql_update);

try {
    $stmt_update->execute([$user_id]);
} catch (PDOException $e) {
    die("Erro ao marcar notificações como vistas: " . $e->getMessage());
}
?>
