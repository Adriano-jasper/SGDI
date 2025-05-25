<?php
include_once 'conexao.php';

session_start();

if(!isset($_SESSION['logado']) || !isset($_POST['userId'])) {
    die("0");
}

$userId = $_POST['userId'];

// Contar notificações não visualizadas
$sql = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$userId' AND Visualizada = 0";
$result = mysqli_query($mysqli, $sql);
$row = mysqli_fetch_assoc($result);

echo $row['total'];
?>






