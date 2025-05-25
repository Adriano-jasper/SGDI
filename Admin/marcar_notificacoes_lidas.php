<?php
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado']) || !isset($_POST['userId'])) {
    die("Acesso não autorizado");
}

$userId = $_POST['userId'];

// Atualiza todas as notificações do usuário como visualizadas
$sql = "UPDATE notificacoes SET Visualizada = 1 WHERE Id_usuario = '$userId' AND Visualizada = 0";
if(mysqli_query($mysqli, $sql)) {
    echo "success";
} else {
    echo "error";
}
?>