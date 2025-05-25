<?php
include 'conexão.php';

if (isset($_GET['id'])) {
    $id = mysqli_escape_string($mysqli,$_GET['id']);

    // Busca o caminho do arquivo no banco de dados
    $sql="SELECT Caminho_Doc FROM documentos WHERE  id ='$id'";
          $resultado = mysqli_query($mysqli,$sql);
          $dados = mysqli_fetch_array($resultado);

        $file_path = '../Arquivos';
        $file_name = $dados['Caminho_Doc'];

        // Verifica se o arquivo existe
        if (file_exists($file_path)) {
            // Define os headers para forçar o download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "Arquivo não encontrado.";
        }
    } else {
        echo "Documento não encontrado.";
    }

?>