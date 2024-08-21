<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_profile_pic'])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = 'profile_pics/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $profile_pic = $upload_dir . basename($_FILES['new_profile_pic']['name']);
    if (move_uploaded_file($_FILES['new_profile_pic']['tmp_name'], $profile_pic)) {
        $stmt = $pdo->prepare('UPDATE users SET profile_pic = :profile_pic WHERE id_usuario = :id_usuario');
        $stmt->execute(['profile_pic' => $profile_pic, 'id_usuario' => $user_id]);
        $_SESSION['profile_pic'] = $profile_pic; 
        header('Location: profile.php');
        exit();
    } else {
        $error = 'Erro ao fazer upload da foto de perfil.';
    }
}

$stmt = $pdo->prepare('SELECT nome_usuario, profile_pic FROM usuarios WHERE id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Mini Rede Social</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Mini Rede Social</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php">Perfil</a></li>
            <li class="nav-item"><a class="nav-link" href="feed.php">Feed</a></li>
            <li class="nav-item"><a class="nav-link" href="projeto_usuario.php">Projetos</a></li>
            <li class="nav-item"><a class="nav-link" href="vagas.php">Vagas</a></li>
            <li class="nav-item"><a class="nav-link" href="notificacoes.php">Mensagens</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($user['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Foto de perfil" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                    <?php else: ?>
                        <img src="default-profile.png" alt="Foto de perfil padrão" class="img-thumbnail mb-3" style="width: 150px; height: 150px;">
                    <?php endif; ?>
                    <h2 class="card-title">Perfil</h2>
                    <p class="card-text">Bem-vindo, <?php echo htmlspecialchars($user['nome_usuario']); ?>!</p>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="profile.php" method="POST" enctype="multipart/form-data" class="mb-3">
                        <div class="form-group">
                            <label for="new_profile_pic">Atualizar foto de perfil:</label>
                            <input type="file" id="new_profile_pic" name="new_profile_pic" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Atualizar Foto</button>
                    </form>
                    <form action="post_status.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="status">Postar uma atualização de status:</label>
                            <textarea id="status" name="status" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Upload de imagem:</label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="youtube_link">Link do YouTube:</label>
                            <input type="url" id="youtube_link" name="youtube_link" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Postar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer bg-light text-center py-3 mt-4">
    <p>&copy; 2024 Mini Rede Social</p>
</footer>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

