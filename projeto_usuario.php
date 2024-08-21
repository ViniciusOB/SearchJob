<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ínicio</title>
    <link rel="stylesheet" href="feed.css">
</head>
<body>
<?php
session_start();

include 'conexao.php';
include 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query('SELECT projetos.id_projeto, projetos.empresa_id, empresas.ID_empresas, empresas.nome_empresa, empresas.profile_pic, projetos.nome_projeto, projetos.descricao, projetos.imagem_capa, projetos.nivel_especialidade, projetos.data_criacao
                     FROM projetos 
                     JOIN empresas ON projetos.empresa_id = empresas.ID_empresas 
                     ORDER BY projetos.data_criacao DESC');

while ($row = $stmt->fetch()) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<div class='media'>";

    
    if ($row['profile_pic']) {
        echo "<img src='profile_pics/" . htmlspecialchars($row['profile_pic']) . "' class='mr-3 rounded-circle' alt='Profile Picture' style='width: 50px; height: 50px;'>";
    } else {
        echo "<img src='default-profile.png' class='mr-3 rounded-circle' alt='Default Profile Picture' style='width: 50px; height: 50px;'>";
    }

    echo "<div class='media-body'>";
    echo "<h5 class='mt-0'>" . htmlspecialchars($row['nome_empresa']) . "</h5>";

    
    echo "<h4 class='mt-2'>" . htmlspecialchars($row['nome_projeto']) . "</h4>";

    
    if ($row['imagem_capa']) {
        echo "<img src='capa_projeto/" . htmlspecialchars($row['imagem_capa']) . "' class='img-fluid rounded mb-3' alt='Post Image'>";
    }

    echo "<p class='nivel_especialidade'> Nível de especialidade: " . htmlspecialchars($row['nivel_especialidade']) . "</p>";

    echo "<p>" . htmlspecialchars($row['descricao']) . "</p>";

    echo "<p class='text-muted'><small>" . htmlspecialchars($row['data_criacao']) . "</small></p>";

    echo "</div>"; 
    echo "</div>"; 
    echo "</div>"; 
    echo "</div>"; 
}

include 'views/footer.php';
?>
</body>
</html>
