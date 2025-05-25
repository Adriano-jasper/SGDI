<?php
include 'conexão.php';

$mensagem=[];
 if(isset($_GET['id'])):
    $idUsuario = mysqli_escape_string($mysqli,$_GET['id']);

 
if(isset($_POST['upload'])):
    $nome = mysqli_escape_string($mysqli,$_POST['nome']);
    $descricao = mysqli_escape_string($mysqli,$_POST['descricao']);
    $extensao = pathinfo($_FILES['Documento']['name'], PATHINFO_EXTENSION);

    
$pasta = "../Arquivos/";
$temporario = $_FILES['Documento']['tmp_name'];
$novoNome = uniqid().".$extensao";

    move_uploaded_file($temporario, $pasta.$novoNome);
    
    $query = mysqli_query( $mysqli, "INSERT INTO Documentos (Titulo, Descricao, Id_usuario, Caminho_Doc) values ('$nome','$descricao','$idUsuario','$novoNome')");
if($query):    
    $mensagem =array( "Upload feito com sucesso.");
endif;
    endif;
endif;
?>