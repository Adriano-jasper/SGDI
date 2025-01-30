<?php 
include 'conexão.php';
if(isset($_POST['criar'])){

    $dir = $_POST['nameDir'];
    
    if(mkdir('Diretorios/'.$dir,0755, true)){

    
    $desc = $_POST['DescDir'];
    $chefe = $_POST['chefeDep'];


    $query = mysqli_query( $mysqli, "INSERT INTO departamentos (Nome, Descricao, chefeDep) values ('$dir','$desc','$chefeDep')");
    header('Location:ListarDeprt.php');}else{
        header('Location:ListarDeprt.php');
    }
    
 }




?>