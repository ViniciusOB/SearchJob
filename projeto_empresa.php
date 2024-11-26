<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Conexão com o banco de dados MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Obtendo informações do funcionário logado
$email = $_SESSION['email'];
$sql_funcionario = "SELECT * FROM funcionarios WHERE email_funcionario = '$email'";
$result_funcionario = $conn->query($sql_funcionario);
$funcionario = $result_funcionario->fetch_assoc();
$id_funcionario = $funcionario['id_funcionario'];

// Verificar se há um termo de pesquisa
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Obtendo os projetos criados pelo funcionário logado com base no termo de pesquisa
$sql_projetos_pendentes = "SELECT * FROM projetos WHERE id_funcionario = $id_funcionario AND nome_projeto LIKE '%" . $conn->real_escape_string($search_term) . "%' AND status_aprovacao = 'pendente'";
$result_projetos_pendentes = $conn->query($sql_projetos_pendentes);

$sql_projetos_aprovados = "SELECT * FROM projetos WHERE id_funcionario = $id_funcionario AND nome_projeto LIKE '%" . $conn->real_escape_string($search_term) . "%' AND status_aprovacao = 'aprovado'";
$result_projetos_aprovados = $conn->query($sql_projetos_aprovados);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos - Funcionário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/projeto_empresa.css"> <!-- Link para o arquivo CSS separado -->
    <style>
        .tabs {
            display: flex;
            margin-bottom: 10px;
            background-color: #333;
            padding: 5px;
            border-radius: 5px;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            background-color: #444;
            color: #fff;
            border: 1px solid #444;
            transition: background-color 0.3s ease;
        }
        .tab.active {
            background-color: #555;
        }
        .tab:hover {
            background-color: #555;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .add-project-card {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            font-size: 24px;
            padding: 50px 20px;
            cursor: pointer;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }
        .add-project-card:hover {
            background-color: #45a049;
        }
        .project-card {
            background-color: white;
            color: black;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
            text-align: center;
        }
        .project-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .project-name {
            font-weight: bold;
            padding: 10px 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?> <!-- Incluindo o cabeçalho -->

    <div class="projects-container">
        <div class="projects-body">
            <form class="form-inline my-2 my-lg-0 mr-auto" method="get" action="">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Pesquisar projetos" aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Pesquisar</button>
            </form>
            <div class="tabs">
                <div class="tab active" onclick="showContent('pendente')">Projetos Pendentes</div>
                <div class="tab" onclick="showContent('aprovado')">Projetos Aprovados</div>
            </div>
            <div id="pendente" class="content-section active">
                <div class="projects-grid">
                    <!-- Adiciona o botão com "+" para redirecionar para a página de criar projeto -->
                    <div class="add-project-card" onclick="window.location.href='add_projeto.php'">
                        <span>+</span>
                    </div>

                    <!-- Exibe os projetos pendentes criados pelo funcionário logado -->
                    <?php while ($projeto = $result_projetos_pendentes->fetch_assoc()) { ?>
                        <div class="project-card" onclick="window.location.href='editar_projeto.php?id=<?php echo $projeto['id_projeto']; ?>'">
                            <img src="capa_projeto/<?php echo htmlspecialchars($projeto['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($projeto['nome_projeto']); ?>">
                            <div class="project-name"><?php echo htmlspecialchars($projeto['nome_projeto']); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div id="aprovado" class="content-section">
                <div class="projects-grid">
                    <!-- Exibe os projetos aprovados criados pelo funcionário logado -->
                    <?php while ($projeto = $result_projetos_aprovados->fetch_assoc()) { ?>
                        <div class="project-card" onclick="window.location.href='informacoes_projeto.php?id=<?php echo $projeto['id_projeto']; ?>'">
                            <img src="capa_projeto/<?php echo htmlspecialchars($projeto['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($projeto['nome_projeto']); ?>">
                            <div class="project-name"><?php echo htmlspecialchars($projeto['nome_projeto']); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

<script>
    function showContent(id) {
        document.querySelectorAll('.content-section').forEach(content => content.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        document.querySelector(`.tab[onclick="showContent('${id}')"]`).classList.add('active');
    }
</script>
</body>
</html>
