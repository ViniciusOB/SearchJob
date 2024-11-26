<?php
session_start();
include 'conexao.php';

// Verificar se o usuário está logado como funcionário
if (!isset($_SESSION['id_funcionario'])) {
    header("Location: login.php");
    exit();
}

$id_funcionario = $_SESSION['id_funcionario'];

// Obter os projetos criados pelo funcionário logado para a lista de assuntos
$stmt = $pdo->prepare('SELECT id_projeto, nome_projeto FROM projetos WHERE id_funcionario = :id_funcionario');
$stmt->execute(['id_funcionario' => $id_funcionario]);
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_projeto = $_POST['assunto'];
    $descricao = $_POST['descricao'];
    $link = $_POST['link'];
    $arquivo = NULL;

    // Verificar se um arquivo foi enviado
    if (!empty($_FILES['arquivo']['name'])) {
        $arquivo = 'relatorios/' . basename($_FILES['arquivo']['name']);
        move_uploaded_file($_FILES['arquivo']['tmp_name'], $arquivo);
    }

    // Verificar se pelo menos um link ou arquivo foi fornecido
    if (empty($link) && empty($arquivo)) {
        $mensagem = 'Por favor, forneça um link ou um arquivo.';
    } else {
        // Inserir o relatório no banco de dados
        $stmt = $pdo->prepare('
            INSERT INTO relatorios (id_funcionario, id_projeto, assunto, descricao, arquivo, link) 
            VALUES (:id_funcionario, :id_projeto, :assunto, :descricao, :arquivo, :link)
        ');
        $stmt->execute([
            'id_funcionario' => $id_funcionario,
            'id_projeto' => $id_projeto,
            'assunto' => $_POST['assunto'],
            'descricao' => $descricao,
            'arquivo' => $arquivo,
            'link' => $link
        ]);

        $mensagem = 'Relatório enviado com sucesso!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Relatório</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'views/header.php'; ?>
<div class="container mt-5">
    <h1>Enviar Relatório</h1>
    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="assunto">Assunto (Projeto):</label>
            <select name="assunto" id="assunto" class="form-control" required>
                <option value="">Selecione um projeto</option>
                <?php foreach ($projetos as $projeto): ?>
                    <option value="<?php echo $projeto['id_projeto']; ?>"><?php echo $projeto['nome_projeto']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="arquivo">Anexar Arquivo:</label>
            <input type="file" name="arquivo" id="arquivo" class="form-control">
        </div>
        <div class="form-group">
            <label for="link">Link:</label>
            <input type="url" name="link" id="link" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Enviar Relatório</button>
    </form>
</div>
</body>
</html>
