<?php
session_start();
include 'conexao.php';

// Verificar se é uma empresa logada
if (!isset($_SESSION['email'])) {
    echo "Usuário não autenticado.";
    exit();
}

$email_empresa = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_pic'])) {
        $profile_pic = $_FILES['profile_pic'];
        $profile_pic_name = time() . '_' . $profile_pic['name'];
        $profile_pic_target = 'profile_pics/' . $profile_pic_name;

        if (move_uploaded_file($profile_pic['tmp_name'], $profile_pic_target)) {
            $sql = "UPDATE empresas SET profile_pic = :profile_pic WHERE email_de_trabalho = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['profile_pic' => $profile_pic_name, 'email' => $email_empresa]);
            echo "Imagem de perfil atualizada.";
        }
    }

    if (isset($_FILES['banner_empresa'])) {
        $banner = $_FILES['banner_empresa'];
        $banner_name = time() . '_' . $banner['name'];
        $banner_target = 'banner_empresa/' . $banner_name;

        if (move_uploaded_file($banner['tmp_name'], $banner_target)) {
            $sql = "UPDATE empresas SET banner_empresa = :banner_empresa WHERE email_de_trabalho = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['banner_empresa' => $banner_name, 'email' => $email_empresa]);
            echo "Banner atualizado.";
        }
    }
}
?>
