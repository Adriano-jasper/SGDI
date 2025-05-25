<?php
include_once 'conexao.php';

header('Content-Type: application/json');

// Verificar se o ID do documento foi fornecido
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do documento não fornecido']);
    exit;
}

$id = $_GET['id'];

// Consulta segura com prepared statements
$sql = "SELECT Titulo, Caminho_Doc, Descricao FROM documentos WHERE Id = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $documento = $result->fetch_assoc();
    
    // Verificar se o caminho do documento existe
    if (!file_exists($documento['Caminho_Doc'])) {
        echo json_encode(['success' => false, 'message' => 'Arquivo físico não encontrado no servidor']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'titulo' => $documento['Titulo'],
        'caminho' => $documento['Caminho_Doc'],
        'descricao' => $documento['Descricao']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Documento não encontrado no banco de dados']);
}

$stmt->close();
$mysqli->close();
?>