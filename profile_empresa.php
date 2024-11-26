<?php
session_start();
include 'conexao.php';

$is_owner = false; // Variável para determinar se quem está acessando é a própria empresa
$is_user = false;  // Variável para determinar se quem está acessando é um usuário

// Verificar se um usuário ou empresa está logado
if (isset($_GET['id_empresa'])) {
    // Se for usuário visualizando o perfil da empresa
    $id_empresa = $_GET['id_empresa'];
    $sql = "SELECT ID_empresas, nome_empresa, profile_pic, banner_empresa FROM empresas WHERE ID_empresas = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_empresa' => $id_empresa]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa) {
        // Registrar a visita se um usuário está visualizando
        if (isset($_SESSION['user_id']) || isset($_SESSION['id_funcionario'])) {
            $sql_visita = "INSERT INTO visitas_empresa (id_empresa) VALUES (:id_empresa)";
            $stmt_visita = $pdo->prepare($sql_visita);
            $stmt_visita->execute(['id_empresa' => $id_empresa]);
            $is_user = true; // Um usuário está visualizando o perfil
        }
    } else {
        echo "Empresa não encontrada.";
        exit();
    }
} elseif (isset($_SESSION['email'])) {
    // Se for empresa visualizando seu próprio perfil
    $email_empresa = $_SESSION['email'];
    $sql = "SELECT ID_empresas, nome_empresa, profile_pic, banner_empresa FROM empresas WHERE email_de_trabalho = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email_empresa]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa) {
        $is_owner = true; // A empresa está visualizando seu próprio perfil
    }
} else {
    header('Location: login.php');
    exit();
}

// Verificar se a imagem de perfil e o banner existem, caso contrário, usar imagens padrão
$profile_pic_path = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'profile_pics/default.png';
$banner_path = $empresa['banner_empresa'] ? 'banner_empresa/' . $empresa['banner_empresa'] : 'banner_empresa/default_banner.png';

// Verificar se o logado é uma empresa ou um usuário para incluir o cabeçalho correto
if ($is_owner) {
    // Se for empresa logada, usar o header_empresa
    include 'views/header_empresa.php';
} else {
    // Se for um usuário logado, usar o header padrão
    include 'views/header.php';
}

// Se for a própria empresa, permitir a edição
if ($is_owner && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Atualizar imagem de perfil
    if (isset($_FILES['profile_pic'])) {
        $profile_pic = $_FILES['profile_pic'];
        $profile_pic_name = time() . '_' . $profile_pic['name'];
        $profile_pic_target = 'profile_pics/' . $profile_pic_name;

        if (move_uploaded_file($profile_pic['tmp_name'], $profile_pic_target)) {
            $sql = "UPDATE empresas SET profile_pic = :profile_pic WHERE email_de_trabalho = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['profile_pic' => $profile_pic_name, 'email' => $email_empresa]);
            header('Location: profile_empresa.php');
            exit();
        }
    }

    // Atualizar banner
    if (isset($_FILES['banner_empresa'])) {
        $banner = $_FILES['banner_empresa'];
        $banner_name = time() . '_' . $banner['name'];
        $banner_target = 'banner_empresa/' . $banner_name;

        if (move_uploaded_file($banner['tmp_name'], $banner_target)) {
            $sql = "UPDATE empresas SET banner_empresa = :banner_empresa WHERE email_de_trabalho = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['banner_empresa' => $banner_name, 'email' => $email_empresa]);
            header('Location: profile_empresa.php');
            exit();
        }
    }

    // Atualizar nome da empresa
    if (isset($_POST['nome_empresa']) && !empty(trim($_POST['nome_empresa']))) {
        $novo_nome = trim($_POST['nome_empresa']);
        $sql = "UPDATE empresas SET nome_empresa = :nome_empresa WHERE email_de_trabalho = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nome_empresa' => $novo_nome, 'email' => $email_empresa]);
        header('Location: profile_empresa.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil da Empresa</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="CSS/profile_empresa.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-banner" <?php if ($is_owner) echo 'onclick="document.getElementById(\'bannerInput\').click();"' ?>>
                <img src="<?php echo $banner_path; ?>" alt="Banner da Empresa">
                <?php if ($is_owner): ?>
                <div class="overlay">
                    <i class="fas fa-edit" style="color: white; font-size: 2rem;"></i>
                </div>
                <?php endif; ?>
            </div>

            <div class="profile-pic-container" <?php if ($is_owner) echo 'onclick="document.getElementById(\'profilePicInput\').click();"' ?>>
                <img src="<?php echo $profile_pic_path; ?>" alt="Foto de Perfil">
                <?php if ($is_owner): ?>
                <div class="overlay">
                    <i class="fas fa-edit" style="color: white; font-size: 2rem;"></i>
                </div>
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <h2><?php echo htmlspecialchars($empresa['nome_empresa']); ?></h2>

                <?php if ($is_owner): ?>
                <form id="nameForm" action="" method="POST" onsubmit="return confirmarAlteracao();">
                    <div class="form-group">
                        <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="<?php echo htmlspecialchars($empresa['nome_empresa']); ?>" placeholder="Novo nome da empresa">
                    </div>
                    <button type="submit" class="btn btn-primary">Alterar Nome</button>
                </form>
                <?php endif; ?>
            </div>

            <?php if ($is_owner): ?>
            <form id="imageForm" action="" method="POST" enctype="multipart/form-data">
                <input type="file" id="profilePicInput" name="profile_pic" class="hidden-input" onchange="uploadImage('profile_pic');">
                <input type="file" id="bannerInput" name="banner_empresa" class="hidden-input" onchange="uploadImage('banner_empresa');">
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Função para confirmar a alteração do nome da empresa
        function confirmarAlteracao() {
            var novoNome = document.getElementById('nome_empresa').value;
            if (novoNome.trim() === "") {
                alert("O nome da empresa não pode estar vazio.");
                return false;
            }
            return confirm("Tem certeza que deseja alterar o nome da empresa para '" + novoNome + "'?");
        }

        // Função para upload da imagem via AJAX
        function uploadImage(fieldName) {
            var formData = new FormData(document.getElementById('imageForm'));

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "upload_image.php", true); // Criar upload_image.php para tratar a requisição
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Recarregar a página após a imagem ser enviada com sucesso
                    window.location.reload();
                } else {
                    alert("Erro ao enviar a imagem.");
                }
            };
            xhr.send(formData);
        }
    </script>
</body>
</html>

