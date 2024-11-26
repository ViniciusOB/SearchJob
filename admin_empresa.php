<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    // Pegando lista de empresas com suas fotos de perfil
    $stmt = $pdo->query("
        SELECT e.ID_empresas, e.nome_empresa, e.profile_pic
        FROM empresas e
    ");
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao acessar o banco de dados: " . $e->getMessage();
}

// Lógica para excluir a empresa e todas as dependências associadas a ela
if (isset($_POST['excluir_empresa'])) {
    $empresa_id = $_POST['id_empresa'];

    try {
        // Iniciar a transação
        $pdo->beginTransaction();

        // Excluir dependências relacionadas à empresa antes de excluir a empresa
        // Excluir funcionários
        $stmtDeleteFuncionarios = $pdo->prepare("DELETE FROM funcionarios WHERE empresa_id = :empresa_id");
        $stmtDeleteFuncionarios->bindParam(':empresa_id', $empresa_id);
        $stmtDeleteFuncionarios->execute();

        // Excluir projetos da empresa
        $stmtDeleteProjetos = $pdo->prepare("DELETE FROM projetos WHERE empresa_id = :empresa_id");
        $stmtDeleteProjetos->bindParam(':empresa_id', $empresa_id);
        $stmtDeleteProjetos->execute();

        // Excluir visitas da empresa
        $stmtDeleteVisitas = $pdo->prepare("DELETE FROM visitas_empresa WHERE id_empresa = :empresa_id");
        $stmtDeleteVisitas->bindParam(':empresa_id', $empresa_id);
        $stmtDeleteVisitas->execute();

        // Excluir a própria empresa
        $stmtDeleteEmpresa = $pdo->prepare("DELETE FROM empresas WHERE ID_empresas = :empresa_id");
        $stmtDeleteEmpresa->bindParam(':empresa_id', $empresa_id);
        $stmtDeleteEmpresa->execute();

        // Confirmar a transação
        $pdo->commit();

        // Redirecionar para a página de admin_empresa após a exclusão
        header("Location: admin_empresa.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Erro ao excluir a empresa: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            color: #444;
            margin-bottom: 40px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .empresa-lista {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .empresa-card {
            background-color: #fff;
            border: none;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .empresa-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-8px);
        }

        .empresa-card img {
            max-width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #ececec;
        }

        .empresa-card h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }

        .btn-excluir {
            background-color: red;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-voltar {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            font-size: 0.9rem;
            color: #777;
            padding: 20px 0;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Empresas Cadastradas</h1>

        <div class="empresa-lista">
            <?php foreach ($empresas as $empresa) { 
                // Verificar se a empresa possui uma imagem de perfil
                $profile_pic = $empresa['profile_pic'] ? 'profile_pics/' . $empresa['profile_pic'] : 'default-profile.png';
            ?>
                <div class="empresa-card">
                    <div>
                        <!-- Exibir imagem de perfil da empresa -->
                        <img src="<?php echo $profile_pic; ?>" alt="Foto de Perfil da Empresa">
                        <h3><?php echo $empresa['nome_empresa']; ?></h3>
                    </div>
                    <div>
                        <br>
                        <!-- Botão para excluir a empresa -->
                        <form method="POST" action="admin_empresa.php" onsubmit="return confirm('Tem certeza que deseja excluir esta empresa e todas as suas dependências?');">
                            <input type="hidden" name="id_empresa" value="<?php echo $empresa['ID_empresas']; ?>">
                            <button type="submit" name="excluir_empresa" class="btn-excluir">Excluir Empresa</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
        <a href="dashboard_admin.php" class="btn-voltar">Voltar ao Dashboard</a>
    </div>
    <footer>
        &copy; <?php echo date("Y"); ?> Dashboard Admin. Todos os direitos reservados.
    </footer>
</body>
</html>
