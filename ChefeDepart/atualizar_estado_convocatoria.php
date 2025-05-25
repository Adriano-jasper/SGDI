<?php
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

$id = $_SESSION['id_userChefe'];

if(!isset($_POST['id']) || !isset($_POST['estado'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit();
}

$convocatoriaId = (int)$_POST['id'];
$estado = $_POST['estado'];

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
    // Atualizar estado da convocatória
    $sqlUpdate = "UPDATE convocatorias SET Estado = ? WHERE Id = ?";
    $stmt = $mysqli->prepare($sqlUpdate);
    $stmt->bind_param("si", $estado, $convocatoriaId);
    $stmt->execute();

    // Atualizar confirmação dos participantes
    $novoEstadoParticipante = ($estado === 'Cancelada') ? 'Recusado' : 
                             (($estado === 'Realizada') ? 'Confirmado' : 'Pendente');
    
    $sqlUpdateParticipantes = "UPDATE convocatoria_participantes 
                              SET Confirmacao = ?, 
                                  Data_confirmacao = CURRENT_TIMESTAMP 
                              WHERE Id_convocatoria = ?";
    $stmt = $mysqli->prepare($sqlUpdateParticipantes);
    $stmt->bind_param("si", $novoEstadoParticipante, $convocatoriaId);
    $stmt->execute();

    // Notificar participantes
    $sqlNotificar = "INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
                     SELECT 
                         CONCAT('A convocatória foi ', ?, ': ', c.Titulo),
                         'Convocatoria',
                         'Pendente',
                         ?,
                         c.Id,
                         'Convocatoria',
                         cp.Id_usuario
                     FROM convocatorias c
                     JOIN convocatoria_participantes cp ON c.Id = cp.Id_convocatoria
                     WHERE c.Id = ?";

    $stmt = $mysqli->prepare($sqlNotificar);
    $stmt->bind_param("sii", $estado, $id, $convocatoriaId);
    $stmt->execute();

    // Commit da transação
    $mysqli->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback em caso de erro
    $mysqli->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao atualizar estado: ' . $e->getMessage()]);
} 