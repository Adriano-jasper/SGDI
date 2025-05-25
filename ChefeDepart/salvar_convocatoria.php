<?php
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

// Verificar se todos os campos necessários foram enviados
if(!isset($_POST['titulo']) || !isset($_POST['descricao']) || !isset($_POST['data']) || !isset($_POST['local'])) {
    echo json_encode(['error' => 'Todos os campos são obrigatórios']);
    exit();
}

$id = $_SESSION['id_userChefe'];

// Obter ID do departamento do chefe
$sqlDepartamento = "SELECT id FROM departamentos WHERE Id_Chefe = '$id'";
$resultadoDepartamento = mysqli_query($mysqli, $sqlDepartamento);

if(!$resultadoDepartamento || mysqli_num_rows($resultadoDepartamento) == 0) {
    echo json_encode(['error' => 'Departamento não encontrado']);
    exit();
}

$departamento = mysqli_fetch_assoc($resultadoDepartamento)['id'];

// Validar e sanitizar dados do formulário
$titulo = mysqli_real_escape_string($mysqli, trim($_POST['titulo']));
$descricao = mysqli_real_escape_string($mysqli, trim($_POST['descricao']));
$data = mysqli_real_escape_string($mysqli, trim($_POST['data']));
$local = mysqli_real_escape_string($mysqli, trim($_POST['local']));
$membros = isset($_POST['membros']) ? $_POST['membros'] : [];

// Validar se os campos obrigatórios não estão vazios
if(empty($titulo) || empty($descricao) || empty($data) || empty($local)) {
    echo json_encode(['error' => 'Todos os campos são obrigatórios']);
    exit();
}

// Validar a data e hora
$dataConvocatoria = new DateTime($data);
$agora = new DateTime();

if($dataConvocatoria < $agora) {
    echo json_encode(['error' => 'A data e hora da convocatória não podem ser no passado']);
    exit();
}

// Inserir convocatória
$sqlConvocatoria = "INSERT INTO convocatorias (Titulo, Descricao, Data, Local, Id_departamento, Id_criador, Data_criacao) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $mysqli->prepare($sqlConvocatoria);
$stmt->bind_param("ssssii", $titulo, $descricao, $data, $local, $departamento, $id);

if($stmt->execute()) {
    $id_convocatoria = $mysqli->insert_id;
    
    // Se selecionou todos os membros
    if(isset($_POST['todosMembrosDept']) && $_POST['todosMembrosDept'] == 'true') {
        $sqlTodosMembros = "INSERT INTO convocatoria_participantes (Id_convocatoria, Id_usuario)
                           SELECT ?, Id FROM usuario 
                           WHERE Id_Departamento = ? AND Id != ?";
        $stmtTodos = $mysqli->prepare($sqlTodosMembros);
        $stmtTodos->bind_param("iii", $id_convocatoria, $departamento, $id);
        $stmtTodos->execute();
    }
    // Se selecionou membros específicos
    else if(!empty($membros)) {
        $sqlParticipante = "INSERT INTO convocatoria_participantes (Id_convocatoria, Id_usuario) VALUES (?, ?)";
        $stmtPart = $mysqli->prepare($sqlParticipante);
        
        foreach($membros as $membro) {
            $membro = (int)$membro;
            $stmtPart->bind_param("ii", $id_convocatoria, $membro);
            $stmtPart->execute();
        }
    }
    
    // Processar anexos
    if(isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
        $uploadDir = '../uploads/convocatorias/';
        
        if(!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $anexos = $_FILES['anexos'];
        $totalFiles = count($anexos['name']);
        
        for($i = 0; $i < $totalFiles; $i++) {
            if($anexos['error'][$i] == 0) {
                $fileName = uniqid() . '_' . basename($anexos['name'][$i]);
                $targetPath = $uploadDir . $fileName;
                
                if(move_uploaded_file($anexos['tmp_name'][$i], $targetPath)) {
                    $sqlAnexo = "INSERT INTO convocatoria_anexos 
                                (Id_convocatoria, Nome_arquivo, Caminho_arquivo, Tipo_arquivo, Tamanho) 
                                VALUES (?, ?, ?, ?, ?)";
                    $stmtAnexo = $mysqli->prepare($sqlAnexo);
                    $stmtAnexo->bind_param("isssi", 
                        $id_convocatoria, 
                        $anexos['name'][$i], 
                        $fileName, 
                        $anexos['type'][$i], 
                        $anexos['size'][$i]
                    );
                    $stmtAnexo->execute();
                }
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Convocatória criada com sucesso']);
} else {
    echo json_encode(['error' => 'Erro ao criar convocatória: ' . $mysqli->error]);
}
?> 