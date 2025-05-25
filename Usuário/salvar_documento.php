<?php
session_start();
require 'conexão.php';

if (!isset($_SESSION['logado'])) {
    http_response_code(403);
    exit('Não autorizado');
}

// Receber dados do OnlyOffice
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    exit('Dados inválidos');
}

// Verificar status
switch ($data['status']) {
    case 0: // Documento ainda está sendo editado
        http_response_code(200);
        echo json_encode(array("error" => 0));
        break;

    case 1: // Documento pronto para ser salvo
        $documentUrl = $data['url'];
        $fileName = basename($documentUrl);
        $filePath = "../Arquivos/" . $fileName;

        // Baixar novo conteúdo
        $newContent = file_get_contents($data['url']);
        if ($newContent === false) {
            http_response_code(500);
            exit('Erro ao baixar documento');
        }

        // Salvar arquivo
        if (file_put_contents($filePath, $newContent) === false) {
            http_response_code(500);
            exit('Erro ao salvar documento');
        }

        // Se for documento Office, atualizar versão PDF
        if (isOfficeDocument($fileName)) {
            require_once 'converter_para_pdf.php';
            $resultado = convertToPDF($filePath, "../Arquivos/");
            if ($resultado['success']) {
                // Atualizar caminho do PDF no banco
                $sql = "UPDATE documentos SET Caminho_PDF = ? WHERE Caminho_Doc = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("ss", $resultado['pdf_path'], $fileName);
                $stmt->execute();
            }
        }

        http_response_code(200);
        echo json_encode(array("error" => 0));
        break;

    case 2: // Documento foi editado, mas ainda não está salvo
        http_response_code(200);
        echo json_encode(array("error" => 0));
        break;

    default:
        http_response_code(400);
        echo json_encode(array("error" => "Status desconhecido"));
        break;
}

// Função auxiliar
function isOfficeDocument($filename) {
    $officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $officeExtensions);
}
?> 