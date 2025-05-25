<?php
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

$id = $_SESSION['id_userChefe'];

// Buscar departamento do chefe
$sqlDepartamento = "SELECT d.id FROM departamentos d WHERE d.Id_Chefe = ?";
$stmt = $mysqli->prepare($sqlDepartamento);
$stmt->bind_param("i", $id);
$stmt->execute();
$departamento = $stmt->get_result()->fetch_assoc();

if(!$departamento) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Departamento não encontrado']);
    exit();
}

// Buscar convocatórias que estão próximas (dentro de 30 minutos) e ainda não foram confirmadas
$sqlConvocatorias = "SELECT c.* 
                     FROM convocatorias c
                     WHERE c.Id_departamento = ?
                     AND c.Estado = 'Agendada'
                     AND c.Data BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                     AND NOT EXISTS (
                         SELECT 1 
                         FROM notificacoes n 
                         WHERE n.Id_origem = c.Id 
                         AND n.Tipo = 'Convocatoria'
                         AND n.Descricao LIKE '%confirmação de realização%'
                         AND n.Data > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                     )";

$stmt = $mysqli->prepare($sqlConvocatorias);
$stmt->bind_param("i", $departamento['id']);
$stmt->execute();
$resultado = $stmt->get_result();

$convocatorias_proximas = [];
while($conv = $resultado->fetch_assoc()) {
    // Criar notificação de confirmação
    $sqlNotificacao = "INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
                       VALUES (?, 'Convocatoria', 'Pendente', ?, ?, 'Convocatoria', ?)";
    
    $descricao = "Solicitação de confirmação de realização da convocatória: " . $conv['Titulo'];
    $stmt = $mysqli->prepare($sqlNotificacao);
    $stmt->bind_param("siis", $descricao, $id, $conv['Id'], $id);
    $stmt->execute();
    
    $conv['confirmacao_solicitada'] = true;
    $convocatorias_proximas[] = $conv;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'convocatorias_proximas' => $convocatorias_proximas
]); 