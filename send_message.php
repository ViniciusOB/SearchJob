<?php
session_start();
include 'conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    exit('Acesso negado');
}

$user_id = $_SESSION['user_id'];
$destinatario_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$conteudo = isset($_POST['conteudo']) ? trim($_POST['conteudo']) : '';

if (!$destinatario_id || empty($conteudo)) {
    exit('Dados inválidos');
}

// Inserir nova mensagem no banco de dados
$stmt = $pdo->prepare('INSERT INTO mensagens (remetente_id, destinatario_id, conteudo) 
                       VALUES (:remetente_id, :destinatario_id, :conteudo)');
$stmt->execute([
    'remetente_id' => $user_id,
    'destinatario_id' => $destinatario_id,
    'conteudo' => $conteudo
]);

// Verifica se a mensagem foi inserida corretamente
if ($stmt->rowCount() > 0) {
    // Inserir notificação de mensagem enviada
    $sql_notificacao = "INSERT INTO notificacoes (tipo, usuario_id, remetente_id, mensagem_id, visto) 
                        VALUES ('mensagem', :usuario_id, :remetente_id, :mensagem_id, 0)";
    $stmt_notificacao = $pdo->prepare($sql_notificacao);
    $stmt_notificacao->execute([
        'usuario_id' => $destinatario_id,
        'remetente_id' => $user_id,
        'mensagem_id' => $pdo->lastInsertId()
    ]);
} else {
    // Se não inseriu, mostre o erro
    echo "Erro ao enviar mensagem.";
}
?>
