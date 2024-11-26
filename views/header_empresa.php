<?php
session_start();
include 'conexao.php';

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Obter informações da empresa
$email_empresa = $_SESSION['email'];
$stmt = $pdo->prepare('SELECT nome_empresa, profile_pic FROM empresas WHERE email_de_trabalho = :email');
$stmt->execute(['email' => $email_empresa]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

$nomeEmpresa = $empresa['nome_empresa'];
$profilePic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'profile_pics/default.png';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
  /* Menu lateral */
.sidebar {
    height: 100vh;
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgb(24,21,54); /* Cor mais moderna */
    padding-top: 20px;
    color: #f9fafb;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 1000; /* Garante que o menu fique acima do conteúdo */
    transition: transform 0.3s ease; /* Animação de deslizamento */
}

.sidebar h4 {
    text-align: center;
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 1px;
    color: #f9fafb;
}

.sidebar a {
    color: #e5e7eb;
    text-decoration: none;
    display: block;
    padding: 15px 20px;
    font-size: 1.1rem;
    transition: background-color 0.3s, color 0.3s;
    border-left: 4px solid transparent; /* Destaque sutil ao hover */
}

.sidebar a:hover {
    background-color: #374151;
    color: #ffcc00;
    border-left-color: #ffcc00; /* Realçar o link ativo */
}

.profile-footer {
    margin-bottom: 20px;
    text-align: center;
}

.profile-footer img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 2px solid #ffcc00; /* Destaque sutil para a foto de perfil */
}

.logout-icon {
    font-size: 1.5rem;
    color: #f87171;
    margin-top: 10px;
    cursor: pointer;
    transition: color 0.3s;
}

.logout-icon:hover {
    color: #ef4444; /* Efeito hover para logout */
}

/* Ajuste do conteúdo principal */
.main-content {
    margin-left: 250px; /* Espaço para o menu lateral */
    padding: 20px;
    transition: margin-left 0.3s ease;
}

/* Estilos dos cards */
.card {
    margin-bottom: 20px;
}

/* Responsividade */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .sidebar a {
        padding: 12px 15px;
        font-size: 1rem;
    }

    .profile-footer img {
        width: 50px;
        height: 50px;
    }

    .main-content {
        margin-left: 200px; /* Ajuste para dispositivos menores */
    }
}

@media (max-width: 576px) {
    .sidebar {
        width: 180px;
    }

    .sidebar h4 {
        font-size: 1rem;
    }

    .sidebar a {
        padding: 10px 12px;
        font-size: 0.9rem;
    }

    .profile-footer img {
        width: 40px;
        height: 40px;
    }

    .main-content {
        margin-left: 180px; /* Ajuste para dispositivos menores */
    }
}

/* Responsividade para dispositivos móveis */
@media (max-width: 480px) {
    .sidebar {
        transform: translateX(-250px); /* Oculta a barra lateral */
    }

    .sidebar.active {
        transform: translateX(0); /* Mostra a barra lateral quando ativo */
    }

    .toggle-btn {
        display: block; /* Exibe o botão de menu */
        position: fixed;
        top: 20px;
        left: 85%;
        font-size: 1.8rem;
        color: #f9fafb;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 1001; /* Acima do menu lateral */
        background-color:black;
        border-radius:10px;
        
    }

    .main-content {
        margin-left: 0; /* Ajuste do conteúdo para ocupar toda a tela */
    }
}
@media (min-width: 1000px){
    .toggle-btn{
        opacity: 0;
        position: relative;
        top:400px;
        
    }

}

    </style>
</head>
<body>
<div class="sidebar">
    <h4><?php echo htmlspecialchars($nomeEmpresa); ?></h4>
    <a href="dashboard_empresa.php">Dashboard</a>
    <a href="gerenciar_funcionario.php">Funcionários</a>
    <a href="visualizar_projetos.php">Projetos</a>
    <a href="atividade_empresa.php">Atividade da Empresa</a>
    <a href="atividade_funcionario.php">Atividade dos Funcionários</a>

    <div class="profile-footer">
        <a href="profile_empresa.php">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Foto de perfil">
        </a>
        <div>
            <i class="fas fa-sign-out-alt logout-icon" onclick="confirmLogout()"></i>
        </div>
    </div>
</div>

<button class="toggle-btn">&#9776;</button>



<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script>
    function confirmLogout() {
        if (confirm('Tem certeza que deseja fazer logout?')) {
            window.location.href = 'logout.php';
        }
    }

    document.querySelector('.toggle-btn').addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('active');
});

</script>
</body>
</html>
