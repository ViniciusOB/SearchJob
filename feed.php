<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
</head>
<body class="bg-gray-100 text-gray-800">
    <?php
    session_start();
    include 'conexao.php';
    include 'views/header.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_funcionario'])) {
        header('Location: login.php');
        exit();
    }

    // Identifica o tipo de usuário e carrega informações do banco de dados
    if (isset($_SESSION['user_id'])) {
        $userType = 'cliente';
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT nome_usuario AS nome, profile_pic, descricao FROM usuarios WHERE id_usuario = :id_usuario');
        $stmt->execute(['id_usuario' => $userId]);
    } else {
        $userType = 'funcionario';
        $userId = $_SESSION['id_funcionario'];
        $stmt = $pdo->prepare('SELECT CONCAT(nome_funcionario, " ", sobrenome_funcionario) AS nome, profile_pic FROM funcionarios WHERE id_funcionario = :id_funcionario');
        $stmt->execute(['id_funcionario' => $userId]);
    }

    $user = $stmt->fetch();
    ?>

    <!-- Navbar Tabs -->
    <div class="bg-white shadow-md p-4 flex justify-center md:justify-start flex-wrap space-x-4 md:space-x-6 overflow-x-auto">
        <button class="tab font-semibold text-blue-600 hover:bg-blue-100 px-4 py-2 rounded transition" onclick="showContent('feed')">Para Você</button>
        <?php if ($userType === 'cliente'): ?>
            <button class="tab font-semibold text-gray-600 hover:bg-blue-100 px-4 py-2 rounded transition" onclick="showContent('seguindo')">Seguindo</button>
        <?php endif; ?>
    </div>

    <div class="flex flex-col md:flex-row max-w-6xl mx-auto mt-8 gap-6 px-4">
        <!-- Sidebar: Following List -->
        <div class="bg-gray-800 text-white p-4 rounded-lg shadow-md w-full md:w-64 mb-6 md:mb-0 overflow-y-auto max-h-screen">
            <h3 class="text-lg font-semibold mb-4">Seguindo</h3>
            <?php
            if ($userType === 'cliente') {
                $stmt = $pdo->prepare('
                    SELECT id_usuario, nome_usuario, profile_pic
                    FROM usuarios
                    JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id
                    WHERE seguidores.seguidor_id = :user_id
                ');
                $stmt->execute(['user_id' => $userId]);
                while ($following = $stmt->fetch()) {
                    echo '
                    <div class="flex items-center mb-4" onmouseover="showUserDetails(' . $following['id_usuario'] . ')" 
                         onmouseout="hideUserDetails(' . $following['id_usuario'] . ')">
                        <img src="' . htmlspecialchars($following['profile_pic'] ?: 'default-profile.png') . '" alt="Seguido" class="w-10 h-10 rounded-full mr-3 cursor-pointer">
                        <span>' . htmlspecialchars($following['nome_usuario']) . '</span>
                        <div class="user-details-popup hidden absolute bg-gray-700 p-4 rounded-lg shadow-lg z-10" id="user-details-' . $following['id_usuario'] . '">
                            <div class="flex items-center">
                                <img src="' . htmlspecialchars($following['profile_pic'] ?: 'default-profile.png') . '" alt="Foto de perfil" class="w-8 h-8 rounded-full mr-2">
                                <p class="font-semibold">' . htmlspecialchars($following['nome_usuario']) . '</p>
                            </div>
                            <form action="mensagens.php" method="get" class="mt-3">
                                <input type="hidden" name="user_id" value="' . $following['id_usuario'] . '">
                                <input type="text" name="message" placeholder="Digite uma mensagem..." required class="w-full p-2 rounded bg-gray-800 border border-gray-600 text-white">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-1 mt-2 rounded hover:bg-blue-600">Enviar</button>
                            </form>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-6">
            <!-- Post Form -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form id="postForm" action="post_status.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <img src="<?php echo htmlspecialchars($user['profile_pic'] ?: 'default-profile.png'); ?>" alt="Foto de perfil" class="w-10 h-10 rounded-full cursor-pointer" onclick="viewProfile(<?php echo $userId; ?>)">
                        <textarea name="status" placeholder="O que está acontecendo?" rows="3" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                    </div>
                    <input type="hidden" name="author_type" value="<?php echo $userType; ?>">
                    <div class="flex justify-between items-center">
                        <label for="image-upload" class="text-blue-500 cursor-pointer">&#128247;</label>
                        <input type="file" id="image-upload" name="image" accept="image/*" class="hidden">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Postar</button>
                    </div>
                </form>
            </div>

            <!-- Feed Content (Para Você) -->
            <div id="feed" class="space-y-4">
                <?php
                // Exibir todos os posts no feed "Para Você"
                $stmt = $pdo->prepare('
                    SELECT posts.*, 
                        CASE 
                            WHEN posts.author_type = "cliente" THEN usuarios.nome_usuario
                            WHEN posts.author_type = "funcionario" THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
                        END AS nome_autor, 
                        CASE 
                            WHEN posts.author_type = "cliente" THEN usuarios.profile_pic
                            WHEN posts.author_type = "funcionario" THEN funcionarios.profile_pic
                        END AS profile_pic
                    FROM posts
                    LEFT JOIN usuarios ON posts.user_id = usuarios.id_usuario
                    LEFT JOIN funcionarios ON posts.funcionario_id = funcionarios.id_funcionario
                    ORDER BY posts.created_at DESC
                ');
                $stmt->execute();
                
                while ($row = $stmt->fetch()) {
                    include 'post_template.php';
                }
                ?>
            </div>

            <!-- Following Content (Seguindo) -->
            <div id="seguindo" class="hidden space-y-4">
                <?php if ($userType === 'cliente'): ?>
                    <?php
                    // Exibe apenas posts dos usuários que o cliente está seguindo
                    $stmt = $pdo->prepare('
                        SELECT posts.*, 
                               CASE 
                                   WHEN posts.author_type = "cliente" THEN usuarios.nome_usuario
                                   WHEN posts.author_type = "funcionario" THEN CONCAT(funcionarios.nome_funcionario, " ", funcionarios.sobrenome_funcionario)
                               END AS nome_autor, 
                               CASE 
                                   WHEN posts.author_type = "cliente" THEN usuarios.profile_pic
                                   WHEN posts.author_type = "funcionario" THEN funcionarios.profile_pic
                               END AS profile_pic
                        FROM posts 
                        LEFT JOIN usuarios ON posts.user_id = usuarios.id_usuario 
                        LEFT JOIN funcionarios ON posts.funcionario_id = funcionarios.id_funcionario 
                        JOIN seguidores ON usuarios.id_usuario = seguidores.seguido_id 
                        WHERE seguidores.seguidor_id = :user_id
                        ORDER BY posts.created_at DESC
                    ');
                    $stmt->execute(['user_id' => $userId]);

                    while ($row = $stmt->fetch()) {
                        include 'post_template.php';
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showContent(id) {
            document.querySelectorAll('.space-y-4').forEach(content => content.classList.add('hidden'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('text-blue-600', 'bg-blue-200'));
            document.getElementById(id).classList.remove('hidden');
            document.querySelector('.tab[onclick="showContent(\'' + id + '\')"]').classList.add('text-blue-600', 'bg-blue-200');
        }

        function viewProfile(userId) {
            window.location.href = 'profile.php?user_id=' + userId;
        }

        function showUserDetails(userId) {
            document.getElementById('user-details-' + userId).classList.remove('hidden');
        }

        function hideUserDetails(userId) {
            document.getElementById('user-details-' + userId).classList.add('hidden');
        }
    </script>
</body>
</html>
