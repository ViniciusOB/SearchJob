<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="rickeocara">Home</title>
    <link rel="stylesheet" href="CSS/home_css.css">
</head>
<body>
<nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <a href="home.php" style="background-image: url('Img/searchIcon.png');">JOB</a>
            </div>
            <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Cadastrar-se</a>
                    <ul class="dropdown-menu">
                        <li><a href="cadastro_empresa.php" class="dropdown-item">Corporativo</a></li>
                        <li><a href="registro.php" class="dropdown-item">Pessoal</a></li>
                    </ul>
                </li>
                <li><a href="index.php" class="nav-link">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="gradient-section">
        <div class="boaVinda">
            <h1>Code your future, shape the world!</h1>
        </div>
        <div class="Texto-Boavinda">
            Nosso objetivo é ajudar profissionais iniciantes na área de TI a conseguirem seu primeiro emprego...
        </div>
    </div>

<div class="backgroundpt2"></div>


<div class ="container_body">
    <div class="container-img">
<img src="Img/trabalho3.png" alt="Imagem 3" class="image-slow">
    </div>
</div>

<div class="project-card">
  <h2>Comunidade</h2>
  <p>Mostre seus projetos e interaja com a nossa sociedade</p>
  <p class="texto-project-card">Converse com outros usuários para compartilhar seus projetos, trocar ideias e aprender novas técnicas. Aproveite a oportunidade para colaborar, receber feedback construtivo e expandir seus conhecimentos, enquanto explora soluções criativas e inovações com a comunidade.</p>
</div>

    <script>
        // Script para alternar a exibição do dropdown ao clicar
        document.querySelector('.dropdown-toggle').addEventListener('click', function(event) {
            event.preventDefault();
            this.parentElement.classList.toggle('show');
        });

        // Fechar o dropdown se clicar fora dele
        document.addEventListener('click', function(event) {
            var dropdown = document.querySelector('.dropdown');
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

    
    </script>
 
</body>
</html>
