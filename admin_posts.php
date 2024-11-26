<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    // Pegar os posts e os respectivos comentários
    $stmtPosts = $pdo->query("
        SELECT p.*, 
               COALESCE(u.nome_usuario, f.nome_funcionario, e.nome_empresa) AS autor, 
               COALESCE(u.profile_pic, f.profile_pic, e.profile_pic) AS foto_autor 
        FROM posts p
        LEFT JOIN usuarios u ON p.user_id = u.id_usuario
        LEFT JOIN funcionarios f ON p.funcionario_id = f.id_funcionario
        LEFT JOIN empresas e ON p.ID_empresas = e.ID_empresas
        ORDER BY p.created_at DESC
    ");
    $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao acessar o banco de dados: " . $e->getMessage();
}

// Lógica para excluir o post
if (isset($_POST['excluir_post'])) {
    $post_id = $_POST['post_id'];
    try {
        $stmtDeletePost = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
        $stmtDeletePost->bindParam(':post_id', $post_id);
        $stmtDeletePost->execute();
        header("Location: admin_posts.php");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao excluir o post: " . $e->getMessage();
    }
}

// Lógica para excluir o comentário
if (isset($_POST['excluir_comentario'])) {
    $comentario_id = $_POST['comentario_id'];
    try {
        $stmtDeleteComentario = $pdo->prepare("DELETE FROM comentarios WHERE id = :comentario_id");
        $stmtDeleteComentario->bindParam(':comentario_id', $comentario_id);
        $stmtDeleteComentario->execute();
        header("Location: admin_posts.php");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao excluir o comentário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .post-container {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .post-header {
            display: flex;
            align-items: center;
        }
        .post-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .post-content {
            margin-top: 10px;
            font-size: 1.1rem;
        }
        .comentarios {
            margin-top: 20px;
            padding-left: 20px;
            border-left: 2px solid #ddd;
        }
        .comentario {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
        }
        .comentario span {
            font-weight: bold;
        }
        button {
            background-color: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background-color: darkred;
        }

        /* Botão de Voltar */
        .btn-voltar-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-voltar {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }
        
    </style>
</head>
<body>
    <h1>Gerenciar Posts e Comentários</h1>

    <?php foreach ($posts as $post) { ?>
    <div class="post-container">
        <div class="post-header">
            <img src="<?php echo $post['foto_autor'] ? 'profile_pics/' . $post['foto_autor'] : 'default-profile.png'; ?>" alt="Foto do Autor">
            <h3><?php echo $post['autor']; ?></h3>
        </div>
        <p class="post-content"><?php echo $post['content']; ?></p>

        <!-- Formulário para excluir post -->
        <form method="POST" action="admin_posts.php" onsubmit="return confirm('Tem certeza que deseja excluir este post?');">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <button type="submit" name="excluir_post">Excluir Post</button>
        </form>

        <!-- Comentários relacionados ao post -->
        <?php
        $stmtComentarios = $pdo->prepare("SELECT * FROM comentarios WHERE post_id = :post_id");
        $stmtComentarios->bindParam(':post_id', $post['id']);
        $stmtComentarios->execute();
        $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if ($comentarios) { ?>
        <div class="comentarios">
            <h4>Comentários:</h4>
            <?php foreach ($comentarios as $comentario) { ?>
            <div class="comentario">
                <p><?php echo $comentario['conteudo']; ?></p>
                <form method="POST" action="admin_posts.php" onsubmit="return confirm('Tem certeza que deseja excluir este comentário?');">
                    <input type="hidden" name="comentario_id" value="<?php echo $comentario['id']; ?>">
                    <button type="submit" name="excluir_comentario">Excluir Comentário</button>
                </form>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="btn-voltar-container">
        <a href="dashboard_admin.php" class="btn-voltar">Voltar ao Dashboard</a>
    </div>

</body>
</html>
