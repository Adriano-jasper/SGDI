<?php
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

$id = $_SESSION['id_userChefe'];

// Obter ID do departamento do chefe
$sqlDepartamento = "SELECT id FROM departamentos WHERE Id_Chefe = '$id'";
$resultadoDepartamento = mysqli_query($mysqli, $sqlDepartamento);
$departamento = mysqli_fetch_assoc($resultadoDepartamento)['id'];

// Buscar membros do departamento
$sqlMembros = "SELECT Id, Nome FROM usuario WHERE Id_Departamento = '$departamento' AND Id != '$id' ORDER BY Nome";
$resultadoMembros = mysqli_query($mysqli, $sqlMembros);

while($membro = mysqli_fetch_assoc($resultadoMembros)) {
    echo '<div class="form-check">';
    echo '<input class="form-check-input membro-checkbox" type="checkbox" name="membros[]" value="' . $membro['Id'] . '" id="membro' . $membro['Id'] . '">';
    echo '<label class="form-check-label" for="membro' . $membro['Id'] . '">';
    echo htmlspecialchars($membro['Nome']);
    echo '</label>';
    echo '</div>';
}
?> 