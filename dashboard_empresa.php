<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Obter o nome de usuário da sessão
$email_usuario = $_SESSION['email'];

// Conectar ao banco de dados e buscar a imagem de perfil da empresa logada
include 'conexao.php';

$sql = "SELECT profile_pic FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a empresa possui uma imagem de perfil
$profile_pic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        /* Estilos personalizados */
        body {
            background-color: #f0f0f0;
            display: flex;
        }
        .side-menu {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
        }
        .content {
            margin-left: 270px; /* Adicionando margem entre o side menu e o conteúdo */
            padding: 20px;
            width: 100%;
            flex: 1;
        }
        .card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 56px); /* Ajuste para a altura da navbar */
        }
        .folder {
            width: 180px;
            height: 140px;
            background-color: #007bff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 30px; /* Aumentando o espaçamento entre os blocos */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: transform 0.3s ease;
            position: relative;
        }
        .folder:before {
            content: '';
            position: absolute;
            bottom: -25px;
            left: 0;
            width: 0;
            height: 0;
            border-left: 90px solid transparent;
            border-right: 90px solid transparent;
            border-top: 25px solid #007bff;
        }
        .folder:hover {
            transform: translateY(-5px);
        }
        .folder-icon {
            font-size: 2.5em;
            color: #fff;
        }
        .folder-label {
            font-weight: bold;
            margin-top: 5px;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include 'views/header_empresa2.php'; ?>

    <div class="content">
        <div class="card-container">
            <!-- Blocos superiores -->
            <div class="folder">
                <i class="fas fa-folder-open folder-icon"></i>
                <div class="folder-label">Social</div>
            </div>
            <div class="folder">
                <i class="fas fa-folder-open folder-icon"></i>
                <div class="folder-label">Chat</div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Script para alternar a exibição do dropdown ao clicar na foto
        document.getElementById('profileDropdown').addEventListener('click', function() {
            var dropdownMenu = document.querySelector('.dropdown-menu');
            dropdownMenu.classList.toggle('show');
        });

        // Fechar o dropdown se clicar fora dele
        document.addEventListener('click', function(event) {
            var dropdownMenu = document.querySelector('.dropdown-menu');
            if (!event.target.closest('#profileDropdown')) {
                dropdownMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
