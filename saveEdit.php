<?php
include_once('conexao.php');

if(isset($_POST['update'])) {
    $id_usuario = $_POST['id_usuario'];
    $nome_usuario = $_POST['nome'];
    $sobrenome_usuario = $_POST['sobrenome'];
    $email_usuario = $_POST['email'];
    $apelido = $_POST['apelido'];

    $sqlUpdate = "UPDATE usuarios 
                  SET nome_usuario=:nome_usuario, 
                      email_usuario=:email_usuario, 
                      sobrenome_usuario=:sobrenome_usuario, 
                      apelido=:apelido
                  WHERE id_usuario=:id_usuario";
    
    $stmt = $pdo->prepare($sqlUpdate);
    
    $stmt->bindParam(':nome_usuario', $nome_usuario, PDO::PARAM_STR);
    $stmt->bindParam(':sobrenome_usuario', $sobrenome_usuario, PDO::PARAM_STR);
    $stmt->bindParam(':email_usuario', $email_usuario, PDO::PARAM_STR);
    $stmt->bindParam(':apelido', $apelido, PDO::PARAM_STR);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        echo "Atualização realizada com sucesso.";
    } else {
        echo "Nenhuma alteração feita.";
    }
}

header('Location: dashboard_admin.php');
?>
