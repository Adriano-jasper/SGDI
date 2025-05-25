<?php
include_once 'conexão.php';
session_start();

if (!isset($_SESSION['logado'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$chefe_id = $_SESSION['id_userChefe'];

// Obter o departamento onde o usuário é chefe
$query = "SELECT d.id AS departamento_id, d.Nome AS departamento_nome 
          FROM departamentos d 
          WHERE d.Id_Chefe = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $chefe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Apenas chefes de departamento podem aprovar solicitações']);
    exit();
}

$departamento_chefe = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificacao_id'], $_POST['acao'], $_POST['documento_id'] )) {
    $notif_id = $_POST['notificacao_id'];
    $action = $_POST['acao'];
    $doc_id = $_POST['documento_id'];
    $destino = $_POST['Destino'];
    
    // Obter detalhes da notificação
    $query = "SELECT n.*, u.Nome AS usuario_nome, u.Email AS usuario_email, 
                     d.Nome AS departamento_nome, d.Id_Chefe, d.id AS departamento_id
              FROM notificacoes n
              JOIN usuario u ON n.Id_usuario = u.Id
              LEFT JOIN departamentos d ON u.Id_Departamento = d.id
              WHERE n.Id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $notif_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
        exit();
    }
    
    $notificacao = $result->fetch_assoc();
    
    // Obter detalhes do documento
    $query = "SELECT d.*, u.Nome AS autor_nome, u.Email AS autor_email,
                     ud.id AS autor_departamento_id, ud.Nome AS autor_departamento_nome,
                     ud.Id_Chefe AS autor_departamento_chefe
              FROM documentos d
              JOIN usuario u ON d.Id_usuario = u.Id
              LEFT JOIN departamentos ud ON u.Id_Departamento = ud.id
              WHERE d.Id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documento = $result->fetch_assoc();
    
    if (!$documento) {
        echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
        exit();
    }
    
    if ($action === 'Aceite') {
        // Verificar se é uma solicitação de partilha entre departamentos
        if ($notificacao['Tipo'] == 'Partilha') {
            // Obter o chefe do departamento de destino 
            $chefe_destino_id = $destino;
            $departamento_destino_nome = $departamento_chefe['departamento_nome'];
            
            // Obter o chefe do departamento de origem
            $chefe_origem_id = $documento['autor_departamento_chefe'];
            
            // Criar notificação detalhada para o chefe de destino (usuário atual)
            $descricao = sprintf(
                "SOLICITAÇÃO DE PARTILHA De documento\n\n".
                "Documento: %s\n".
                "Autor: %s (%s)\n".
                "Departamento de Origem: %s\n".
                "Chefe de Origem: %s\n".
                "Ação Requerida: Por favor, revise e aprove esta solicitação.",
                htmlspecialchars($documento['Titulo']),
                htmlspecialchars($documento['autor_nome']),
                htmlspecialchars($documento['autor_email']),
                htmlspecialchars($documento['autor_departamento_nome']),
                htmlspecialchars($notificacao['usuario_nome'])
            );
            
            // Atualizar notificação atual
            $query = "UPDATE notificacoes SET Descricao = ?, Estado = 'Pendente', Id_usuario = ?, Visualizada = 0 WHERE Id ='$notif_id' ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('si', $descricao, $chefe_destino_id);
            $stmt->execute();
            
            // Registrar fluxo de trabalho
            $query = "INSERT INTO fluxo_trabalho 
                      (Id_documento, Id_de, Id_para, Tipo_destino, Acao, Comentario) 
                      VALUES (?, ?, ?, 'Usuario', 'Enviar', ?)";
            $comentario = sprintf("Aprovado por %s - Enviado para chefe de %s",
                htmlspecialchars($notificacao['usuario_nome']),
                htmlspecialchars($departamento_destino_nome)
            );
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('iiis', $doc_id, $chefe_id, $chefe_destino_id, $comentario);
            $stmt->execute();
            
            // Notificar o autor original
            $descricao_autor = sprintf(
                "Sua solicitação para o departamento %s foi aprovada por %s e encaminhada para o chefe do departamento de destino.",
                htmlspecialchars($departamento_destino_nome),
                htmlspecialchars($notificacao['usuario_nome'])
            );
            
            $query = "INSERT INTO notificacoes 
                      (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem) 
                      VALUES (?, 'Aprovacao', 'Pendente', ?, ?, 'Documento')";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sii', $descricao_autor, $documento['Id_usuario'], $doc_id);
            $stmt->execute();
            
            // Redirecionar para Notificacoes.php em caso de sucesso
            header('Location: Notificacoes.php?status=success');
            exit();
        }
        
        // Processar aprovação normal (dentro do mesmo departamento)
        $query = "SELECT Caminho_Doc FROM documentos WHERE Id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $doc_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doc_info = $result->fetch_assoc();
        
        $diretorio_destino = '../Admin/Diretorios/'.$departamento_chefe['departamento_nome'].'/'.$doc_info['Caminho_Doc'];
        
        // Copiar arquivo para o diretório de destino
        $origem = '../Arquivos/' . $doc_info['Caminho_Doc'];
        if (copy($origem, $diretorio_destino)) {
            // Atualizar status do documento
            $query = "UPDATE documentos SET Estado='Aprovado' WHERE Id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $doc_id);
            $stmt->execute();
            
            // Associar documento ao departamento
            $query = "INSERT INTO documento_departamento (Id_documento, Id_departamento) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $doc_id, $departamento_chefe['departamento_id']);
            $stmt->execute();
            
            // Registrar fluxo de trabalho
            $query = "INSERT INTO fluxo_trabalho 
                      (Id_documento, Id_de, Id_para, Tipo_destino, Acao, Comentario) 
                      VALUES (?, ?, ?, 'Departamento', 'Aprovar', ?)";
            $comentario = "Documento aprovado e compartilhado com departamento";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('iiis', $doc_id, $chefe_id, $departamento_chefe['departamento_id'], $comentario);
            $stmt->execute();
            
            // Notificar membros do departamento (exceto o chefe)
            $query = "SELECT Id FROM usuario WHERE Id_Departamento = ? AND Id != ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $departamento_chefe['departamento_id'], $chefe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $descricao_membros = sprintf(
                "NOVO DOCUMENTO COMPARTILHADO\n\n".
                "Departamento: %s\n".
                "Aprovado por: %s\n\n".
                "Este documento foi compartilhado com todo o departamento.",
                htmlspecialchars($departamento_chefe['departamento_nome']),
                htmlspecialchars($notificacao['usuario_nome'])
            );
            
            while ($membro = $result->fetch_assoc()) {
                $query_notif = "INSERT INTO notificacoes 
                                (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem) 
                                VALUES (?, 'Documento', 'Aceite', ?, ?, 'Documento')";
                $stmt_notif = $mysqli->prepare($query_notif);
                $stmt_notif->bind_param('sii', $descricao_membros, $membro['Id'], $doc_id);
                $stmt_notif->execute();
            }
            
            // Notificar o autor original
            $descricao_autor = sprintf(
                "Sua solicitação de partilha do documento '%s' foi aprovada por %s.",
                htmlspecialchars($documento['Titulo']),
                htmlspecialchars($notificacao['usuario_nome'])
            );
            
            $query = "INSERT INTO notificacoes 
                      (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem) 
                      VALUES (?, 'Aprovacao', 'Aceite', ?, ?, 'Documento')";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sii', $descricao_autor, $documento['Id_usuario'], $doc_id);
            $stmt->execute();
            
        
            
        } else {
            // Redirecionar com mensagem de erro se a cópia falhar
            header('Location: Notificacoes.php?status=error&message=Erro ao copiar arquivo');
            exit();
        }
    }   
    
    // Atualizar descrição da notificação do chefe
    $nova_desc = sprintf(
        "Você %s a solicitação de partilha do documento: %s\nSolicitante: %s\nData: %s",
        ($action === 'Aceite') ? 'aprovou' : 'rejeitou',
        htmlspecialchars($documento['Titulo']),
        htmlspecialchars($documento['autor_nome']),
        date('d/m/Y H:i')
    );
    
    $query = "UPDATE notificacoes SET Descricao = ?, Estado ='Aceite' WHERE Id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $nova_desc, $notif_id);
    $stmt->execute();
    
    // Redirecionar para Notificacoes.php em caso de sucesso
    header('Location: Notificacoes.php?status=success');
    exit();
} else {
    // Redirecionar com mensagem de erro se a requisição for inválida
    header('Location: Notificacoes.php?status=error&message=Requisição inválida');
    exit();
}