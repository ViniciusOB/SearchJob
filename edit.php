<?php
include_once('conexao.php');

if(!empty($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    $sqlSelect = "SELECT * FROM usuarios WHERE id_usuario=:id_usuario";
    $stmt = $pdo->prepare($sqlSelect);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user_data) {
        $nome_usuario = $user_data['nome_usuario']; 
        $sobrenome_usuario = $user_data['sobrenome_usuario']; 
        $email_usuario = $user_data['email_usuario']; 
        $apelido = $user_data['apelido']; 
    } else {
        header('Location: dashboard_admin.php');
        exit; 
    }
} else {
    header('Location: dashboard_admin.php');
    exit; 
}
   
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="edit.css">
    <title>Formulário</title>
</head>
<body>
    <div class="box">
        <form action="saveEdit.php" method="POST">
            <fieldset>
                <legend><b>Editar Cliente</b></legend>
                <br>
                <div class="inputBox">
                    <input type="text" name="nome" id="nome" class="inputUser" value="<?php echo $nome_usuario; ?>">
                    <label for="nome" class="labelInput">Nome completo</label>
                </div>
                <br><br>
                <div class="inputBox">
                    <input type="text" name="sobrenome" id="sobrenome" class="inputUser" value="<?php echo $sobrenome_usuario; ?>">
                    <label for="sobrenome" class="labelInput">Sobrenome</label>
                </div>
                <br><br>
                <div class="inputBox">
                    <input type="text" name="email" id="email" class="inputUser" value="<?php echo $email_usuario; ?>"> 
                    <label for="email" class="labelInput">Email</label>
                </div>
                <br><br>
                <div class="inputBox">
                    <input type="text" name="apelido" id="apelido" class="inputUser" value="<?php echo $apelido; ?>">
                    <label for="apelido" class="labelInput">Apelido</label>
                </div>
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                <input type="submit" name="update" id="submit">
                <a href="dashboard_admin.php">Voltar</a>
            </fieldset>
        </form>
    </div>
</body>
</html>
