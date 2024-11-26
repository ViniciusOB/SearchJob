<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <!-- Fonte Raleway -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Aplicar a fonte Raleway a todo o documento */
        body {
            font-family: 'Raleway', sans-serif;
        }
    </style>
</head>
<body class="bg-[#1a1833] flex justify-center items-center h-screen overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full bg-black bg-opacity-80 p-3 z-50">
        <div class="flex items-center justify-between mx-auto max-w-6xl px-4">
            <!-- Brand Logo -->
            <div class="text-2xl font-bold text-white">
                <a href="index.php" class="inline-flex items-center space-x-2" style="background-image: url('Img/searchIcon.png'); background-size: 30px; background-repeat: no-repeat; background-position: left center; padding-left: 40px;">
                    <span>JOB</span>
                </a>
            </div>

            <!-- Navbar Menu -->
            <ul class="flex items-center space-x-6">
                <li><a href="index.php" class="text-white hover:text-cyan-400 transition">Home</a></li>
                <li class="relative dropdown">
                    <a href="#" class="text-white hover:text-cyan-400 transition dropdown-toggle">Cadastrar-se</a>
                    <ul class="dropdown-menu absolute hidden mt-2 bg-black bg-opacity-90 p-2 rounded-lg space-y-1">
                        <li><a href="cadastro_empresa.php" class="text-white hover:text-cyan-400 transition block px-4 py-2">Corporativo</a></li>
                        <li><a href="registro.php" class="text-white hover:text-cyan-400 transition block px-4 py-2">Pessoal</a></li>
                    </ul>
                </li>
                <li><a href="home.php" class="text-white hover:text-cyan-400 transition">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Container do Formulário -->
    <div class="flex items-center justify-center mt-16 w-full px-4">
        <div class="bg-white bg-opacity-10 border border-opacity-20 rounded-lg p-6 w-full max-w-md shadow-lg hover:shadow-2xl transition">
            <h3 class="text-center text-cyan-400 text-2xl font-semibold mb-4">Login</h3>
            <form action="login.php" method="POST" class="space-y-4">
                <div class="form-group">
                    <label for="email" class="block text-cyan-400 mb-1">Email do Usuário:</label>
                    <input type="text" name="email" id="email" required class="w-full px-3 py-2 bg-transparent text-cyan-400 border-b-2 border-gray-300 focus:border-cyan-400 outline-none transition">
                </div>
                <div class="form-group">
                    <label for="senha" class="block text-cyan-400 mb-1">Senha:</label>
                    <input type="password" name="senha" id="senha" required class="w-full px-3 py-2 bg-transparent text-cyan-400 border-b-2 border-gray-300 focus:border-cyan-400 outline-none transition">
                </div>
                <button type="submit" class="w-full py-3 bg-white text-[#4C489D] font-semibold rounded-lg hover:bg-gray-200 transition">Login</button>
                <!-- Adicionar uma área para a mensagem de erro -->
<div id="erro" class="text-red-500 text-center mt-2 hidden">
    <!-- Esta mensagem será substituída pelo PHP se houver erro -->
</div>
            </form>
            <a href="recuperar_senha.php" class="block text-center text-cyan-400 mt-4 hover:underline">Esqueci minha senha</a>
        </div>
    </div>


<script>
    // Verificar se existe um parâmetro de erro na URL e exibir a mensagem de erro correspondente
    const urlParams = new URLSearchParams(window.location.search);
    const erroElement = document.getElementById('erro');

    if (urlParams.has('erro')) {
        erroElement.classList.remove('hidden');
        const erroCode = urlParams.get('erro');

        if (erroCode === '1') {
            erroElement.innerText = "Usuário não encontrado.";
        } else if (erroCode === '2') {
            erroElement.innerText = "Senha incorreta.";
        } else {
            erroElement.innerText = "Erro desconhecido.";
        }
    }
</script>



    <!-- Script do Dropdown -->
    <script>
        // Alternar exibição do dropdown ao clicar
        document.querySelector('.dropdown-toggle').addEventListener('click', function(event) {
            event.preventDefault();
            this.nextElementSibling.classList.toggle('hidden');
        });

        // Fechar o dropdown ao clicar fora dele
        document.addEventListener('click', function(event) {
            var dropdown = document.querySelector('.dropdown');
            if (!dropdown.contains(event.target)) {
                dropdown.querySelector('.dropdown-menu').classList.add('hidden');
            }
        });
    </script>
</body>
</html>