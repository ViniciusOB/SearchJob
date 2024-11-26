<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SearchJob | Encontre seu futuro na TI</title>
    <link rel="icon" type="image/x-icon" href="Img/SearchJob.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="CSS/landpage2.css"> 
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="#" class="logo">
                <img src="Img/SearchJob.png" alt="SearchJob Logo">
            </a>
            <div class="nav-links">
                <a href="#home" class="hover:text-blue-500 transition-colors duration-300">Home</a>
                <a href="#about">Sobre</a>
                <a href="#features">Recursos</a>
                <a href="#testimonials">Depoimentos</a>
                <a href="home.php" class="hover:text-blue-500 transition-colors duration-300">Login</a>
                <a href="#faq">FAQ</a>
                <div id="divBusca" class="search-bar flex items-center bg-[#1c1c3c] rounded-full overflow-hidden">
                <input type="text" id="txtBusca" placeholder="Buscar Palavra Chave..." class="bg-[#2c2c54] text-white placeholder-gray-400 px-4 py-2 focus:outline-none w-[180px] md:w-[200px]">
                <button id="btnBusca" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2">Buscar</button>
            </div>

            <a href="#" class="cta-button cadastrar-btn hover:text-blue-500 transition-colors duration-300" onclick="openModal()">Cadastrar-se</a>

               

            </div>
        </div>
    </nav>
   
<div id="registerModal" class="modal fixed inset-0 bg-black bg-opacity-70 p-4">
    <div class="modal-content bg-[#1f1f3a] text-gray-300 rounded-lg p-6 max-w-md w-full">
        <span class="close text-gray-400 text-2xl cursor-pointer float-right" onclick="closeModal()">&times;</span>
                <div class="tabs flex justify-center space-x-4 my-4">
                    <button class="tab-button bg-[#2a2a47] text-gray-400 py-2 px-4 rounded-full active" onclick="showForm('empresa')">Corporativo</button>
                    <button class="tab-button bg-[#2a2a47] text-gray-400 py-2 px-4 rounded-full" onclick="showForm('usuario')">Pessoal</button>
                </div>

                <!-- Formulário de Empresa -->
                <div id="empresaForm" class="form-container">
                    <form id="formEmpresa" action="cadastro_empresa.php" method="post" enctype="multipart/form-data" class="space-y-4">
                        <!-- Campos do formulário -->
                        <div class="form-group">
                            <label for="nome_empresa" class="block text-gray-400">Nome da empresa:</label>
                            <input type="text" name="nome_empresa" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="email_de_trabalho" class="block text-gray-400">Email corporativo:</label>
                            <input type="email" name="email_de_trabalho" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="senha_empresa" class="block text-gray-400">Senha:</label>
                            <input type="password" name="senha_empresa" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="telefone_empresa" class="block text-gray-400">Telefone para contato:</label>
                            <input type="text" name="telefone_empresa" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="profile_pic" class="block text-gray-400">Foto de perfil:</label>
                            <input type="file" name="profile_pic" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="banner_empresa" class="block text-gray-400">Banner da empresa:</label>
                            <input type="file" name="banner_empresa" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Cadastrar Empresa</button>
                    </form>
                </div>

                <!-- Formulário de Usuário -->
                <div id="usuarioForm" class="form-container hidden">
                    <form id="formUsuario" action="registro_processar.php" method="post" enctype="multipart/form-data" class="space-y-4">
                        <div class="form-group">
                            <label for="nome" class="block text-gray-400">Nome:</label>
                            <input type="text" name="nome" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="sobrenome" class="block text-gray-400">Sobrenome:</label>
                            <input type="text" name="sobrenome" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="email" class="block text-gray-400">E-mail:</label>
                            <input type="email" name="email" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="senha" class="block text-gray-400">Senha:</label>
                            <input type="password" name="senha" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="pergunta" class="block text-gray-400">Pergunta de Segurança:</label>
                            <select name="pergunta" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                                <option value="">Selecione uma pergunta</option>
                                <option value="1">Qual é o nome do seu animal de estimação?</option>
                                <option value="2">Qual é a sua cidade natal?</option>
                                <option value="3">Qual é o nome do seu melhor amigo?</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="resposta" class="block text-gray-400">Resposta:</label>
                            <input type="text" name="resposta" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <div class="form-group">
                            <label for="profile_pic" class="block text-gray-400">Foto de perfil:</label>
                            <input type="file" name="profile_pic" class="w-full p-2 rounded bg-[#2a2a47] text-white border border-gray-600 focus:outline-none">
                        </div>
                        <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Registrar Usuário</button>
                    </form>
                </div>
            </div>
        </div>
   
            <!-- Menu Mobile (hamburger) -->
            <div class="md:hidden flex items-center">
                <button id="mobileMenuBtn" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </nav>

    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Bem-vindo ao SearchJob</h1>
            <p>Code seu futuro, molde o mundo!</p>
            <a href="home.php" class="cta-button">Comece Agora</a>
        </div>
    </section>

    <section id="about" class="info-section">
        <div class="container">
            <h2 class="section-title">Sobre Nós</h2>
            <div class="info-grid">
                <div class="info-box">
                    <h3>Sobre a Empresa</h3>
                    <p>Nosso objetivo é proporcionar um ambiente onde profissionais iniciantes na área de TI possam conquistar seu primeiro emprego e desenvolver suas habilidades. Fazemos isso conectando empresas e profissionais através de projetos que permitem a avaliação técnica e comportamental dos participantes.</p>
                </div>
                <div class="info-box">
                    <h3>Para Empresas</h3>
                    <p>Oferecemos às empresas a possibilidade de publicar projetos para diferentes níveis de experiência, permitindo a identificação de novos talentos em ambientes de trabalho práticos e colaborativos. As empresas podem acompanhar o desempenho técnico e comportamental dos profissionais, facilitando a seleção e contratação.</p>
                </div>
                <div class="info-box">
                    <h3>Para Usuários Pessoais</h3>
                    <p>Se você é um profissional de TI, especialmente iniciante, nossa plataforma oferece diversas formas de se destacar. Além de participar de projetos criados por empresas, você pode publicar seus próprios trabalhos, expondo suas habilidades e conquistas para recrutadores e olheiros.</p>
                </div>
                <div class="info-box">
                    <h3>Comunidade</h3>
                    <p>Acreditamos na força da comunidade e na troca de conhecimentos. Nossa plataforma é um espaço para você interagir com outros profissionais, aprender novas habilidades e compartilhar suas experiências. Seja participando de projetos, publicando seu trabalho ou colaborando em discussões, você se torna parte de uma rede que valoriza o crescimento coletivo.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Nossos Recursos</h2>
            <div class="features-grid">
                <div class="feature-box">
                    <i class="fas fa-project-diagram feature-icon"></i>
                    <h3>Projetos Reais</h3>
                    <p>Trabalhe em projetos reais propostos por empresas e ganhe experiência prática.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-users feature-icon"></i>
                    <h3>Networking</h3>
                    <p>Conecte-se com outros profissionais e expanda sua rede de contatos na área de TI.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3>Avaliação de Desempenho</h3>
                    <p>Receba feedback detalhado sobre seu desempenho em projetos e identifique áreas de melhoria.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-briefcase feature-icon"></i>
                    <h3>Oportunidades de Emprego</h3>
                    <p>Acesse vagas exclusivas e seja descoberto por empresas em busca de talentos.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <h2 class="section-title">O que dizem nossos usuários</h2>
            <div class="testimonial">
                <p class="testimonial-content">"Graças ao SearchJob, consegui meu primeiro emprego como desenvolvedor. Os projetos me deram a experiência prática que eu precisava!"</p>
                <p class="testimonial-author">- João Silva, Desenvolvedor Junior</p>
            </div>
            <div class="testimonial">
                <p class="testimonial-content">"Como recrutadora, o SearchJob simplificou muito nosso processo de seleção. Podemos ver os candidatos em ação antes mesmo de entrevistá-los."</p>
                <p class="testimonial-author">- Maria Santos, Recrutadora de TI</p>
            </div>
        </div>
    </section>

    <section id="faq" class="faq-section">
        <div class="container">
            <h2 class="section-title">Perguntas Frequentes</h2>
            <div class="faq-item">
                <h3 class="faq-question">Como funciona o SearchJob?</h3>
                <p class="faq-answer">O SearchJob conecta profissionais de TI a empresas através de projetos reais. Você pode participar de projetos, construir seu portfólio e ser descoberto por recrutadores.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">É gratuito para usar?</h3>
                <p class="faq-answer">O usuário pode ganhar experiência com projetos disponibilizados pelas empresas. Mas, se o usuário deseja uma maior experiência, deverá assinar o nosso plano mensal.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">Como as empresas podem participar?</h3>
                <p class="faq-answer">Empresas podem se cadastrar, publicar projetos e avaliar profissionais. Oferecemos diferentes planos para atender às necessidades de cada empresa.</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
            <p>&copy; 2024 SearchJob - Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            var navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 
 '#1c1c3c';
            } else {
                navbar.style.backgroundColor = 'transparent';
            }
        });

        // FAQ Toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
    <script>
        // Função de busca e destaque de texto
        document.getElementById('btnBusca').addEventListener('click', function() {
            var termoBusca = document.getElementById('txtBusca').value.toLowerCase();
            if (!termoBusca) {
                alert('Por favor, insira uma palavra-chave para busca.');
                return;
            }

            var elementos = document.querySelectorAll('h1, h2, p');
            var encontrado = false;

            for (var i = 0; i < elementos.length; i++) {
                if (highlightText(elementos[i], termoBusca)) {
                    encontrado = true;
                    elementos[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    break;
                }
            }

            if (!encontrado) {
                alert('Palavra não encontrada.');
            }
        });

        function highlightText(element, searchText) {
            var innerHTML = element.innerHTML;
            var regex = new RegExp(searchText, 'gi');
            var highlighted = innerHTML.replace(regex, function(match) {
                return "<span class='highlight bg-yellow-300 text-black px-1'>" + match + "</span>";
            });

            if (innerHTML !== highlighted) {
                element.innerHTML = highlighted;
                return true;
            }
            return false;
        }

        
        function showForm(formType) {
            var empresaForm = document.getElementById('empresaForm');
            var usuarioForm = document.getElementById('usuarioForm');
            var buttons = document.getElementsByClassName('tab-button');

            if (formType === 'empresa') {
                empresaForm.classList.remove('hidden');
                usuarioForm.classList.add('hidden');
                buttons[0].classList.add('active');
                buttons[1].classList.remove('active');
            } else {
                usuarioForm.classList.remove('hidden');
                empresaForm.classList.add('hidden');
                buttons[1].classList.add('active');
                buttons[0].classList.remove('active');
            }
        }

        // Navbar scroll behavior
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('bg-[#1c1c3c]');
            } else {
                navbar.classList.remove('bg-[#1c1c3c]');
            }
        });

        // Menu Mobile
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            var modal = document.getElementById('registerModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Armazena a palavra-chave e a sequência digitada
const keyword = "maiores";
let userInput = "";

// Evento para capturar a entrada de teclas
document.addEventListener('keydown', function (event) {
    userInput += event.key.toLowerCase();

    // Verifica se a sequência digitada coincide com a palavra-chave
    if (userInput.includes(keyword)) {
        // Exibe a imagem do easter egg
        const easterEgg = document.getElementById('easterEgg');
        easterEgg.classList.remove('hidden');
        easterEgg.classList.add('flex');

        // Reseta o input do usuário para evitar múltiplas ativações
        userInput = "";
    } else if (userInput.length > keyword.length) {
        // Limita o tamanho do input armazenado para a palavra-chave
        userInput = userInput.slice(-keyword.length);
    }
});

// Fecha o easter egg ao clicar fora da imagem
document.getElementById('easterEgg').addEventListener('click', function (event) {
    if (event.target === this) {
        this.classList.add('hidden');
    }
});


function openModal() {
    var modal = document.getElementById('registerModal');
    modal.style.display = 'flex';
    showForm('empresa'); 
}

function closeModal() {
    var modal = document.getElementById('registerModal');
    modal.style.display = 'none';
}


function showForm(formType) {
    var empresaForm = document.getElementById('empresaForm');
    var usuarioForm = document.getElementById('usuarioForm');
    var buttons = document.getElementsByClassName('tab-button');

    if (formType === 'empresa') {
        empresaForm.classList.add('show');
        usuarioForm.classList.remove('show');
        empresaForm.classList.remove('hidden');
        usuarioForm.classList.add('hidden');
        buttons[0].classList.add('active');
        buttons[1].classList.remove('active');
    } else {
        usuarioForm.classList.add('show');
        empresaForm.classList.remove('show');
        usuarioForm.classList.remove('hidden');
        empresaForm.classList.add('hidden');
        buttons[1].classList.add('active');
        buttons[0].classList.remove('active');
    }
}

window.onclick = function(event) {
    var modal = document.getElementById('registerModal');
    if (event.target === modal) {
        closeModal();
    }
}



    </script>
</body>
</html>