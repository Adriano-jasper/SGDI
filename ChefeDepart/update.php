<?php

 session_start();

require_once 'conexão.php';

if(isset($_GET['btn-cadastrar']))
    {
        $nome = mysqli_escape_string($mysqli,$_POST['nome']);
        $sobrenome = mysqli_escape_string($mysqli,$_POST['sobrenome']);
        $idade= mysqli_escape_string($mysqli,$_POST['idade']);
        $bairro= mysqli_escape_string($mysqli,$_POST['bairro']);
        $id= mysqli_escape_string($mysqli,$_POST['id']);
       
        $query = mysqli_query( $mysqli, "UPDATE perdido SET nome='$nome',sobrenome='$sobrenome',idade='$idade',bairroResidente='$bairro' WHERE id ='$id' ");
     
            if($query):
                $_SESSION['mensagem'] = "actualizado com sucesso";
                header('Location:listar.php');         
            else:
                $_SESSION['mensagem'] = "erro ao actualizar";
                header('Location:listar.php');    
            endif;
            }

    
    

?> 