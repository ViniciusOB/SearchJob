<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
   
    <link rel="stylesheet" href="index.css">

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelector('.text-modelo').addEventListener('click', function() {
                window.location.href = 'registro.php';
            });
        });
    </script>
</head>


<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <a href="home.php" style="background-image: url('Img/searchIcon.png');">JOB</a>
            </div>
            <ul class="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="#">Contato</a></li>
                <li><a href="index.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <video autoplay muted loop class="video-bg">
        <source src="Img/background.mp4" type="video/mp4">
        Seu navegador não suporta a tag de vídeo.
    </video>


    <div class="boaVinda">
        <h1>SEJA BEM-VINDO</h1>
    </div>
    <div class="Texto-Boavinda">
        Nosso objetivo é ajudar profissionais iniciantes na área de TI a conseguirem seu primeiro emprego...
    </div>

    <div href ="registro.php"  class="text-modelo">
        <p id="texto"></p>
    </div>

    <script type="text/javascript" charset="utf-8">
        const textos = [
            "Acabou de se formar?...................",
            "Quer oportunidade de arranjar seu primeiro emprego?...................",
            "Então venha fazer parte do SEARCHJOB!!!"
        ];

        const elementoTexto = document.getElementById('texto');
        let i = 0;
        let j = 0;
        let textoAtual = textos[i];

        function exibirTexto() {
            elementoTexto.textContent = textoAtual.substring(0, j);
            j++;
            if (j > textoAtual.length) {
                j = 0;
                i++;
                if (i >= textos.length) {
                    i = 0;
                }
                textoAtual = textos[i];
                setTimeout(exibirTexto, 1000);
            } else {
                setTimeout(exibirTexto, 100);
            }
        }

        exibirTexto();
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
