<?php

 session_start();

require_once 'conexão.php';


if(isset($_POST['deletar']))
    {

        $id = mysqli_escape_string($mysqli, $_POST['id']);     
    
        $query = mysqli_query( $mysqli, "DELETE FROM usuario where id = '$id'");
     
            if($query):
                $_SESSION['mensagem'] = "deletado com sucesso";
                header('Location:ListarUser.php');         
            else:
                $_SESSION['mensagem'] = "erro ao deletar";
                header('Location:ListarUser.php');    
            endif;
            }

    
    

?> 