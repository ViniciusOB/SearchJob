<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    // Seleciona todas as mensagens trocadas entre os usuários
    $stmt = $pdo->query("
        SELECT m.id, m.conteudo, m.data_envio, 
               remetente.nome_usuario AS remetente_nome, destinatario.nome_usuario AS destinatario_nome
        FROM mensagens m
        LEFT JOIN usuarios remetente ON m.remetente_id = remetente.id_usuario
        LEFT JOIN usuarios destinatario ON m.destinatario_id = destinatario.id_usuario
        ORDER BY m.data_envio DESC
    ");
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao acessar o banco de dados: " . $e->getMessage();
}

// Função para excluir a mensagem e suas notificações relacionadas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_mensagem'])) {
    $mensagem_id = $_POST['mensagem_id'];
    try {
        // Excluir as notificações associadas à mensagem
        $stmtDeleteNotificacoes = $pdo->prepare("DELETE FROM notificacoes WHERE mensagem_id = :mensagem_id");
        $stmtDeleteNotificacoes->bindParam(':mensagem_id', $mensagem_id);
        $stmtDeleteNotificacoes->execute();

        // Deletar a mensagem
        $stmtDeleteMensagem = $pdo->prepare("DELETE FROM mensagens WHERE id = :mensagem_id");
        $stmtDeleteMensagem->bindParam(':mensagem_id', $mensagem_id);
        $stmtDeleteMensagem->execute();
        
        echo "Mensagem excluída com sucesso!";
        // Redirecionar para a mesma página após a exclusão
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } catch (PDOException $e) {
        echo "Erro ao excluir a mensagem: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Mensagens</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn-excluir {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-excluir:hover {
            background-color: darkred;
        }

        .btn-voltar {
            display: block;
            width: 100px;
            text-align: center;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin: 20px auto 0 auto;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gerenciar Mensagens</h1>

    <?php if (count($mensagens) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Remetente</th>
                <th>Destinatário</th>
                <th>Conteúdo</th>
                <th>Data de Envio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mensagens as $mensagem): ?>
            <tr>
                <td><?php echo htmlspecialchars($mensagem['remetente_nome']); ?></td>
                <td><?php echo htmlspecialchars($mensagem['destinatario_nome']); ?></td>
                <td><?php echo htmlspecialchars($mensagem['conteudo']); ?></td>
                <td><?php echo htmlspecialchars($mensagem['data_envio']); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta mensagem?');">
                        <input type="hidden" name="mensagem_id" value="<?php echo $mensagem['id']; ?>">
                        <button type="submit" name="excluir_mensagem" class="btn-excluir">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Nenhuma mensagem encontrada.</p>
    <?php endif; ?>

    <a href="dashboard_admin.php" class="btn-voltar">Voltar</a>
</div>

</body>
</html>
