<?php

require_once 'conexão.php';

    #sessão
session_start();

if(isset($_POST['enviar'])):
    
    
    $email = mysqli_escape_string($mysqli,$_POST['email']);
    $senha = mysqli_escape_string($mysqli,$_POST['senha']);

    
        $sql = "SELECT Email FROM usuario WHERE Email = '$email'";
        $resultado = mysqli_query($mysqli, $sql); 
 
        if(mysqli_num_rows($resultado) > 0):
            $sql = "SELECT * FROM usuario WHERE Email = '$email' AND Senha = '$senha'";
            $resultado = mysqli_query($mysqli, $sql);

            if(mysqli_num_rows($resultado) == 1):

                $dados = mysqli_fetch_array($resultado); 
                

                    if($dados['Permissao'] == '0'){  
                        $_SESSION['logado'] = true;
                        $_SESSION['id_Admin'] = $dados['Id'];
                        header('Location:Admin/index.php');
                     
                    }else{ 
                        if($dados['Permissao'] == '1'){  
                
                            $_SESSION['logado'] = true;
                            $_SESSION['id_user'] = $dados['Id'];
                            header('Location:Usuário/index.php');
                        }
                        
                        
                        if($dados['Permissao'] == '2'){
                            $_SESSION['logado'] = true;
                            $_SESSION['id_userChefe'] = $dados['Id'];
                            header('Location:ChefeDepart/index.php'); 
                        }
                    }


                     endif;
            endif;    
            
        endif;         

?>
 