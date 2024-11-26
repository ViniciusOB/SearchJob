<?php
include 'conexao.php';

session_start();

// Verifica se há um usuário ou funcionário logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    http_response_code(403); 
    exit('Acesso negado');
}

// Verifica o tipo de usuário logado e ajusta as variáveis de sessão e banco de dados
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
    $userId = $_SESSION['user_id'];
} elseif (isset($_SESSION['id_funcionario'])) {
    $userType = 'funcionario';
    $userId = $_SESSION['id_funcionario'];
}

// Define qual tipo de feed carregar (feed padrão ou seguindo)
$feedType = isset($_GET['feedType']) ? $_GET['feedType'] : 'feed';

// Consulta para posts "Para Você" (feed padrão) - Excluindo os posts seguidos
if ($feedType === 'feed') {
    if ($userType === 'cliente') {
        // Exclui posts seguidos apenas se o usuário for cliente
        $stmt = $pdo->prepare('
            SELECT posts.*, 
                   CASE 
                       WHEN posts.author_type = "cliente" THEN usuarios.nome_usuario
                       WHEN posts.author_type = "funcionario" THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
                   END AS nome_autor, 
                   CASE 
                       WHEN posts.author_type = "cliente" THEN usuarios.profile_pic
                       WHEN posts.author_type = "funcionario" THEN funcionarios.profile_pic
                   END AS profile_pic
            FROM posts
            LEFT JOIN usuarios ON posts.user_id = usuarios.id_usuario
            LEFT JOIN funcionarios ON posts.funcionario_id = funcionarios.id_funcionario
            LEFT JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id 
                AND seguidores.seguidor_id = :user_id
            WHERE seguidores.id IS NULL
            ORDER BY posts.created_at DESC
        ');
        $stmt->execute(['user_id' => $userId]);
    } else {
        // Mostra todos os posts para funcionários
        $stmt = $pdo->prepare('
            SELECT posts.*, 
                   CASE 
                       WHEN posts.author_type = "cliente" THEN usuarios.nome_usuario
                       WHEN posts.author_type = "funcionario" THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
                   END AS nome_autor, 
                   CASE 
                       WHEN posts.author_type = "cliente" THEN usuarios.profile_pic
                       WHEN posts.author_type = "funcionario" THEN funcionarios.profile_pic
                   END AS profile_pic
            FROM posts
            LEFT JOIN usuarios ON posts.user_id = usuarios.id_usuario
            LEFT JOIN funcionarios ON posts.funcionario_id = funcionarios.id_funcionario
            ORDER BY posts.created_at DESC
        ');
        $stmt->execute();
    }
} elseif ($feedType === 'seguindo' && $userType === 'cliente') {
    // Consulta para posts dos perfis seguidos apenas pelos clientes logados
    $stmt = $pdo->prepare('
        SELECT posts.*, 
               CASE 
                   WHEN posts.author_type = "cliente" THEN usuarios.nome_usuario
                   WHEN posts.author_type = "funcionario" THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
               END AS nome_autor, 
               CASE 
                   WHEN posts.author_type = "cliente" THEN usuarios.profile_pic
                   WHEN posts.author_type = "funcionario" THEN funcionarios.profile_pic
               END AS profile_pic
        FROM posts 
        LEFT JOIN usuarios ON posts.user_id = usuarios.id_usuario 
        LEFT JOIN funcionarios ON posts.funcionario_id = funcionarios.id_funcionario 
        JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id 
        WHERE seguidores.seguidor_id = :user_id
        ORDER BY posts.created_at DESC
    ');
    $stmt->execute(['user_id' => $userId]);
}

// Exibe os posts
while ($row = $stmt->fetch()) {
    include 'post_template.php'; // Utilize um único template adaptado
}
?>
