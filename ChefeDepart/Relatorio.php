<?php 
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_userChefe'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dadosChefe = mysqli_fetch_assoc($resultado);

// Obter ID do departamento do chefe
$sqlDepartamento = "SELECT id FROM departamentos WHERE Id_Chefe = '$id'";
$resultadoDepartamento = mysqli_query($mysqli, $sqlDepartamento);
$departamento = mysqli_fetch_assoc($resultadoDepartamento)['id'];

// Processar filtros
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$tipo_relatorio = isset($_GET['tipo_relatorio']) ? $_GET['tipo_relatorio'] : 'geral';

// Relatório específico do departamento
$sqlDeptInfo = "SELECT 
    d.Nome as nome_departamento,
    d.Descricao,
    u.Nome as nome_chefe,
    COUNT(DISTINCT doc.Id) as total_documentos,
    COUNT(DISTINCT usr.Id) as total_usuarios,
    SUM(CASE WHEN doc.Estado = 'Aprovado' THEN 1 ELSE 0 END) as docs_aprovados,
    SUM(CASE WHEN doc.Estado = 'Pendente' THEN 1 ELSE 0 END) as docs_pendentes,
    SUM(CASE WHEN doc.Estado = 'Rejeitado' THEN 1 ELSE 0 END) as docs_rejeitados,
    AVG(TIMESTAMPDIFF(HOUR, doc.Data, doc.Data_aprovacao)) as tempo_medio_aprovacao,
    SUM(doc.Tamanho) as espaco_total
FROM departamentos d
LEFT JOIN usuario u ON d.Id_Chefe = u.Id
LEFT JOIN usuario usr ON usr.Id_Departamento = d.id
LEFT JOIN documento_departamento dd ON d.id = dd.Id_departamento
LEFT JOIN documentos doc ON dd.Id_documento = doc.Id
WHERE d.id = $departamento
GROUP BY d.id";

$resultDeptInfo = mysqli_query($mysqli, $sqlDeptInfo);
$deptInfo = mysqli_fetch_assoc($resultDeptInfo);

// Relatório de atividades do período
if($tipo_relatorio == 'atividades') {
    $sqlAtividades = "SELECT 
        ft.Acao,
        ft.Data,
        u_de.Nome as usuario_origem,
        d.Nome as departamento_destino,
        u_para.Nome as usuario_destino,
        doc.Titulo as documento,
        ft.Comentario
    FROM fluxo_trabalho ft
    JOIN usuario u_de ON ft.Id_de = u_de.Id
    LEFT JOIN usuario u_para ON ft.Id_para = u_para.Id AND ft.Tipo_destino = 'Usuario'
    LEFT JOIN departamentos d ON ft.Id_para = d.id AND ft.Tipo_destino = 'Departamento'
    JOIN documentos doc ON ft.Id_documento = doc.Id
    WHERE DATE(ft.Data) BETWEEN '$data_inicio' AND '$data_fim'
    AND (ft.Id_para = $departamento OR ft.Id_de IN (SELECT Id FROM usuario WHERE Id_Departamento = $departamento))
    ORDER BY ft.Data DESC";
    
    $resultAtividades = mysqli_query($mysqli, $sqlAtividades);
    $atividades = mysqli_fetch_all($resultAtividades, MYSQLI_ASSOC);
}

// Relatório de acessos
if($tipo_relatorio == 'acessos') {
    $sqlAcessos = "SELECT 
        a.Data,
        u.Nome as usuario,
        d.Nome as departamento,
        a.Tipo,
        a.Ip,
        a.Descricao
    FROM acessos a
    JOIN usuario u ON a.Id_usuario = u.Id
    LEFT JOIN departamentos d ON u.Id_Departamento = d.id
    WHERE DATE(a.Data) BETWEEN '$data_inicio' AND '$data_fim'
    AND u.Id_Departamento = $departamento
    ORDER BY a.Data DESC";
    
    $resultAcessos = mysqli_query($mysqli, $sqlAcessos);
    $acessos = mysqli_fetch_all($resultAcessos, MYSQLI_ASSOC);
}

// Relatório específico de usuários do departamento
if($tipo_relatorio == 'usuarios') {
    $sqlUsuarios = "SELECT 
        u.Nome,
        u.Email,
        u.Telefone,
        COUNT(DISTINCT d.Id) as total_documentos,
        SUM(CASE WHEN d.Estado = 'Aprovado' THEN 1 ELSE 0 END) as docs_aprovados,
        SUM(CASE WHEN d.Estado = 'Pendente' THEN 1 ELSE 0 END) as docs_pendentes,
        SUM(CASE WHEN d.Estado = 'Rejeitado' THEN 1 ELSE 0 END) as docs_rejeitados,
        MAX(d.Data) as ultima_atividade
    FROM usuario u
    LEFT JOIN documentos d ON u.Id = d.Id_usuario
    WHERE u.Id_Departamento = $departamento
    GROUP BY u.Id
    ORDER BY u.Nome";
    
    $resultUsuarios = mysqli_query($mysqli, $sqlUsuarios);
    $usuarios = mysqli_fetch_all($resultUsuarios, MYSQLI_ASSOC);
}

// Relatório detalhado de documentos
if($tipo_relatorio == 'documentos') {
    $sqlDocumentos = "SELECT 
        d.Titulo,
        d.Descricao,
        d.Data,
        d.Estado,
        d.Tipo,
        d.Tamanho,
        u.Nome as nome_usuario,
        ua.Nome as nome_aprovador,
        d.Data_aprovacao
    FROM documentos d
    JOIN documento_departamento dd ON d.Id = dd.Id_documento
    JOIN usuario u ON d.Id_usuario = u.Id
    LEFT JOIN usuario ua ON d.Id_aprovador = ua.Id
    WHERE dd.Id_departamento = $departamento
    ORDER BY d.Data DESC";
    
    $resultDocumentos = mysqli_query($mysqli, $sqlDocumentos);
    $documentos = mysqli_fetch_all($resultDocumentos, MYSQLI_ASSOC);
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

function formatarData($dataTimestamp) {
    if(empty($dataTimestamp)) return 'N/A';
    $data = new DateTime($dataTimestamp);
    return $data->format('d/m/Y H:i');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Relatórios do Departamento</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        .report-section {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .metric-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .metric-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-aprovado { background: #dff0d8; color: #3c763d; }
        .status-pendente { background: #fcf8e3; color: #8a6d3b; }
        .status-rejeitado { background: #f2dede; color: #a94442; }
        
        /* Estilos para o relatório em PDF */
        .report-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 30px;
        }
        .report-header img {
            height: 60px;
            margin-right: 20px;
        }
        .report-header h2 {
            margin: 0;
            color: #2c3e50;
        }
        .report-header p {
            margin: 5px 0 0;
            color: #7f8c8d;
        }
        .report-body {
            padding: 20px;
        }
        .report-footer {
            padding: 20px;
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
        @media print {
            .app-sidebar, .app-header, .btn-download {
                display: none !important;
            }
            .report-section {
                break-inside: avoid;
            }
        }
    </style>
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
          <p class="app-sidebar__user-name"><?php echo $dadosChefe['Nome']; ?></p>
          <p class="app-sidebar__user-designation">Chefe de Departamento</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
        
        <li class="treeview">
          <a class="app-menu__item" href="#" data-toggle="treeview">
            <i class="app-menu__icon fa fa-building"></i>
            <span class="app-menu__label">Meu Departamento</span>
            <i class="treeview-indicator fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="listarMembros.php"><i class="icon fa fa-users"></i> Membros</a></li>
            <li><a class="treeview-item" href="DocsCompartilhados.php"><i class="icon fa fa-files-o"></i> Documentos</a></li>
            <li><a class="treeview-item" href="Convocatorias.php"><i class="icon fa fa-bullhorn"></i> Convocatórias</a></li>
          </ul>
        </li>
        
        <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
        <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
        <li><a class="app-menu__item active" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
    </aside>

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text"></i> Relatórios do Departamento</h1>
                <p>Relatórios detalhados do departamento <?php echo htmlspecialchars($deptInfo['nome_departamento']); ?></p>
            </div>
        </div>

        <!-- Seção de Filtros -->
        <div class="tile">
            <h3 class="tile-title">Filtros do Relatório</h3>
            <form method="GET" class="row">
                <div class="col-md-3 form-group">
                    <label>Tipo de Relatório:</label>
                    <select name="tipo_relatorio" class="form-control" onchange="this.form.submit()">
                        <option value="geral" <?php echo $tipo_relatorio == 'geral' ? 'selected' : ''; ?>>Relatório Geral</option>
                        <option value="usuarios" <?php echo $tipo_relatorio == 'usuarios' ? 'selected' : ''; ?>>Relatório de Usuários</option>
                        <option value="documentos" <?php echo $tipo_relatorio == 'documentos' ? 'selected' : ''; ?>>Relatório de Documentos</option>
                        <option value="atividades" <?php echo $tipo_relatorio == 'atividades' ? 'selected' : ''; ?>>Relatório de Atividades</option>
                    </select>
                </div>

                <?php if(in_array($tipo_relatorio, ['atividades'])): ?>
                <div class="col-md-3 form-group">
                    <label>Data Início:</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo $data_inicio; ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label>Data Fim:</label>
                    <input type="date" name="data_fim" class="form-control" value="<?php echo $data_fim; ?>">
                </div>
                <?php endif; ?>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i> Aplicar Filtros
                    </button>
                    <a href="Relatorio.php" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i> Limpar Filtros
                    </a>
                </div>
            </form>
        </div>

        <!-- Conteúdo do Relatório -->
        <div id="report-content">
            <!-- Cabeçalho do Relatório -->
            <div class="report-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="../Usuário/Fotos/documento.png" alt="Logo PGDI">
                    </div>
                    <div class="col">
                        <h2>PGDI - Plataforma de Gestão Documental Integrada</h2>
                        <p>Relatório do Departamento: <?php echo htmlspecialchars($deptInfo['nome_departamento']); ?></p>
                        <p>Gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
                        <p>Gerado por: <?php echo htmlspecialchars($dadosChefe['Nome']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Relatório Geral do Departamento -->
            <?php if($tipo_relatorio == 'geral'): ?>
            <div class="report-section">
                <h4 class="mb-4">Relatório Detalhado do Departamento</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $deptInfo['total_usuarios']; ?></div>
                            <div class="metric-label">Total de Usuários</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $deptInfo['total_documentos']; ?></div>
                            <div class="metric-label">Total de Documentos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $deptInfo['docs_aprovados']; ?></div>
                            <div class="metric-label">Documentos Aprovados</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $deptInfo['docs_pendentes']; ?></div>
                            <div class="metric-label">Documentos Pendentes</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $deptInfo['docs_rejeitados']; ?></div>
                            <div class="metric-label">Documentos Rejeitados</div>
                        </div>
                    </div>
                </div>
                <div class="additional-info mt-4">
                    <p><strong>Chefe do Departamento:</strong> <?php echo htmlspecialchars($deptInfo['nome_chefe']); ?></p>
                    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($deptInfo['Descricao']); ?></p>
                    <p><strong>Tempo Médio de Aprovação:</strong> <?php echo round($deptInfo['tempo_medio_aprovacao'], 1); ?> horas</p>
                    <p><strong>Espaço Total Utilizado:</strong> <?php echo formatBytes($deptInfo['espaco_total']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Relatório de Atividades -->
            <?php if($tipo_relatorio == 'atividades' && !empty($atividades)): ?>
            <div class="report-section">
                <h4>Relatório de Atividades (<?php echo date('d/m/Y', strtotime($data_inicio)); ?> - <?php echo date('d/m/Y', strtotime($data_fim)); ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Documento</th>
                                <th>Destino</th>
                                <th>Comentário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($atividades as $atividade): ?>
                            <tr>
                                <td><?php echo formatarData($atividade['Data']); ?></td>
                                <td><?php echo htmlspecialchars($atividade['usuario_origem']); ?></td>
                                <td><?php echo htmlspecialchars($atividade['Acao']); ?></td>
                                <td><?php echo htmlspecialchars($atividade['documento']); ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($atividade['usuario_destino'] ?? $atividade['departamento_destino'] ?? 'N/A');
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($atividade['Comentario'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Relatório de Acessos -->
            <?php if($tipo_relatorio == 'acessos' && !empty($acessos)): ?>
            <div class="report-section">
                <h4>Relatório de Acessos (<?php echo date('d/m/Y', strtotime($data_inicio)); ?> - <?php echo date('d/m/Y', strtotime($data_fim)); ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>IP</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($acessos as $acesso): ?>
                            <tr>
                                <td><?php echo formatarData($acesso['Data']); ?></td>
                                <td><?php echo htmlspecialchars($acesso['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($acesso['Tipo']); ?></td>
                                <td><?php echo htmlspecialchars($acesso['Ip']); ?></td>
                                <td><?php echo htmlspecialchars($acesso['Descricao'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Relatório de Usuários -->
            <?php if($tipo_relatorio == 'usuarios' && !empty($usuarios)): ?>
            <div class="report-section">
                <h4>Relatório de Usuários do Departamento</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Total Documentos</th>
                                <th>Aprovados</th>
                                <th>Pendentes</th>
                                <th>Rejeitados</th>
                                <th>Última Atividade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['Nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Telefone']); ?></td>
                                <td><?php echo $usuario['total_documentos']; ?></td>
                                <td><span class="status-badge status-aprovado"><?php echo $usuario['docs_aprovados']; ?></span></td>
                                <td><span class="status-badge status-pendente"><?php echo $usuario['docs_pendentes']; ?></span></td>
                                <td><span class="status-badge status-rejeitado"><?php echo $usuario['docs_rejeitados']; ?></span></td>
                                <td><?php echo formatarData($usuario['ultima_atividade']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Relatório de Documentos -->
            <?php if($tipo_relatorio == 'documentos' && !empty($documentos)): ?>
            <div class="report-section">
                <h4>Relatório de Documentos do Departamento</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Data</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Tamanho</th>
                                <th>Criado por</th>
                                <th>Aprovado por</th>
                                <th>Data Aprovação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($documentos as $documento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($documento['Titulo']); ?></td>
                                <td><?php echo htmlspecialchars($documento['Descricao']); ?></td>
                                <td><?php echo formatarData($documento['Data']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($documento['Estado']); ?>">
                                        <?php echo $documento['Estado']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($documento['Tipo']); ?></td>
                                <td><?php echo formatBytes($documento['Tamanho']); ?></td>
                                <td><?php echo htmlspecialchars($documento['nome_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($documento['nome_aprovador'] ?? 'N/A'); ?></td>
                                <td><?php echo $documento['Data_aprovacao'] ? formatarData($documento['Data_aprovacao']) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rodapé do Relatório -->
            <div class="report-footer">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>PGDI - Sistema de Gestão Documental</strong></p>
                        <p>Versão 1.0</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <p>Página 1 de 1</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <p>Data de Emissão: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão de Download -->
        <div class="text-center mb-4">
            <button class="btn btn-primary btn-download" onclick="downloadPDF()">
                <i class="fa fa-download"></i> Baixar Relatório em PDF
            </button>
        </div>
    </main>

    <!-- Essential javascripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <!-- Script para download do PDF -->
    <script>
    function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt', 'a4');
        
        // Configurações do documento
        doc.setProperties({
            title: 'Relatório PGDI - <?php echo $deptInfo["nome_departamento"]; ?>',
            subject: 'Relatório do Departamento',
            author: '<?php echo htmlspecialchars($dadosChefe['Nome']); ?>',
            keywords: 'PGDI, relatório, documentos, departamento',
            creator: 'PGDI System'
        });

        // Adicionar cabeçalho
        doc.setFontSize(20);
        doc.text('PGDI - Relatório do Departamento', 40, 40);
        
        doc.setFontSize(12);
        doc.text(`Departamento: <?php echo $deptInfo['nome_departamento']; ?>`, 40, 60);
        doc.text(`Gerado em: ${new Date().toLocaleString()}`, 40, 80);
        doc.text(`Por: <?php echo htmlspecialchars($dadosChefe['Nome']); ?>`, 40, 100);

        // Adicionar conteúdo específico baseado no tipo de relatório
        let yPosition = 140;

        <?php if($tipo_relatorio == 'geral'): ?>
        // Dados do departamento
        const metrics = [
            ['Total de Usuários', '<?php echo $deptInfo['total_usuarios']; ?>'],
            ['Total de Documentos', '<?php echo $deptInfo['total_documentos']; ?>'],
            ['Documentos Aprovados', '<?php echo $deptInfo['docs_aprovados']; ?>'],
            ['Documentos Pendentes', '<?php echo $deptInfo['docs_pendentes']; ?>'],
            ['Documentos Rejeitados', '<?php echo $deptInfo['docs_rejeitados']; ?>'],
            ['Tempo Médio de Aprovação', '<?php echo round($deptInfo['tempo_medio_aprovacao'], 1); ?> horas'],
            ['Espaço Total Utilizado', '<?php echo formatBytes($deptInfo['espaco_total']); ?>']
        ];

        doc.autoTable({
            startY: yPosition,
            head: [['Métrica', 'Valor']],
            body: metrics,
            margin: { left: 40 },
            theme: 'grid'
        });
        <?php endif; ?>

        <?php if($tipo_relatorio == 'atividades' && !empty($atividades)): ?>
        // Tabela de atividades
        const activities = <?php echo json_encode(array_map(function($a) {
            return [
                formatarData($a['Data']),
                $a['usuario_origem'],
                $a['Acao'],
                $a['documento'],
                $a['usuario_destino'] ?? $a['departamento_destino'] ?? 'N/A',
                $a['Comentario'] ?? ''
            ];
        }, $atividades)); ?>;

        doc.autoTable({
            startY: yPosition,
            head: [['Data', 'Usuário', 'Ação', 'Documento', 'Destino', 'Comentário']],
            body: activities,
            margin: { left: 40 },
            theme: 'grid'
        });
        <?php endif; ?>

        <?php if($tipo_relatorio == 'acessos' && !empty($acessos)): ?>
        // Tabela de acessos
        const accesses = <?php echo json_encode(array_map(function($a) {
            return [
                formatarData($a['Data']),
                $a['usuario'],
                $a['Tipo'],
                $a['Ip'],
                $a['Descricao'] ?? ''
            ];
        }, $acessos)); ?>;

        doc.autoTable({
            startY: yPosition,
            head: [['Data', 'Usuário', 'Tipo', 'IP', 'Descrição']],
            body: accesses,
            margin: { left: 40 },
            theme: 'grid'
        });
        <?php endif; ?>

        <?php if($tipo_relatorio == 'usuarios' && !empty($usuarios)): ?>
        // Tabela de usuários
        const users = <?php echo json_encode(array_map(function($u) {
            return [
                $u['Nome'],
                $u['Email'],
                $u['Telefone'],
                $u['total_documentos'],
                $u['docs_aprovados'],
                $u['docs_pendentes'],
                $u['docs_rejeitados'],
                formatarData($u['ultima_atividade'])
            ];
        }, $usuarios)); ?>;

        doc.autoTable({
            startY: yPosition,
            head: [['Nome', 'Email', 'Telefone', 'Total Docs', 'Aprovados', 'Pendentes', 'Rejeitados', 'Última Atividade']],
            body: users,
            margin: { left: 40 },
            theme: 'grid'
        });
        <?php endif; ?>

        <?php if($tipo_relatorio == 'documentos' && !empty($documentos)): ?>
        // Tabela de documentos
        const docs = <?php echo json_encode(array_map(function($d) {
            return [
                $d['Titulo'],
                $d['Estado'],
                formatarData($d['Data']),
                $d['Tipo'],
                formatBytes($d['Tamanho']),
                $d['nome_usuario'],
                $d['nome_aprovador'] ?? 'N/A',
                $d['Data_aprovacao'] ? formatarData($d['Data_aprovacao']) : 'N/A'
            ];
        }, $documentos)); ?>;

        doc.autoTable({
            startY: yPosition,
            head: [['Título', 'Estado', 'Data', 'Tipo', 'Tamanho', 'Criado por', 'Aprovado por', 'Data Aprovação']],
            body: docs,
            margin: { left: 40 },
            theme: 'grid'
        });
        <?php endif; ?>

        // Adicionar rodapé
        doc.setFontSize(10);
        const pageCount = doc.internal.getNumberOfPages();
        for(let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.width - 100, doc.internal.pageSize.height - 30);
            doc.text('PGDI - Sistema de Gestão Documental', 40, doc.internal.pageSize.height - 30);
        }

        // Salvar o PDF
        doc.save(`Relatorio_${<?php echo json_encode($deptInfo['nome_departamento']); ?>}_${new Date().toISOString().slice(0,10)}.pdf`);
    }
    </script>
</body>
</html>