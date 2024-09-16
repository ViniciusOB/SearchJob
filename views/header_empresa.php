<?php

include 'conexao.php';

$email_usuario = $_SESSION['email'];
$sql = "SELECT profile_pic FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a empresa possui uma imagem de perfil
$profile_pic = $empresa['profile_pic'] ? '../profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard_empresa.php">
                    <span class="material-symbols-outlined">home</span>
                    <br> 
                    <span class="nav-text">Ínicio</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="projeto_empresa.php">
                    <span class="material-symbols-outlined">cases</span>
                    <br> 
                    <span class="nav-text">Projetos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="vagas_empresa.php">
                    <span class="material-symbols-outlined">work</span>
                    <br> 
                    <span class="nav-text">Vagas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="notificacoes_empresa.php">
                    <span class="material-symbols-outlined">notifications</span>
                    <br> 
                    <span class="nav-text">Notificações</span>
                </a>
            </li>
            <!-- Exibição da foto de perfil -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Foto" width="50" height="50" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#">Ver perfil</a>
                    <a class="dropdown-item" href="#">Assinatura</a>
                    <a class="dropdown-item" href="#">Ajuda</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="index.php">Sair</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
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
