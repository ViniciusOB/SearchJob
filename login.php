<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_usuario = $_POST['email'];
    $senha_usuario = $_POST['senha'];

    try {
        $isEmpresa = false;
        $isFuncionario = false;

        // Verifica na tabela de usuarios
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email_usuario = :email");
        $stmt->execute(['email' => $email_usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não encontrar na tabela de usuarios, verifica na tabela de empresas
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM empresas WHERE email_de_trabalho = :email");
            $stmt->execute(['email' => $email_usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $isEmpresa = $user ? true : false;
        }

        // Se não encontrar na tabela de empresas, verifica na tabela de funcionarios
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE email_funcionario = :email");
            $stmt->execute(['email' => $email_usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $isFuncionario = $user ? true : false;
        }

        if ($user) {
            // Verifica a senha com base na tabela de origem
            $hashedPassword = $isEmpresa ? $user['senha_empresa'] : ($isFuncionario ? $user['senha_funcionario'] : $user['senha_usuario']);

            if (password_verify($senha_usuario, $hashedPassword) || $senha_usuario === $hashedPassword) {
                // Define a sessão e o redirecionamento com base na tabela de origem
                $_SESSION['email'] = $isEmpresa ? $user['email_de_trabalho'] : ($isFuncionario ? $user['email_funcionario'] : $user['email_usuario']);
                $_SESSION['role'] = $user['tipo'];

                // Armazena IDs separadamente
                if ($isEmpresa) {
                    $_SESSION['id_empresa'] = $user['ID_empresas'];
                } elseif ($isFuncionario) {
                    $_SESSION['id_funcionario'] = $user['id_funcionario'];
                } else {
                    $_SESSION['user_id'] = $user['id_usuario'];
                }

                // Redireciona o usuário para o dashboard apropriado
                if ($user['tipo'] === 'admin') {
                    header("Location: dashboard_admin.php");
                } elseif ($user['tipo'] === 'empresa') {
                    header("Location: dashboard_empresa.php");
                } elseif ($user['tipo'] === 'cliente') {
                    header("Location: feed.php");
                } elseif ($user['tipo'] === 'funcionario') {
                    header("Location: feed.php");
                } else {
                    header("Location: home.php?erro=3"); // Caso o tipo de usuário seja desconhecido
                }
                exit();
            } else {
                header("Location: home.php?erro=2"); // Senha incorreta
                exit();
            }
        } else {
            header("Location: home.php?erro=1"); // Usuário não encontrado
            exit();
        }
    } catch (PDOException $e) {
        header("Location: home.php?erro=3"); // Erro de banco de dados ou erro desconhecido
        exit();
    }
}
?>
