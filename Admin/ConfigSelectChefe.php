<?php

session_start();

require_once 'conexão.php';


if(isset($_GET['idDep'])):
    $idDepart = mysqli_escape_string($mysqli, $_GET['idDep']);
  
  
if(isset($_POST['AdicionarChefe']))
   {

       $id= mysqli_escape_string($mysqli, $_POST['id']);     
   
       $query = mysqli_query( $mysqli, "UPDATE departamentos SET Id_Chefe='$id' WHERE id ='$idDepart' ");

           if($query):
            $query = mysqli_query( $mysqli, "UPDATE usuario SET Permissao='2', Id_Departamento='$idDepart' WHERE id ='$id' ");
               $_SESSION['mensagem'] = "actualizado com sucesso";
               header('Location:ListarDeprt.php');         
           else:
               $_SESSION['mensagem'] = "erro ao actualizar";
               header('Location:ListarDeprt.php');    
           endif;
           }
        endif;

   
   

?> 


