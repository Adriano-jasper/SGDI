<?php
 session_start();
 
require_once 'conexão.php';

if(isset($_POST['cadastrar']))
    {
        $nome = mysqli_escape_string($mysqli,$_POST['nome']);
        $email = mysqli_escape_string($mysqli,$_POST['email']);
        $senha = mysqli_escape_string($mysqli,$_POST['senha']);
        $telefone = mysqli_escape_string($mysqli,$_POST['telefone']);
        $genero = mysqli_escape_string($mysqli,$_POST['gender']);
        $permissao = mysqli_escape_string($mysqli,$_POST['chefe']);
        
        $query = mysqli_query( $mysqli, "INSERT INTO usuario (Nome, Email, Senha, Telefone, Permissao, Genero) values ('$nome','$email','$senha','$telefone','$permissao','$genero')");
     
            if($query):
                $_SESSION['mensagem'] = "Cadastrado com sucesso";
                header('Location:ListarUser.php');         
            else:
                $_SESSION['mensagem'] = "erro ao cadastrar";
                header('Location:ListarUser.php');    
            endif;
            }

    
    

?> 