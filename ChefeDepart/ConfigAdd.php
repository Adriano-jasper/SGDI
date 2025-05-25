<?php
include_once 'conexão.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['userId'];
    $departmentId = $_POST['departmentId'];
    
    // Atualiza o departamento do usuário
    $sql = "UPDATE usuario SET Id_Departamento = ? WHERE Id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $departmentId, $userId);
    
    if ($stmt->execute()) {
        header('Location: listarMembros.php?success=1');
        exit;
    } else {
        header('Location: listarMembros.php?error=1');
        exit;
    }
}
?>