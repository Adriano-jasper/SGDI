<?php
session_start();
require 'conexão.php';

if (!isset($_SESSION['logado']) || !isset($_POST['documento_id'])) {
    http_response_code(400);
    exit;
}

$user_id = $_SESSION['id_user'];
$doc_id = $_POST['documento_id'];

// Registrar visualização
$stmt = $mysqli->prepare("INSERT INTO historico_visualizacao (id_usuario, id_documento) VALUES (?, ?)");
$stmt->bind_param('ii', $user_id, $doc_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>