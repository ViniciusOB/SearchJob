<?php
session_start();

// Verificar se o usuário está autenticado, redirecionar para a página de login se não estiver
if (!isset($_SESSION['email']) || !isset($_SESSION['id_empresa'])) {
    header("Location: index.php");
    exit();
}

// Conectar ao banco de dados
include 'conexao.php';

// Obter o ID da empresa logada
$id_empresa = $_SESSION['id_empresa'];

// Variável para mensagem de erro ou sucesso
$mensagem = '';

// Verificar se uma ação de exclusão foi realizada
if (isset($_GET['excluir'])) {
    $id_funcionario = $_GET['excluir'];
    
    // Deletar o funcionário do banco de dados, apenas se ele pertence à empresa logada
    $sql = "DELETE FROM funcionarios WHERE id_funcionario = :id AND empresa_id = :empresa_id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['id' => $id_funcionario, 'empresa_id' => $id_empresa])) {
        $mensagem = "Funcionário excluído com sucesso!";
    } else {
        $mensagem = "Erro ao excluir funcionário.";
    }
}

// Buscar todos os funcionários que pertencem à empresa logada
$sql = "SELECT * FROM funcionarios WHERE empresa_id = :empresa_id";
if (isset($_GET['busca'])) {
    $busca = $_GET['busca'];
    $sql .= " AND (nome_funcionario LIKE :busca OR sobrenome_funcionario LIKE :busca OR email_funcionario LIKE :busca)";
}
$stmt = $pdo->prepare($sql);
if (isset($busca)) {
    $stmt->execute(['empresa_id' => $id_empresa, 'busca' => '%' . $busca . '%']);
} else {
    $stmt->execute(['empresa_id' => $id_empresa]);
}
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <title>Gerenciar Funcionários</title>
    <!-- Incluindo o Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <?php include 'views/header_empresa.php'; ?>

    <div class="container mx-auto px-4 py-6 md:py-8">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-md">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-800 mb-4">Gerenciar Funcionários</h1>

            <?php if ($mensagem): ?>
                <div class="alert alert-info bg-green-100 border border-green-300 text-green-700 rounded-lg p-4 mb-4">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <a href="cadastrar_funcionario.php" class="block sm:inline-block bg-blue-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded mb-4">Cadastrar Novo Funcionário</a>

            <!-- Barra de busca mais flexível e amigável -->
            <form method="get" action="" class="flex flex-col sm:flex-row sm:items-center mb-6 space-y-4 sm:space-y-0 sm:space-x-4">
                <input type="text" class="form-control bg-gray-100 border border-gray-300 rounded py-2 px-3 text-gray-700 w-full sm:flex-1 sm:max-w-sm" name="busca" placeholder="Buscar funcionário">
                <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">Buscar</button>
            </form>

            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-blue-800 mb-4">Lista de Funcionários</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="hidden sm:table-header-group bg-blue-800 text-white">
                        <tr>
                            <th class="p-3 text-left">Nome</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Data de Criação</th>
                            <th class="p-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <!-- Tabela responsiva, estilo "stacked" para mobile -->
                            <tr class="block sm:table-row border-b">
                                <td data-label="Nome" class="p-3 flex items-center sm:table-cell sm:text-left">
                                    <img src="profile_pics/<?php echo htmlspecialchars($funcionario['profile_pic'] ?: 'default-profile.png'); ?>" 
                                         alt="Profile Picture" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full mr-4 border-2 border-blue-800">
                                    <div class="block">
                                        <span class="text-gray-900 font-semibold"><?php echo htmlspecialchars($funcionario['nome_funcionario'] . ' ' . $funcionario['sobrenome_funcionario']); ?></span>
                                    </div>
                                </td>
                                
                                <!-- Dados de email e data, exibidos em telas pequenas com rótulos -->
                                <td data-label="Email" class="block p-3 sm:table-cell sm:text-left">
                                    <span class="block sm:hidden font-semibold text-gray-600">Email:</span>
                                    <?php echo htmlspecialchars($funcionario['email_funcionario']); ?>
                                </td>
                                <td data-label="Data de Criação" class="block p-3 sm:table-cell sm:text-left">
                                    <span class="block sm:hidden font-semibold text-gray-600">Data de Criação:</span>
                                    <?php echo date('d/m/Y', strtotime($funcionario['data_registro'])); ?>
                                </td>

                                <td data-label="Ações" class="block p-3 sm:table-cell sm:text-left">
                                    <a href="?excluir=<?php echo $funcionario['id_funcionario']; ?>" 
                                       class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded inline-block"
                                       onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tailwind CDN - already included above -->
    <script src="https://cdn.tailwindcss.com"></script>

</body>
</html>
