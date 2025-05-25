<?php 
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_userChefe'];
$mensagem = '';

// Buscar dados do chefe e departamento logo no início
$sql = "SELECT u.*, d.id as id_departamento, d.Nome as nome_departamento 
        FROM usuario u 
        LEFT JOIN departamentos d ON d.Id_Chefe = u.Id 
        WHERE u.Id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$dados = $stmt->get_result()->fetch_assoc();

if (!$dados || !$dados['id_departamento']) {
    $mensagem = '<div class="alert alert-danger">Erro: Você não está associado a nenhum departamento como chefe.</div>';
} else {
    // Debug - Ver se o POST está chegando
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('POST recebido: ' . print_r($_POST, true));
    }

    // Processar formulários
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['acao'])) {
            switch ($_POST['acao']) {
                case 'nova_convocatoria':
                    try {
                        // Validar dados
                        if (empty($_POST['titulo']) || empty($_POST['descricao']) || empty($_POST['data']) || empty($_POST['local'])) {
                            throw new Exception('Todos os campos obrigatórios devem ser preenchidos.');
                        }

                        $titulo = mysqli_real_escape_string($mysqli, $_POST['titulo']);
                        $descricao = mysqli_real_escape_string($mysqli, $_POST['descricao']);
                        $data = $_POST['data'];
                        $local = mysqli_real_escape_string($mysqli, $_POST['local']);
                        
                        // Inserir convocatória
                        $sql = "INSERT INTO convocatorias (Titulo, Descricao, Data, Local, Id_departamento, Id_criador, Estado) 
                                VALUES (?, ?, ?, ?, ?, ?, 'Agendada')";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("ssssii", $titulo, $descricao, $data, $local, $dados['id_departamento'], $id);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao criar convocatória: " . $mysqli->error);
                        }
                        
                        $convocatoriaId = $mysqli->insert_id;
                        
                        // Adicionar participantes
                        if (isset($_POST['membros']) && is_array($_POST['membros'])) {
                            $sqlParticipante = "INSERT INTO convocatoria_participantes (Id_convocatoria, Id_usuario, Confirmacao) 
                                              VALUES (?, ?, 'Pendente')";
                            $stmt = $mysqli->prepare($sqlParticipante);
                            foreach ($_POST['membros'] as $membroId) {
                                $stmt->bind_param("ii", $convocatoriaId, $membroId);
                                $stmt->execute();
                            }
                        }
                        
                        $mensagem = '<div class="alert alert-success">Convocatória criada com sucesso!</div>';
                        header("Location: Convocatorias.php");
                        exit();
                        
                    } catch (Exception $e) {
                        $mensagem = '<div class="alert alert-danger">Erro: ' . $e->getMessage() . '</div>';
                    }
                    break;

                case 'cancelar':
                    try {
                        $convocatoriaId = (int)$_POST['id'];
                        
                        // Atualizar estado
                        $sql = "UPDATE convocatorias SET Estado = 'Cancelada' WHERE Id = ? AND Id_departamento = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("ii", $convocatoriaId, $dados['id_departamento']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao cancelar convocatória: " . $mysqli->error);
                        }
                        
                        // Atualizar participantes
                        $sql = "UPDATE convocatoria_participantes SET Confirmacao = 'Recusado', Data_confirmacao = NOW() WHERE Id_convocatoria = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("i", $convocatoriaId);
                        $stmt->execute();
                        
                        $mensagem = '<div class="alert alert-success">Convocatória cancelada com sucesso!</div>';
                        header("Location: Convocatorias.php");
                        exit();
                        
                    } catch (Exception $e) {
                        $mensagem = '<div class="alert alert-danger">Erro: ' . $e->getMessage() . '</div>';
                    }
                    break;

                case 'excluir':
                    try {
                        $convocatoriaId = (int)$_POST['id'];
                        
                        $sql = "DELETE FROM convocatorias WHERE Id = ? AND Id_departamento = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("ii", $convocatoriaId, $dados['id_departamento']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao excluir convocatória: " . $mysqli->error);
                        }
                        
                        $mensagem = '<div class="alert alert-success">Convocatória excluída com sucesso!</div>';
                        header("Location: Convocatorias.php");
                        exit();
                        
                    } catch (Exception $e) {
                        $mensagem = '<div class="alert alert-danger">Erro: ' . $e->getMessage() . '</div>';
                    }
                    break;
            }
        }
    }

    // Buscar convocatórias
    $sqlConvocatorias = "SELECT c.*, COUNT(cp.Id) as total_participantes 
                         FROM convocatorias c 
                         LEFT JOIN convocatoria_participantes cp ON c.Id = cp.Id_convocatoria 
                         WHERE c.Id_departamento = ? 
                         GROUP BY c.Id 
                         ORDER BY c.Data DESC";
    $stmt = $mysqli->prepare($sqlConvocatorias);
    $stmt->bind_param("i", $dados['id_departamento']);
    $stmt->execute();
    $resultadoConvocatorias = $stmt->get_result();

    // Buscar membros do departamento
    $sqlMembros = "SELECT Id, Nome FROM usuario WHERE Id_Departamento = ? AND Id != ?";
    $stmt = $mysqli->prepare($sqlMembros);
    $stmt->bind_param("ii", $dados['id_departamento'], $id);
    $stmt->execute();
    $resultadoMembros = $stmt->get_result();
}

// Se receber uma requisição AJAX para detalhes
if(isset($_GET['action']) && $_GET['action'] == 'detalhes' && isset($_GET['id'])) {
    $convocatoriaId = (int)$_GET['id'];
    
    // Buscar detalhes da convocatória
    $sqlDetalhes = "SELECT c.*, d.Nome as Departamento
                    FROM convocatorias c
                    JOIN departamentos d ON c.Id_departamento = d.id
                    WHERE c.Id = ? AND d.Id_Chefe = ?";
    
    $stmt = $mysqli->prepare($sqlDetalhes);
    $stmt->bind_param("ii", $convocatoriaId, $id);
    $stmt->execute();
    $convocatoria = $stmt->get_result()->fetch_assoc();
    
    if($convocatoria) {
        // Buscar participantes
        $sqlParticipantes = "SELECT u.Nome, cp.Confirmacao, cp.Data_confirmacao
                            FROM convocatoria_participantes cp
                            JOIN usuario u ON cp.Id_usuario = u.Id
                            WHERE cp.Id_convocatoria = ?";
        
        $stmt = $mysqli->prepare($sqlParticipantes);
        $stmt->bind_param("i", $convocatoriaId);
        $stmt->execute();
        $participantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Buscar anexos
        $sqlAnexos = "SELECT Id, Nome_arquivo, Caminho_arquivo, Tipo_arquivo
                      FROM convocatoria_anexos
                      WHERE Id_convocatoria = ?";
        
        $stmt = $mysqli->prepare($sqlAnexos);
        $stmt->bind_param("i", $convocatoriaId);
        $stmt->execute();
        $anexos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Formatar data
        $data = new DateTime($convocatoria['Data']);
        $convocatoria['Data_formatada'] = $data->format('d/m/Y H:i');
        
        // Adicionar participantes e anexos ao resultado
        $convocatoria['participantes'] = $participantes;
        $convocatoria['anexos'] = $anexos;
        $convocatoria['total_participantes'] = count($participantes);
        $convocatoria['total_anexos'] = count($anexos);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'convocatoria' => $convocatoria]);
        exit();
    }
}

// Se receber uma requisição AJAX para atualizar estado
if(isset($_POST['action']) && $_POST['action'] == 'atualizar_estado' && isset($_POST['id']) && isset($_POST['estado'])) {
    $convocatoriaId = (int)$_POST['id'];
    $estado = $_POST['estado'];
    
    if(!in_array($estado, ['Realizada', 'Cancelada'])) {
        echo json_encode(['error' => 'Estado inválido']);
        exit();
    }
    
    // Verificar se a convocatória pertence ao departamento do chefe
    $sqlVerificar = "SELECT c.* FROM convocatorias c
                     JOIN departamentos d ON c.Id_departamento = d.id
                     WHERE c.Id = ? AND d.Id_Chefe = ?";
    
    $stmt = $mysqli->prepare($sqlVerificar);
    $stmt->bind_param("ii", $convocatoriaId, $id);
    $stmt->execute();
    
    if($stmt->get_result()->num_rows > 0) {
        // Atualizar estado
        $sqlUpdate = "UPDATE convocatorias SET Estado = ? WHERE Id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        $stmt->bind_param("si", $estado, $convocatoriaId);
        
        if($stmt->execute()) {
            // Notificar participantes
            $sqlNotificar = "INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
                            SELECT 
                                CONCAT('A convocatória foi marcada como ', ?, ': ', c.Titulo),
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
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar estado']);
        }
    } else {
        echo json_encode(['error' => 'Convocatória não encontrada ou sem permissão']);
    }
    exit();
}

// Se receber uma requisição AJAX para adiar
if(isset($_POST['action']) && $_POST['action'] == 'adiar' && isset($_POST['id']) && isset($_POST['nova_data']) && isset($_POST['motivo'])) {
    $convocatoriaId = (int)$_POST['id'];
    $novaData = $_POST['nova_data'];
    $motivo = $_POST['motivo'];
    
    // Verificar se a convocatória pertence ao departamento do chefe
    $sqlVerificar = "SELECT c.* FROM convocatorias c
                     JOIN departamentos d ON c.Id_departamento = d.id
                     WHERE c.Id = ? AND d.Id_Chefe = ? AND c.Estado = 'Agendada'";
    
    $stmt = $mysqli->prepare($sqlVerificar);
    $stmt->bind_param("ii", $convocatoriaId, $id);
    $stmt->execute();
    $convocatoria = $stmt->get_result()->fetch_assoc();
    
    if($convocatoria) {
        // Validar nova data
        $dataConvocatoria = new DateTime($novaData);
        $agora = new DateTime();
        
        if($dataConvocatoria <= $agora) {
            echo json_encode(['error' => 'A nova data deve ser no futuro']);
            exit();
        }
        
        // Atualizar data
        $sqlUpdate = "UPDATE convocatorias SET Data = ? WHERE Id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        $stmt->bind_param("si", $novaData, $convocatoriaId);
        
        if($stmt->execute()) {
            // Notificar participantes
            $sqlNotificar = "INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
                            SELECT 
                                CONCAT('A convocatória \"', c.Titulo, '\" foi adiada.\nNova data: ', 
                                      DATE_FORMAT(?, '%d/%m/%Y %H:%i'),
                                      '\nMotivo: ', ?),
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
            $stmt->bind_param("ssii", $novaData, $motivo, $id, $convocatoriaId);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao adiar convocatória']);
        }
    } else {
        echo json_encode(['error' => 'Convocatória não encontrada ou sem permissão']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Convocatórias</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/convocatoria.js"></script>
</head>
<body class="app sidebar-mini">
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
        <!--Notification Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i></a>
          <ul class="app-notification dropdown-menu dropdown-menu-right">
            <li class="app-notification__title">Notificações</li>
            <div class="app-notification__content">
            </div>
            <li class="app-notification__footer"><a href="#">Ver todas as notificações</a></li>
          </ul>
        </li>
        <!-- User Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
          <ul class="dropdown-menu settings-menu dropdown-menu-right">
            <li><a class="dropdown-item" href="EditPerfiluser.php"><i class="fa fa-user fa-lg"></i> Perfil</a></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out fa-lg"></i> Sair</a></li>
          </ul>
        </li>
      </ul>
    </header>
    
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name"><?php echo $dados['Nome']; ?></p>
          <p class="app-sidebar__user-designation">Chefe de Departamento</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
        
        <li class="treeview is-expanded">
          <a class="app-menu__item" href="#" data-toggle="treeview">
            <i class="app-menu__icon fa fa-building"></i>
            <span class="app-menu__label">Meu Departamento</span>
            <i class="treeview-indicator fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="listarMembros.php"><i class="icon fa fa-users"></i> Membros</a></li>
            <li><a class="treeview-item" href="DocsCompartilhados.php"><i class="icon fa fa-files-o"></i> Documentos</a></li>
            <li><a class="treeview-item active" href="Convocatorias.php"><i class="icon fa fa-bullhorn"></i> Convocatórias</a></li>
          </ul>
        </li>
        
        <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
        <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
    </aside>

    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-bullhorn"></i> Convocatórias</h1>
          <p>Gerencie as convocatórias do departamento <?php echo htmlspecialchars($dados['nome_departamento']); ?></p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">Meu Departamento</li>
          <li class="breadcrumb-item"><a href="#">Convocatórias</a></li>
        </ul>
      </div>

      <?php if($mensagem): ?>
        <?php echo $mensagem; ?>
      <?php endif; ?>

      <?php if ($dados && $dados['id_departamento']): ?>
      <!-- Botão para Nova Convocatória -->
      <div class="row">
        <div class="col-md-12">
          <div class="tile">
            <div class="tile-title-w-btn">
              <h3 class="title">Convocatórias</h3>
              <button class="btn btn-primary" data-toggle="modal" data-target="#novaConvocatoriaModal">
                <i class="fa fa-plus"></i> Nova Convocatória
              </button>
            </div>
            
            <!-- Lista de Convocatórias -->
            <div class="tile-body">
              <div class="table-responsive">
                <table class="table table-hover table-bordered" id="tabelaConvocatorias">
                  <thead>
                    <tr>
                      <th>Título</th>
                      <th>Data/Hora</th>
                      <th>Local</th>
                      <th>Estado</th>
                      <th>Participantes</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($conv = $resultadoConvocatorias->fetch_assoc()): 
                        $data = new DateTime($conv['Data']);
                        $dataFormatada = $data->format('d/m/Y H:i');
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($conv['Titulo']); ?></td>
                      <td><?php echo $dataFormatada; ?></td>
                      <td><?php echo htmlspecialchars($conv['Local']); ?></td>
                      <td><?php echo $conv['Estado']; ?></td>
                      <td><?php echo $conv['total_participantes']; ?></td>
                      <td>
                        <?php if($conv['Estado'] === 'Agendada'): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="acao" value="cancelar">
                            <input type="hidden" name="id" value="<?php echo $conv['Id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja cancelar esta convocatória?')">
                                <i class="fa fa-times"></i> Cancelar
                            </button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?php echo $conv['Id']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta convocatória? Esta ação não pode ser desfeita.')">
                                <i class="fa fa-trash"></i> Excluir
                            </button>
                        </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Nova Convocatória -->
      <div class="modal fade" id="novaConvocatoriaModal" tabindex="-1" role="dialog" aria-labelledby="novaConvocatoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="novaConvocatoriaModalLabel">Nova Convocatória</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="nova_convocatoria">
                <div class="form-group">
                  <label for="titulo">Título da Convocatória *</label>
                  <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                  <label for="descricao">Descrição *</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="data">Data e Hora *</label>
                  <input type="datetime-local" class="form-control" id="data" name="data" required>
                </div>
                <div class="form-group">
                  <label for="local">Local *</label>
                  <input type="text" class="form-control" id="local" name="local" required>
                </div>
                <div class="form-group">
                  <label>Participantes *</label>
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="todosMembrosDept" name="todosMembrosDept">
                    <label class="custom-control-label" for="todosMembrosDept">Todos os membros do departamento</label>
                  </div>
                  <div id="listaMembros" class="mt-3">
                    <?php
                    while($membro = $resultadoMembros->fetch_assoc()): ?>
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input membro-checkbox" 
                               id="membro_<?php echo $membro['Id']; ?>" 
                               name="membros[]" 
                               value="<?php echo $membro['Id']; ?>">
                        <label class="custom-control-label" for="membro_<?php echo $membro['Id']; ?>">
                          <?php echo htmlspecialchars($membro['Nome']); ?>
                        </label>
                      </div>
                    <?php endwhile; ?>
                  </div>
                </div>
                <div class="form-group">
                  <label for="anexos">Anexos</label>
                  <input type="file" class="form-control-file" id="anexos" name="anexos[]" multiple>
                  <small class="form-text text-muted">Você pode selecionar múltiplos arquivos</small>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Salvar Convocatória</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Detalhes -->
      <div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Detalhes da Convocatória</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="detalhesConvocatoria">
                <!-- Os detalhes serão carregados aqui -->
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
              <button type="button" class="btn btn-warning" id="btnAdiar" style="display: none;">Adiar</button>
              <button type="button" class="btn btn-danger" id="btnCancelar" style="display: none;">Cancelar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Adiamento -->
      <div class="modal fade" id="modalAdiar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Adiar Convocatória</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form id="formAdiar">
                <input type="hidden" id="convocatoriaId" name="convocatoriaId">
                <div class="form-group">
                  <label for="novaData">Nova Data e Hora *</label>
                  <input type="datetime-local" class="form-control" id="novaData" name="novaData" required>
                </div>
                <div class="form-group">
                  <label for="motivoAdiamento">Motivo do Adiamento *</label>
                  <textarea class="form-control" id="motivoAdiamento" name="motivoAdiamento" rows="3" required></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-primary" onclick="confirmarAdiamento()">Confirmar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Confirmação de Realização -->
      <div class="modal fade" id="modalConfirmacaoRealizacao" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirmação de Realização</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>A convocatória foi realizada conforme programado?</p>
              <form id="formConfirmacaoRealizacao">
                <input type="hidden" id="convocatoriaIdConfirmacao" name="convocatoriaId">
                <div class="form-group">
                  <label for="observacoes">Observações (opcional)</label>
                  <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" onclick="naoRealizada()">Não Realizada</button>
              <button type="button" class="btn btn-success" onclick="confirmarRealizacao()">Sim, Realizada</button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </main>

    <!-- Essential javascripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    
    <script>
    // Marcar/desmarcar todos os membros
    document.getElementById('todosMembrosDept').addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('membro-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });

    // Configurar o campo de data/hora com o mínimo sendo agora
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    const dataMinima = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('data').min = dataMinima;

    // Recarregar a página a cada 5 minutos
    setInterval(function() {
        window.location.reload();
    }, 300000);
    </script>
</body>
</html> 