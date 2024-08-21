<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pergunta_id = $_POST['pergunta'];
    $resposta = $_POST['resposta'];

    
    $stmt = $pdo->prepare("SELECT rs.resposta
                           FROM respostas_seguranca rs
                           INNER JOIN usuarios u ON rs.usuario_id = u.id_usuario
                           WHERE u.email_usuario = :email
                           AND rs.pergunta_id = :pergunta_id");
    $stmt->execute(['email' => $email, 'pergunta_id' => $pergunta_id]);
    $resposta_correta = $stmt->fetchColumn();

    if ($resposta_correta === $resposta) {
       
        header("Location: nova_senha.php?email=$email");
        exit();
    } else {
        echo "Resposta de segurança incorreta.";
    }
}
?>
