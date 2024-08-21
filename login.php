<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_usuario = $_POST['email'];
    $senha_usuario = $_POST['senha'];

    try {
        
        $isEmpresa = false;

       
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email_usuario = :email");
        $stmt->execute(['email' => $email_usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM empresas WHERE email_de_trabalho = :email");
            $stmt->execute(['email' => $email_usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $isEmpresa = true;
        }

        if ($user) {
            $hashedPassword = $isEmpresa ? $user['senha_empresa'] : $user['senha_usuario'];
        
            if (password_verify($senha_usuario, $hashedPassword) || $senha_usuario === $hashedPassword) {
                $_SESSION['email'] = $isEmpresa ? $user['email_de_trabalho'] : $user['email_usuario'];
                $_SESSION['role'] = $user['tipo'];
                $_SESSION['user_id'] = $isEmpresa ? $user['ID_empresas'] : $user['id_usuario'];
        
                if ($user['tipo'] === 'admin') {
                    header("Location: dashboard_admin.php");
                } elseif ($user['tipo'] === 'empresa') {
                    header("Location: dashboard_empresa.php");
                } elseif ($user['tipo'] === 'cliente') {
                    header("Location: feed.php");
                } else {
                    echo "Tipo de usuário desconhecido.";
                }
                exit();
            } else {
                echo "Senha incorreta.";
            }
        } else {
            echo "Usuário não encontrado.";
        }
        
    } catch (PDOException $e) {
        echo "Erro ao acessar o banco de dados: " . $e->getMessage();
    }
}
?>
