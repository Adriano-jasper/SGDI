<?php
include "conexão.php"; 
if(isset($_POST['resetar'])){   
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Verifica se o email existe no banco de dados
    $query = $mysqli->prepare("SELECT Id FROM usuario WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        // Gera um token único
        $token = bin2hex(random_bytes(32)); // Token seguro de 64 caracteres

        // Define o tempo de expiração (1 hora a partir de agora)
        $expira_em = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Armazena o token no banco de dados
        $stmt = $mysqli->prepare("INSERT INTO password_reset_tokens (email, token, expira_em) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expira_em);
        $stmt->execute();

        // Envia o email com o link de reset de senha
        $reset_link = "http://seusite.com/ResetSenha.php?token=" . $token;
        $to = $email;
        $subject = "Reset de Senha";
        $message = "Clique no link abaixo para resetar sua senha:\n\n" . $reset_link . "\n\nEste link expira em 1 hora.";
        $headers = "From: no-reply@seusite.com";

        if (mail($to, $subject, $message, $headers)) {
            echo "Um email com instruções para resetar sua senha foi enviado.";
        } else {
            echo "Erro ao enviar o email.";
        }
    } else {
        echo "Usuario não encontrado.";
    }
} }
?>