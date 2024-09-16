<?php
session_start(); 
require 'conexao.php'; 
include 'views/header.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id'])) {
    die('Usuário não está logado.'); 
}

$user_id = $_SESSION['user_id']; 

// Consulta para buscar as notificações
$sql = "SELECT n.*, 
               CONCAT(u.nome_usuario, ' ', u.sobrenome_usuario) AS remetente_nome, 
               CONCAT(s.nome_usuario, ' ', s.sobrenome_usuario) AS seguidor_nome, 
               m.conteudo AS mensagem_conteudo
        FROM notificacoes n
        LEFT JOIN usuarios u ON n.remetente_id = u.id_usuario
        LEFT JOIN usuarios s ON n.seguidor_id = s.id_usuario
        LEFT JOIN mensagens m ON n.mensagem_id = m.id
        WHERE n.usuario_id = ?
        ORDER BY n.data_notificacao DESC";

$stmt = $pdo->prepare($sql);

if (!$stmt) {
    die("Erro na preparação da consulta: " . implode(":", $pdo->errorInfo())); // Debug para erros de preparação
}

$stmt->bindParam(1, $user_id, PDO::PARAM_INT); // O ID do usuário logado é dinâmico

try {
    $stmt->execute();
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage()); // Debug para erros de execução
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .container {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
        }
        .notificacao {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        }
        .notificacao.novo {
            background-color: #eaf4fc;
        }
        .notificacao p {
            margin: 0;
            color: #555;
        }
        .notificacao small {
            display: block;
            margin-top: 8px;
            color: #999;
        }
        p.nenhuma-notificacao {
            text-align: center;
            font-size: 18px;
            color: #777;
        }
    </style>
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
$stmt_update->bindParam(1, $user_id, PDO::PARAM_INT); // O ID do usuário logado é dinâmico

try {
    $stmt_update->execute();
} catch (PDOException $e) {
    die("Erro ao marcar notificações como vistas: " . $e->getMessage()); // Debug para erros de atualização
}
?>
