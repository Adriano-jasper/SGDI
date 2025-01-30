<?php

 session_start();

require_once 'conexão.php';


if(isset($_POST['Editar']))
    {

        $nome = mysqli_escape_string($mysqli,$_POST['nome']);
        $email = mysqli_escape_string($mysqli,$_POST['email']);
        $senha = mysqli_escape_string($mysqli,$_POST['senha']);
        $telefone = mysqli_escape_string($mysqli,$_POST['telefone']);
        $genero = mysqli_escape_string($mysqli,$_POST['gender']);
        $permissao = mysqli_escape_string($mysqli,$_POST['chefe']);
        $id = mysqli_escape_string($mysqli, $_POST['id']);     
    
        $query = mysqli_query( $mysqli, "UPDATE usuario SET Nome='$nome',Email='$email',Senha='$senha',Telefone='$telefone',Genero='$genero',Permissao ='$permissao' WHERE id ='$id' ");
     
            if($query):
                $_SESSION['mensagem'] = "actualizado com sucesso";
                header('Location:ListarUser.php');         
            else:
                $_SESSION['mensagem'] = "erro ao actualizar";
                header('Location:ListarUser.php');    
            endif;
            }

    
    

?> 