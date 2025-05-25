<?php
include 'conexão.php';
if (isset($_GET['id'])) {
$id = mysqli_escape_string($mysqli,$_GET['id']);


$sql="SELECT * FROM documentos WHERE  id ='$id'";
$resultado = mysqli_query($mysqli,$sql);
$dados = mysqli_fetch_array($resultado);

$file_name = $dados['Caminho_Doc'];

    $caminho_completo ="../Arquivos/".$dados['Caminho_Doc'];
    if (file_exists($caminho_completo)) {
        if (unlink($caminho_completo)) { 

            $query = mysqli_query( $mysqli, "DELETE FROM documentos where id = '$id'");
            if($query):
            $mensagem= array("Arquivo deletado com sucesso");   
            header('Location:meusUploads.PHP');
            endif;
        } else {
            $mensagem= array("erro ao deletar documento"); 
            header('Location:meusUploads.PHP');
        }
    } else {
        $mensagem= array("Arquivo $file_name não encontrado.<br>");
    }
}


?>