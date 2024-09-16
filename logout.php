<?php
// Iniciar a sessão
session_start();

// Encerrar a sessão (limpar todas as variáveis de sessão)
session_unset();

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: index.php");
exit();
?>
