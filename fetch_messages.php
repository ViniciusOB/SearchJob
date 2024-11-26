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

if (!$destinatario_id) {
    exit('Destinatário não especificado');
}

// Busca mensagens trocadas entre os dois usuários
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

// Verifica se encontrou mensagens
if (!$mensagens) {
    echo json_encode(['erro' => 'Nenhuma mensagem encontrada.']);
} else {
    // Retorna as mensagens em formato JSON
    echo json_encode($mensagens);
}
?>
