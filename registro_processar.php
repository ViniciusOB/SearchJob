<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); 
    $tipo = 'cliente';
    $pergunta_id = $_POST['pergunta'];
    $resposta = $_POST['resposta'];
    $profile_pic = 'profile_pics/default.png'; // Define a imagem padrão

    // Verifica se um arquivo foi enviado e não houve erro
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'profile_pics/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $profile_pic_path = $upload_dir . basename($_FILES['profile_pic']['name']);
        
        // Move o arquivo enviado para o diretório especificado
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_path)) {
            $profile_pic = $profile_pic_path; // Atualiza o caminho da imagem de perfil
        }
    }
   
    // Insere os dados do usuário na tabela 'usuarios'
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome_usuario, sobrenome_usuario, email_usuario, senha_usuario, profile_pic, tipo) VALUES (:nome, :sobrenome, :email, :senha, :profile_pic, :tipo)");
    $stmt->execute([
        'nome' => $nome, 
        'sobrenome' => $sobrenome, 
        'email' => $email, 
        'senha' => $senha, 
        'profile_pic' => $profile_pic, 
        'tipo' => $tipo
    ]);
    $usuario_id = $pdo->lastInsertId();
    
    // Insere a resposta de segurança na tabela 'respostas_seguranca'
    $stmt = $pdo->prepare("INSERT INTO respostas_seguranca (usuario_id, pergunta_id, resposta) VALUES (:usuario_id, :pergunta_id, :resposta)");
    $stmt->execute([
        'usuario_id' => $usuario_id, 
        'pergunta_id' => $pergunta_id, 
        'resposta' => $resposta
    ]);

    // Redireciona para a página inicial após o cadastro
    header("Location: index.php");
    exit();
}
?>
