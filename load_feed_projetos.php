<?php
include 'conexao.php';
session_start();

// Verifica se há um usuário ou funcionário logado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
    http_response_code(403); 
    exit('Acesso negado');
}

// Verifica o tipo de usuário logado e ajusta as variáveis de sessão e banco de dados
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
    $userId = $_SESSION['user_id'];
} elseif (isset($_SESSION['id_funcionario'])) {
    $userType = 'funcionario';
    $userId = $_SESSION['id_funcionario'];
}

// Define qual tipo de feed carregar (feed padrão ou seguindo)
$feedType = isset($_GET['feedType']) ? $_GET['feedType'] : 'feed';

// Seleciona apenas os projetos aprovados para exibição no feed
$stmt = $pdo->query('
    SELECT projetos.id_projeto, projetos.empresa_id, empresas.ID_empresas, empresas.nome_empresa, empresas.profile_pic, projetos.nome_projeto, projetos.descricao, projetos.imagem_capa, projetos.nivel_especialidade, projetos.max_inscricoes, projetos.data_criacao
    FROM projetos 
    JOIN empresas ON projetos.empresa_id = empresas.ID_empresas 
    WHERE projetos.status_aprovacao = "aprovado"  -- Apenas projetos aprovados
    ORDER BY projetos.data_criacao DESC
');

while ($row = $stmt->fetch()) {
    // Verifica quantas inscrições já existem para o projeto
    $stmt_count = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE id_projeto = :id_projeto');
    $stmt_count->execute(['id_projeto' => $row['id_projeto']]);
    $numInscritos = $stmt_count->fetchColumn();

    // Verifica se o usuário já está inscrito no projeto
    $stmt_inscricao = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE id_usuario = :id_usuario AND id_projeto = :id_projeto');
    $stmt_inscricao->execute(['id_usuario' => $userId, 'id_projeto' => $row['id_projeto']]);
    $jaInscrito = $stmt_inscricao->fetchColumn() > 0;

    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<div class='media'>";
    $profile_pic_url = htmlspecialchars($row['profile_pic']) ?: 'default-profile.png';
    echo "<img src='profile_pics/" . $profile_pic_url . "' class='mr-3 rounded-circle' alt='Profile Picture' style='width: 50px; height: 50px;' onclick=\"viewProfile(" . htmlspecialchars($row['empresa_id']) . ")\">";
    echo "<div class='media-body'>";
    echo "<h5 class='mt-0'>" . htmlspecialchars($row['nome_empresa']) . "</h5>";
    echo "<h4 class='mt-2'>" . htmlspecialchars($row['nome_projeto']) . "</h4>";
    if ($row['imagem_capa']) {
        echo "<img src='capa_projeto/" . htmlspecialchars($row['imagem_capa']) . "' class='img-fluid rounded mb-3' alt='Capa do Projeto'>";
    }
    echo "<p class='nivel_especialidade'> Nível de especialidade: " . htmlspecialchars($row['nivel_especialidade']) . "</p>";
    echo "<p>" . htmlspecialchars($row['descricao']) . "</p>";
    echo "<p class='text-muted'><small>" . htmlspecialchars($row['data_criacao']) . "</small></p>";

    // Verifica se há vagas disponíveis e se o usuário já está inscrito
    $vagasRestantes = $row['max_inscricoes'] - $numInscritos;

    // Adiciona o botão de inscrição ou a mensagem de já inscrito
    if ($jaInscrito) {
        echo "<button class='btn btn-success' disabled>Inscrito</button>";
        if ($vagasRestantes > 0) {
            echo "<span class='badge badge-info ml-2'>" . $vagasRestantes . " vagas disponíveis</span>";
        } else {
            echo "<span class='badge badge-danger ml-2'>Vagas Esgotadas</span>";
        }
    } elseif ($vagasRestantes > 0) {
        echo "<button class='btn btn-primary' onclick='inscreverProjeto(" . htmlspecialchars($row['id_projeto']) . ")'>Inscrever-se</button>";
        echo "<span class='badge badge-info ml-2'>" . $vagasRestantes . " vagas disponíveis</span>";
    } else {
        echo "<button class='btn btn-secondary' disabled>Vagas Esgotadas</button>";
    }

    echo "</div>"; // Fecha media-body
    echo "</div>"; // Fecha media
    echo "</div>"; // Fecha card-body
    echo "</div>"; // Fecha card
}
?>

<script>
    // Função para inscrição em projetos
    function inscreverProjeto(projetoId) {
        fetch('inscrever_projeto.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id_projeto: projetoId,
                id_usuario: <?php echo $userId; ?>
            })
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                alert('Inscrição realizada com sucesso!');
                // Recarrega a página para atualizar o feed
                location.reload();
            } else {
                alert('Erro ao inscrever-se no projeto.');
            }
        })
        .catch(error => console.error('Erro ao inscrever-se no projeto:', error));
    }
</script>
