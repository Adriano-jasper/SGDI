<?php

 session_start();

require_once 'conexão.php';


if(isset($_POST['Editar']))
    {

        $nome = mysqli_escape_string($mysqli,$_POST['nome']);
        $email = mysqli_escape_string($mysqli,$_POST['email']);
        $senha = mysqli_escape_string($mysqli,$_POST['senha']);
        $telefone = mysqli_escape_string($mysqli,$_POST['telefone']);
        $id = mysqli_escape_string($mysqli, $_POST['id']);   
        
        $query = mysqli_query( $mysqli, "UPDATE usuario SET Nome='$nome',Email='$email',Senha='$senha',Telefone='$telefone' WHERE Id ='$id' ");
     
        if(isset($_FILES['imagem'])){      
            $pasta = "../Usuário/Fotos/";
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);  
            $novoNome = uniqid().".$extensao";
            $temporario = $_FILES['imagem']['tmp_name'];
            move_uploaded_file($temporario, $pasta.$novoNome);
            $query = mysqli_query( $mysqli, "UPDATE usuario SET Caminho_da_Ft='$novoNome' WHERE Id ='$id' ");
                    }
            
           
        if($query):
               
            $_SESSION['mensagem'] = "actualizado com sucesso";
                header('Location:EditPerfiluser.php');         
            else:
                $_SESSION['mensagem'] = "erro ao actualizar";
                header('Location:EditPerfiluser.php');    
            endif;
  
    }     

?> 