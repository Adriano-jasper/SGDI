<?php
include 'conexão.php';


if (isset($_POST['id'])) {
    $id = mysqli_escape_string($mysqli, $_POST['id']);

    $sql = "SELECT * FROM documentos WHERE id = '$id'";
    $resultado = mysqli_query($mysqli, $sql);
    $dados = mysqli_fetch_array($resultado);

    if ($dados) {
        $file_name = $dados['Caminho_Doc'];
        $caminho_completo = "../Arquivos/" . $dados['Caminho_Doc'];
        
        if (file_exists($caminho_completo)) {
            if (unlink($caminho_completo)) {
                $query = mysqli_query($mysqli, "DELETE FROM documentos WHERE id = '$id'");
                if ($query) {
                    $_SESSION['sucesso'] = "Arquivo deletado com sucesso";
                } else {
                    $_SESSION['erro'] = "Erro ao deletar registro do banco de dados";
                }
            } else {
                $_SESSION['erro'] = "Erro ao deletar arquivo físico";
            }
        } else {
            // Se o arquivo físico não existe, ainda assim tenta deletar o registro
            $query = mysqli_query($mysqli, "DELETE FROM documentos WHERE id = '$id'");
            if ($query) {
                $_SESSION['sucesso'] = "Registro deletado com sucesso";
            } else {
                $_SESSION['erro'] = "Erro ao deletar registro do banco de dados";
            }
        }
    } else {
        $_SESSION['erro'] = "Documento não encontrado";
    }

    header('Location: meusUploads.php');
    exit;
}
?>