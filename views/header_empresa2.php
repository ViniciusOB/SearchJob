
<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Obter o nome de usuário da sessão
$email_usuario = $_SESSION['email'];

// Conectar ao banco de dados e buscar o nome e a imagem de perfil da empresa logada
include 'conexao.php';

$sql = "SELECT nome_empresa, profile_pic FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a empresa possui uma imagem de perfil
$profile_pic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';
$nome_empresa = $empresa['nome_empresa'];
?>

<nav class="side-menu">
    <h2><?php echo $nome_empresa; ?></h2>
    <ul>
        <li><a href="dashboard_empresa.php">Dashboard</a></li>
        <li><a href="gerenciar_funcionario.php">Funcionários</a></li>
        <li><a href="visualizar_projetos.php">Projetos</a></li>
        <li><a href="atividade_empresa.php">Atividade da Empresa</a></li>
        <li><a href="atividade_funcionarios.php">Atividade dos Funcionários</a></li>
    </ul>
    <div class="profile-section">
        <div class="dropright">
            <a href="#" class="dropdown-toggle" id="profileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-pic">
            </a>
            <div class="dropdown-menu" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="editar_perfil.php">Editar Perfil</a>
                <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<style>
    body {
        display: flex;
        min-height: 100vh;
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }
    .side-menu {
        width: 250px;
        background-color: #333;
        color: white;
        padding: 20px;
        position: fixed;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .side-menu h2 {
        text-align: center;
        margin-bottom: 40px;
    }
    .side-menu ul {
        list-style-type: none;
        padding: 0;
        flex-grow: 1;
    }
    .side-menu ul li {
        margin: 20px 0;
    }
    .side-menu ul li a {
        text-decoration: none;
        color: white;
        font-size: 18px;
        display: block;
        text-align: center;
    }
    .side-menu ul li a:hover {
        color: #f1c40f;
    }
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
    }
    .profile-section {
        padding-top: 20px;
        text-align: center;
    }
    .profile-pic {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: inline-block;
        margin-bottom: 10px;
    }
    .dropright .dropdown-menu {
        left: 105%;
        top: 0;
    }
    .dropright .dropdown-menu a {
        color: #333;
        text-decoration: none;
    }
</style>
