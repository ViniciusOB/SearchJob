<?php
// Arquivo de configuração seguro (config.php)

// Retorna um array com as configurações do projeto
return [
    // E-mail de suporte
    'support_email' => 'suporte.searchjob@gmail.com',

    // Configurações do servidor SMTP
    'smtp_host' => 'smtp.seudominio.com',   // Endereço do servidor SMTP
    'smtp_username' => 'usuario_smtp',      // Nome de usuário do servidor SMTP
    'smtp_password' => 'senha_secreta',     // Senha do servidor SMTP (use uma senha forte)
    'smtp_port' => 587,                     // Porta usada para o envio (geralmente 587 para TLS, 465 para SSL)
    'smtp_secure' => 'tls',                 // Tipo de criptografia (tls ou ssl)

    // Outras configurações adicionais (opcional)
    'site_name' => 'SearchJob',             // Nome do seu site ou projeto
    'base_url' => 'https://www.seudominio.com',  // URL base do site
];
