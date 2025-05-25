<?php 

$mensagem=[];
if(isset($_POST['criar'])){

    $dir = $_POST['nameDir'];
    
    if(mkdir('Diretorios/'.$dir,0755, true)){

    
    $desc = $_POST['DescDir'];
    
    $sql_insert = "INSERT INTO departamentos (Nome, Descricao) VALUES ('$dir', '$desc')";
    if (mysqli_query($mysqli, $sql_insert)) {
        $mensagem =array( "Departamento Cadastrado com sucesso.");
    } 
} else {
    $mensagem= array("Erro ao registrar o departamento.");}
}

?>