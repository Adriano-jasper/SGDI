<?php
include_once 'conexão.php';
// ConfigSolicitacao.php
session_start();
if (!isset($_SESSION['logado'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificacao_id'], $_POST['acao'], $_POST['documento_id'])) {
    $notif_id = $_POST['notificacao_id'];
    $action = $_POST['acao'];
    $doc_id = $_POST['documento_id'];
    $chefe_id = $_SESSION['id_userChefe'];
    
    // Verificar se a notificação pertence ao chefe
    $query = "SELECT * FROM notificacoes WHERE Id = ? AND Id_usuario = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $notif_id, $chefe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
        exit();
    }
    
    $notificacao = $result->fetch_assoc();
    
    // Atualizar estado da notificação
    $query = "UPDATE notificacoes SET Estado = ? WHERE Id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $action, $notif_id);
    $stmt->execute();
    
    // Obter informações do documento
    $query = "SELECT d.Titulo, d.Id_usuario, u.Nome 
              FROM documentos d
              JOIN usuario u ON d.Id_usuario = u.Id
              WHERE d.Id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documento = $result->fetch_assoc();



 // Ação de rejeição
 $descricao = "Sua solicitação de partilha do documento '" . 
 htmlspecialchars($documento['Titulo']) . "' foi negada.";

$query = "INSERT INTO notificacoes 
(Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem) 
VALUES (?, 'Aprovacao', 'Negada', ?, ?, 'Documento')";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sii', $descricao, $documento['Id_usuario'], $doc_id);
$stmt->execute();

// Registrar fluxo de trabalho
$query = "INSERT INTO fluxo_trabalho 
(Id_documento, Id_de, Id_para, Tipo_destino, Acao, Comentario) 
VALUES (?, ?, (SELECT id FROM departamentos WHERE Id_Chefe = ?), 'Departamento', 'Rejeitar', 'Solicitação negada')";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('iii', $doc_id, $chefe_id, $chefe_id);
$stmt->execute();

// Atualizar estado do documento
$query = mysqli_query( $mysqli, "UPDATE documentos SET Estado='Rejeitado' WHERE Id ='$doc_id' ");

// Atualizar descrição da notificação do chefe
$nova_desc = "Negaste a partilha do documento: " . htmlspecialchars($documento['Titulo']);
$query = "UPDATE notificacoes SET Descricao = ? WHERE Id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('si', $nova_desc, $notif_id);
$stmt->execute();

echo json_encode(['success' => true]);
header('Location:Notificacoes.php');
}


?>