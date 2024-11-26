<?php
session_start();

// Verificar se a empresa está autenticada
if (!isset($_SESSION['id_empresa'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

// Obter ID da empresa
$id_empresa = $_SESSION['id_empresa'];

// Obter imagem de perfil da empresa
$email_empresa = $_SESSION['email'];
$sql = "SELECT profile_pic FROM empresas WHERE email_de_trabalho = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_empresa]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_pic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';

// Filtros e ordenação
$order_by = 'data_criacao DESC'; // Ordem padrão: mais recente para o mais antigo
$search = '';
$start_date = '';
$end_date = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['search'])) {
        $search = $_GET['search'];
    }
    if (!empty($_GET['start_date'])) {
        $start_date = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) {
        $end_date = $_GET['end_date'];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos Criados pelos Funcionários</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
</head>
<body class="bg-gray-100 min-h-screen">

    <?php include 'views/header_empresa.php'; ?>

    <div class="p-6 md:ml-64">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Projetos Criados pelos Funcionários</h1>

        <!-- Formulário de pesquisa e filtro -->
        <form method="GET" class="flex flex-wrap items-center gap-4 mb-6">
            <input type="text" name="search" placeholder="Pesquisar por nome" value="<?php echo htmlspecialchars($search); ?>" class="p-2 border border-gray-300 rounded-lg w-full md:w-1/3">
            <div class="flex items-center gap-2">
                <label class="text-gray-700 font-medium">Data de Criação:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="p-2 border border-gray-300 rounded-lg">
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="p-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-500 transition">Filtrar</button>
        </form>

        <!-- Tabs -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="tab active cursor-pointer p-4 bg-gray-200 rounded-full text-center text-gray-700 font-semibold" onclick="showContent('pendente')">Projetos Pendentes</div>
            <div class="tab cursor-pointer p-4 bg-gray-200 rounded-full text-center text-gray-700 font-semibold" onclick="showContent('aprovado')">Projetos Aprovados</div>
        </div>

        <!-- Conteúdo de projetos pendentes -->
        <div id="pendente" class="content-section active">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <?php
                // Query para projetos pendentes
                $sql_pendente = "SELECT p.*, f.nome_funcionario 
                                 FROM projetos p 
                                 JOIN funcionarios f ON p.id_funcionario = f.id_funcionario 
                                 WHERE p.empresa_id = :id_empresa 
                                 AND p.status_aprovacao = 'pendente'";

                // Aplicar filtros
                if ($search) {
                    $sql_pendente .= " AND p.nome_projeto LIKE :search";
                }
                if ($start_date && $end_date) {
                    $sql_pendente .= " AND p.data_criacao BETWEEN :start_date AND :end_date";
                }

                // Adiciona ordenação
                $sql_pendente .= " ORDER BY $order_by";

                $stmt_pendente = $pdo->prepare($sql_pendente);  
                $params = ['id_empresa' => $id_empresa];

                if ($search) {
                    $params['search'] = "%$search%";
                }
                if ($start_date && $end_date) {
                    $params['start_date'] = $start_date;
                    $params['end_date'] = $end_date;
                }

                $stmt_pendente->execute($params);
                $projetos_pendentes = $stmt_pendente->fetchAll(PDO::FETCH_ASSOC);

                foreach ($projetos_pendentes as $projeto_pendente): ?>
                    <div class="bg-white rounded-lg shadow p-4 cursor-pointer transition transform hover:-translate-y-1 hover:shadow-lg" onclick="window.location.href='informacoes_projeto.php?id=<?php echo $projeto_pendente['id_projeto']; ?>'">
                        <img src="capa_projeto/<?php echo htmlspecialchars($projeto_pendente['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($projeto_pendente['nome_projeto']); ?>" class="w-full h-40 object-cover rounded-lg mb-4"> <!-- Alterada para h-40 -->
                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($projeto_pendente['nome_projeto']); ?></h3>
                        <p class="text-gray-600 text-sm mt-2">Criado em: <?php echo date('d/m/Y H:i', strtotime($projeto_pendente['data_criacao'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Conteúdo de projetos aprovados -->
        <div id="aprovado" class="content-section hidden">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <?php
                // Query para projetos aprovados
                $sql_aprovado = "SELECT p.*, f.nome_funcionario 
                                 FROM projetos p 
                                 JOIN funcionarios f ON p.id_funcionario = f.id_funcionario 
                                 WHERE p.empresa_id = :id_empresa 
                                 AND p.status_aprovacao = 'aprovado'";

                // Aplicar filtros
                if ($search) {
                    $sql_aprovado .= " AND p.nome_projeto LIKE :search";
                }
                if ($start_date && $end_date) {
                    $sql_aprovado .= " AND p.data_criacao BETWEEN :start_date AND :end_date";
                }

                // Adiciona ordenação
                $sql_aprovado .= " ORDER BY $order_by";

                $stmt_aprovado = $pdo->prepare($sql_aprovado);
                $params = ['id_empresa' => $id_empresa];

                if ($search) {
                    $params['search'] = "%$search%";
                }
                if ($start_date && $end_date) {
                    $params['start_date'] = $start_date;
                    $params['end_date'] = $end_date;
                }

                $stmt_aprovado->execute($params);
                $projetos_aprovados = $stmt_aprovado->fetchAll(PDO::FETCH_ASSOC);

                foreach ($projetos_aprovados as $projeto_aprovado): ?>
                    <div class="bg-white rounded-lg shadow p-4 cursor-pointer transition transform hover:-translate-y-1 hover:shadow-lg" onclick="window.location.href='informacoes_projeto.php?id=<?php echo $projeto_aprovado['id_projeto']; ?>'">
                        <img src="capa_projeto/<?php echo htmlspecialchars($projeto_aprovado['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($projeto_aprovado['nome_projeto']); ?>" class="w-full h-40 object-cover rounded-lg mb-4"> <!-- Alterada para h-40 -->
                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($projeto_aprovado['nome_projeto']); ?></h3>
                        <p class="text-gray-600 text-sm mt-2">Criado em: <?php echo date('d/m/Y H:i', strtotime($projeto_aprovado['data_criacao'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function showContent(id) {
            document.querySelectorAll('.content-section').forEach(content => content.classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('bg-blue-600', 'text-white'));
            document.querySelector(`[onclick="showContent('${id}')"]`).classList.add('bg-blue-600', 'text-white');
        }
    </script>
</body>
</html>
