<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['id_projeto'])) {
    echo 'Erro: Dados inválidos.';
    exit();
}

$user_id = $_SESSION['user_id'];
$id_projeto = $_POST['id_projeto'];

// Verificar se o usuário já está inscrito no projeto
$sql = "SELECT * FROM inscricoes WHERE id_usuario = :user_id AND id_projeto = :id_projeto";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id, 'id_projeto' => $id_projeto]);

if ($stmt->rowCount() > 0) {
    echo 'Você já está inscrito neste projeto.';
} else {
    // Inserir a inscrição na tabela
    $sql = "INSERT INTO inscricoes (id_usuario, id_projeto) VALUES (:user_id, :id_projeto)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'id_projeto' => $id_projeto]);
    echo 'Inscrição realizada com sucesso!';
}
?>
