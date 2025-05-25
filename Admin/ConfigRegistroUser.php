<?php
$mensagem = [];

if(isset($_POST['cadastrar'])) {
    $nome = mysqli_escape_string($mysqli, $_POST['nome']);
    $email = mysqli_escape_string($mysqli, $_POST['email']);
    $senha = mysqli_escape_string($mysqli, $_POST['senha']);
    $telefone = mysqli_escape_string($mysqli, $_POST['telefone']);
    $genero = mysqli_escape_string($mysqli, $_POST['gender']);
    $permissao = mysqli_escape_string($mysqli, $_POST['chefe']);
    
    // Inserir o usuário
    $query = mysqli_query($mysqli, "INSERT INTO usuario (Nome, Email, Senha, Telefone, Permissao, Genero) 
                                  VALUES ('$nome','$email','$senha','$telefone','$permissao','$genero')");
     
    if($query) {  
        // Obter o ID do usuário recém-cadastrado
        $id_usuario = mysqli_insert_id($mysqli);
        
        // Obter todas as permissões disponíveis
        $permissoes_query = mysqli_query($mysqli, "SELECT Id FROM permissoes");
        
        // Associar cada permissão ao novo usuário
        $erro_permissao = false;
        while($permissao = mysqli_fetch_assoc($permissoes_query)) {
            $insert_perm = mysqli_query($mysqli, 
                "INSERT INTO usuario_permissoes (Id_usuario, Id_permissao, Estado) 
                 VALUES ('$id_usuario', '{$permissao['Id']}', 1)");
            
            if(!$insert_perm) {
                $erro_permissao = true;
            }
        }
        
        if($erro_permissao) {
            $mensagem = array("Usuário cadastrado, mas houve erro ao associar algumas permissões.");
        } else {
            $mensagem = array("Usuário cadastrado com sucesso e todas as permissões associadas.");
        }
    } else { 
        $mensagem = array("Erro ao cadastrar o usuário.");
    }   
}
?>