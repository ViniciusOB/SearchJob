<?php
if(!empty($_GET['id_usuario'])) {
    include_once('conexao.php');

    
    $id = $_GET['id_usuario'];

    
    $sqlSelect = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario";
    $stmtSelect = $conexao->prepare($sqlSelect);
    $stmtSelect->bindParam(':id_usuario', $id, PDO::PARAM_INT);
    $stmtSelect->execute();
    $resultSelect = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

   
    if($resultSelect) {
       
        $sqlDelete = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";
        $stmtDelete = $conexao->prepare($sqlDelete);
        $stmtDelete->bindParam(':id_usuario', $id, PDO::PARAM_INT);
        $stmtDelete->execute();
    }
}

header('Location: dashboard_admin.php');
exit();
?>
