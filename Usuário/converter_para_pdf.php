<?php
function convertToPDF($inputFile, $outputDirectory) {
    // Verificar se o arquivo existe
    if (!file_exists($inputFile)) {
        return [
            'success' => false,
            'error' => 'Arquivo não encontrado'
        ];
    }

    // Gerar nome do arquivo PDF
    $fileInfo = pathinfo($inputFile);
    $pdfFileName = $fileInfo['filename'] . '_preview.pdf';
    $outputFile = $outputDirectory . '/' . $pdfFileName;

    // Comando para converter usando LibreOffice (deve estar instalado no servidor)
    $command = "soffice --headless --convert-to pdf:writer_pdf_Export --outdir " . 
               escapeshellarg($outputDirectory) . " " . 
               escapeshellarg($inputFile) . " 2>&1";

    // Executar comando
    exec($command, $output, $returnCode);

    // Verificar se a conversão foi bem sucedida
    if ($returnCode === 0 && file_exists($outputFile)) {
        return [
            'success' => true,
            'pdf_path' => $pdfFileName
        ];
    }

    return [
        'success' => false,
        'error' => 'Erro na conversão: ' . implode("\n", $output)
    ];
}

// Função para verificar se é um documento Office
function isOfficeDocument($filename) {
    $officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $officeExtensions);
}
?> 