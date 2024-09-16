<?php
session_start();


if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Obtendo informações da empresa logada
$email = $_SESSION['email'];
$sql = "SELECT * FROM empresas WHERE email_de_trabalho = '$email'";
$result = $conn->query($sql);
$empresa = $result->fetch_assoc();
$id_empresa = $empresa['ID_empresas'];

// Verificar se há um termo de pesquisa
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Obtendo os projetos da empresa logada com base no termo de pesquisa
$sql_projetos = "SELECT * FROM projetos WHERE empresa_id = $id_empresa AND nome_projeto LIKE '%" . $conn->real_escape_string($search_term) . "%'";
$result_projetos = $conn->query($sql_projetos);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos - Empresa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/projeto_empresa.css"> 
</head>
<body>
    <?php include 'views/header_empresa.php'; ?> 

    <div class="projects-container">
        <div class="projects-body">
            <form class="form-inline my-2 my-lg-0 mr-auto" method="get" action="">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Pesquisar projetos" aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Pesquisar</button>
            </form>
            <div class="projects-grid">
                <div class="add-project-card" onclick="window.location.href='add_projeto.php'">
                    <span>+</span>
                </div>
                <?php while ($projeto = $result_projetos->fetch_assoc()) { ?>
                    <div class="project-card">
                        <img src="capa_projeto/<?php echo htmlspecialchars($projeto['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($projeto['nome_projeto']); ?>">
                        <div class="project-name"><?php echo htmlspecialchars($projeto['nome_projeto']); ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>