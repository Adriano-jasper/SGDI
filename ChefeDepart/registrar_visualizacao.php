<?php
session_start();
require 'conexão.php';

if (!isset($_SESSION['logado'])) {
    die(json_encode(['status' => 'error', 'message' => 'Não autorizado']));
}

$id_usuario = $_SESSION['id_userChefe'];
$id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;

if ($id_documento <= 0) {
    die(json_encode(['status' => 'error', 'message' => 'ID do documento inválido']));
}

// Verificar se o usuário já visualizou este documento recentemente (para evitar múltiplos registros)
$sql_verifica = "SELECT * FROM historico_visualizacao 
                 WHERE id_usuario = ? AND id_documento = ? 
                 AND data_visualizacao > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
$stmt = $mysqli->prepare($sql_verifica);
$stmt->bind_param("ii", $id_usuario, $id_documento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Registrar nova visualização
    $sql = "INSERT INTO historico_visualizacao (id_usuario, id_documento) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_documento);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar visualização']);
    }
} else {
    echo json_encode(['status' => 'success', 'message' => 'Visualização já registrada recentemente']);
}
?>