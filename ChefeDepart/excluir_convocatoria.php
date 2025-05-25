<?php
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

$id = $_SESSION['id_userChefe'];

// Verificar se o ID da convocatória foi fornecido
if(!isset($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID da convocatória não fornecido']);
    exit();
}

$convocatoriaId = (int)$_POST['id'];

// Verificar se a convocatória pertence ao departamento do chefe
$sqlVerificar = "SELECT c.* FROM convocatorias c
                 JOIN departamentos d ON c.Id_departamento = d.id
                 WHERE c.Id = ? AND d.Id_Chefe = ?";

$stmt = $mysqli->prepare($sqlVerificar);
$stmt->bind_param("ii", $convocatoriaId, $id);
$stmt->execute();

if($stmt->get_result()->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Convocatória não encontrada ou sem permissão']);
    exit();
}

// Começar transação
$mysqli->begin_transaction();

try {
    // Excluir registros relacionados primeiro
    $sqlDeleteParticipantes = "DELETE FROM convocatoria_participantes WHERE Id_convocatoria = ?";
    $stmt = $mysqli->prepare($sqlDeleteParticipantes);
    $stmt->bind_param("i", $convocatoriaId);
    $stmt->execute();

    $sqlDeleteAnexos = "DELETE FROM convocatoria_anexos WHERE Id_convocatoria = ?";
    $stmt = $mysqli->prepare($sqlDeleteAnexos);
    $stmt->bind_param("i", $convocatoriaId);
    $stmt->execute();

    $sqlDeleteNotificacoes = "DELETE FROM notificacoes WHERE Id_origem = ? AND Tipo_origem = 'Convocatoria'";
    $stmt = $mysqli->prepare($sqlDeleteNotificacoes);
    $stmt->bind_param("i", $convocatoriaId);
    $stmt->execute();

    // Por fim, excluir a convocatória
    $sqlDeleteConvocatoria = "DELETE FROM convocatorias WHERE Id = ?";
    $stmt = $mysqli->prepare($sqlDeleteConvocatoria);
    $stmt->bind_param("i", $convocatoriaId);
    $stmt->execute();

    // Commit da transação
    $mysqli->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback em caso de erro
    $mysqli->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao excluir convocatória: ' . $e->getMessage()]);
} 