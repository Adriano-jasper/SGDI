<?php
include 'conexão.php';

if(isset($_POST['upload'])):
    $nome = mysqli_escape_string($mysqli,$_POST['nome']);
    $descricao = mysqli_escape_string($mysqli,$_POST['descricao']);
    $extensao = pathinfo($_FILES['Documento']['name'], PATHINFO_EXTENSION);

    $query = mysqli_query( $mysqli, "INSERT INTO Documentos (Titulo, Descricao) values ('$nome','$descricao')");

$pasta = "../Arquivos/";
$temporario = $_FILES['Documento']['tmp_name'];
$novoNome = uniqid().".$extensao";

move_uploaded_file($temporario, $pasta.$novoNome);


endif;
?>