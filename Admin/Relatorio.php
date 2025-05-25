<?php 
include_once 'conexão.php';
session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_Admin'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dadosAdmin = mysqli_fetch_assoc($resultado);

// Processar filtros
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$departamento = isset($_GET['departamento']) ? (int)$_GET['departamento'] : null;
$tipo_relatorio = isset($_GET['tipo_relatorio']) ? $_GET['tipo_relatorio'] : 'geral';
$usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : null;

// Lista de departamentos para o filtro
$sqlDepartamentos = "SELECT id, Nome FROM departamentos WHERE Ativo = 1 ORDER BY Nome";
$resultadoDepartamentos = mysqli_query($mysqli, $sqlDepartamentos);
$listaDepartamentos = mysqli_fetch_all($resultadoDepartamentos, MYSQLI_ASSOC);

// Lista de usuários para o filtro
$sqlUsuarios = "SELECT Id, Nome FROM usuario WHERE Ativo = 1 ORDER BY Nome";
$resultadoUsuarios = mysqli_query($mysqli, $sqlUsuarios);
$listaUsuarios = mysqli_fetch_all($resultadoUsuarios, MYSQLI_ASSOC);

// Relatório específico do departamento
if($departamento && $tipo_relatorio == 'departamento') {
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
}

// Relatório específico do usuário
if($usuario && $tipo_relatorio == 'usuario') {
    $sqlUserInfo = "SELECT 
        u.Nome,
        u.Email,
        d.Nome as departamento,
        COUNT(doc.Id) as total_documentos,
        SUM(CASE WHEN doc.Estado = 'Aprovado' THEN 1 ELSE 0 END) as docs_aprovados,
        SUM(CASE WHEN doc.Estado = 'Pendente' THEN 1 ELSE 0 END) as docs_pendentes,
        SUM(CASE WHEN doc.Estado = 'Rejeitado' THEN 1 ELSE 0 END) as docs_rejeitados,
        COUNT(DISTINCT n.Id) as total_notificacoes,
        MAX(a.Data) as ultimo_acesso,
        COUNT(DISTINCT a.Id) as total_acessos
    FROM usuario u
    LEFT JOIN departamentos d ON u.Id_Departamento = d.id
    LEFT JOIN documentos doc ON u.Id = doc.Id_usuario
    LEFT JOIN notificacoes n ON u.Id = n.Id_usuario
    LEFT JOIN acessos a ON u.Id = a.Id_usuario
    WHERE u.Id = $usuario
    GROUP BY u.Id";
    
    $resultUserInfo = mysqli_query($mysqli, $sqlUserInfo);
    $userInfo = mysqli_fetch_assoc($resultUserInfo);
}

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
    ORDER BY a.Data DESC";
    
    $resultAcessos = mysqli_query($mysqli, $sqlAcessos);
    $acessos = mysqli_fetch_all($resultAcessos, MYSQLI_ASSOC);
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
    <title>PGDI - Relatórios</title>
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
        .report-meta {
            margin-bottom: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .table-header {
            background: #f4f6f9;
            font-weight: bold;
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
    <!-- Adicionar biblioteca jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="app sidebar-mini">
    <!-- Navbar-->
    <header class="app-header">
        <a class="app-header__logo" href="index.php">PGDI</a>
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="fa fa-user fa-lg"></i>
                </a>
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
                <p class="app-sidebar__user-name"><?php echo $dadosAdmin['Nome'] ?></p>
                <p class="app-sidebar__user-designation">Admin</p>
            </div>
        </div>
        <ul class="app-menu">
            <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-bar-chart"></i><span class="app-menu__label">Dashboard</span></a></li>
            <li class="treeview">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-users"></i>
                    <span class="app-menu__label">Usuários</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="RegistroUser.php"><i class="icon fa fa-circle-o"></i> Registrar Usuários</a></li>
                    <li><a class="treeview-item" href="ListarUser.php" target="_blank" rel="noopener"><i class="icon fa fa-circle-o"></i>Listar Usuários</a></li>
                </ul>
            </li>
            <li class="treeview">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-sitemap"></i>
                    <span class="app-menu__label">Departamentos</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="RegistroDepart.php"><i class="icon fa fa-circle-o"></i> Registrar Departamentos</a></li>
                    <li><a class="treeview-item" href="ListarDeprt.php" target="_blank" rel="noopener"><i class="icon fa fa-circle-o"></i>Listar Departamentos</a></li>
                </ul>
            </li>
            <li class="treeview">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-files-o"></i>
                    <span class="app-menu__label">Documentos</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="ListarDoc.php"><i class="icon fa fa-circle-o"></i>Listar Documentos</a></li>
                </ul>
            </li>
            <li><a class="app-menu__item active" href="Relatorio.php"><i class="app-menu__icon fa fa-bell-o"></i><span class="app-menu__label">Relatórios</span></a></li>
        </ul>
    </aside>

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text"></i> Relatórios</h1>
                <p>Geração de relatórios detalhados do sistema</p>
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
                        <option value="departamento" <?php echo $tipo_relatorio == 'departamento' ? 'selected' : ''; ?>>Relatório por Departamento</option>
                        <option value="usuario" <?php echo $tipo_relatorio == 'usuario' ? 'selected' : ''; ?>>Relatório por Usuário</option>
                        <option value="atividades" <?php echo $tipo_relatorio == 'atividades' ? 'selected' : ''; ?>>Relatório de Atividades</option>
                        <option value="acessos" <?php echo $tipo_relatorio == 'acessos' ? 'selected' : ''; ?>>Relatório de Acessos</option>
                    </select>
                </div>
                
                <?php if($tipo_relatorio == 'departamento'): ?>
                <div class="col-md-3 form-group">
                    <label>Departamento:</label>
                    <select name="departamento" class="form-control" onchange="this.form.submit()">
                        <option value="">Selecione um departamento</option>
                        <?php foreach($listaDepartamentos as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($departamento == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['Nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if($tipo_relatorio == 'usuario'): ?>
                <div class="col-md-3 form-group">
                    <label>Usuário:</label>
                    <select name="usuario" class="form-control" onchange="this.form.submit()">
                        <option value="">Selecione um usuário</option>
                        <?php foreach($listaUsuarios as $user): ?>
                            <option value="<?php echo $user['Id']; ?>" <?php echo ($usuario == $user['Id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['Nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if(in_array($tipo_relatorio, ['atividades', 'acessos'])): ?>
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
                        <p>Relatório Gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
                        <p>Gerado por: <?php echo htmlspecialchars($dadosAdmin['Nome']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Metadados do Relatório -->
            <div class="report-meta">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Tipo de Relatório:</strong> 
                        <?php
                        $tipos = [
                            'geral' => 'Relatório Geral',
                            'departamento' => 'Relatório por Departamento',
                            'usuario' => 'Relatório por Usuário',
                            'atividades' => 'Relatório de Atividades',
                            'acessos' => 'Relatório de Acessos'
                        ];
                        echo $tipos[$tipo_relatorio] ?? 'N/A';
                        ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Período:</strong> 
                        <?php if(in_array($tipo_relatorio, ['atividades', 'acessos'])): ?>
                            <?php echo date('d/m/Y', strtotime($data_inicio)); ?> até <?php echo date('d/m/Y', strtotime($data_fim)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>ID do Relatório:</strong> 
                        <?php echo uniqid('REL-'); ?>
                    </div>
                </div>
            </div>

            <!-- Conteúdo específico do relatório baseado no tipo -->
            <?php if($tipo_relatorio == 'departamento' && $departamento && $deptInfo): ?>
            <div class="report-section">
                <h4 class="mb-4">Relatório Detalhado do Departamento: <?php echo htmlspecialchars($deptInfo['nome_departamento']); ?></h4>
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
                    <p><strong>Chefe do Departamento:</strong> <?php echo htmlspecialchars($deptInfo['nome_chefe'] ?? 'Não definido'); ?></p>
                    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($deptInfo['Descricao']); ?></p>
                    <p><strong>Tempo Médio de Aprovação:</strong> <?php echo round($deptInfo['tempo_medio_aprovacao'], 1); ?> horas</p>
                    <p><strong>Espaço Total Utilizado:</strong> <?php echo formatBytes($deptInfo['espaco_total']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if($tipo_relatorio == 'usuario' && $usuario && $userInfo): ?>
            <div class="report-section">
                <h4>Relatório do Usuário: <?php echo htmlspecialchars($userInfo['Nome']); ?></h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $userInfo['total_documentos']; ?></div>
                            <div class="metric-label">Total de Documentos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $userInfo['total_notificacoes']; ?></div>
                            <div class="metric-label">Total de Notificações</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $userInfo['total_acessos']; ?></div>
                            <div class="metric-label">Total de Acessos</div>
                        </div>
                    </div>
                </div>
                <div class="additional-info mt-4">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userInfo['Email']); ?></p>
                    <p><strong>Departamento:</strong> <?php echo htmlspecialchars($userInfo['departamento'] ?? 'Não definido'); ?></p>
                    <p><strong>Último Acesso:</strong> <?php echo formatarData($userInfo['ultimo_acesso']); ?></p>
                    <p><strong>Status dos Documentos:</strong></p>
                    <ul>
                        <li>Aprovados: <span class="status-badge status-aprovado"><?php echo $userInfo['docs_aprovados']; ?></span></li>
                        <li>Pendentes: <span class="status-badge status-pendente"><?php echo $userInfo['docs_pendentes']; ?></span></li>
                        <li>Rejeitados: <span class="status-badge status-rejeitado"><?php echo $userInfo['docs_rejeitados']; ?></span></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

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

            <?php if($tipo_relatorio == 'acessos' && !empty($acessos)): ?>
            <div class="report-section">
                <h4>Relatório de Acessos (<?php echo date('d/m/Y', strtotime($data_inicio)); ?> - <?php echo date('d/m/Y', strtotime($data_fim)); ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Usuário</th>
                                <th>Departamento</th>
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
                                <td><?php echo htmlspecialchars($acesso['departamento'] ?? 'N/A'); ?></td>
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

    <!-- Script para download do PDF -->
    <script>
    function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt', 'a4');
        const reportContent = document.getElementById('report-content');
        
        // Configurações do documento
        doc.setProperties({
            title: 'Relatório PGDI',
            subject: 'Relatório do Sistema de Gestão Documental',
            author: '<?php echo htmlspecialchars($dadosAdmin['Nome']); ?>',
            keywords: 'PGDI, relatório, documentos',
            creator: 'PGDI System'
        });

        // Adicionar cabeçalho
        doc.setFontSize(20);
        doc.text('PGDI - Relatório', 40, 40);
        
        doc.setFontSize(12);
        doc.text(`Gerado em: ${new Date().toLocaleString()}`, 40, 60);
        doc.text(`Por: <?php echo htmlspecialchars($dadosAdmin['Nome']); ?>`, 40, 80);

        // Adicionar tipo de relatório
        doc.setFontSize(14);
        doc.text(`Tipo: <?php echo $tipos[$tipo_relatorio] ?? 'N/A'; ?>`, 40, 110);

        // Adicionar conteúdo específico baseado no tipo de relatório
        let yPosition = 140;

        <?php if($tipo_relatorio == 'departamento' && $departamento && $deptInfo): ?>
        // Dados do departamento
        doc.setFontSize(16);
        doc.text(`Departamento: <?php echo htmlspecialchars($deptInfo['nome_departamento']); ?>`, 40, yPosition);
        yPosition += 30;

        // Métricas principais
        const metrics = [
            ['Total de Usuários', '<?php echo $deptInfo['total_usuarios']; ?>'],
            ['Total de Documentos', '<?php echo $deptInfo['total_documentos']; ?>'],
            ['Documentos Aprovados', '<?php echo $deptInfo['docs_aprovados']; ?>'],
            ['Documentos Pendentes', '<?php echo $deptInfo['docs_pendentes']; ?>'],
            ['Documentos Rejeitados', '<?php echo $deptInfo['docs_rejeitados']; ?>']
        ];

        doc.autoTable({
            startY: yPosition,
            head: [['Métrica', 'Valor']],
            body: metrics,
            margin: { left: 40 },
            theme: 'grid'
        });

        yPosition = doc.lastAutoTable.finalY + 30;
        <?php endif; ?>

        <?php if($tipo_relatorio == 'atividades' && !empty($atividades)): ?>
        // Tabela de atividades
        const activities = <?php echo json_encode(array_map(function($a) {
            return [
                formatarData($a['Data']),
                $a['usuario_origem'],
                $a['Acao'],
                $a['documento'],
                $a['usuario_destino'] ?? $a['departamento_destino'] ?? 'N/A'
            ];
        }, $atividades)); ?>;

        doc.autoTable({
            startY: yPosition,
            head: [['Data', 'Usuário', 'Ação', 'Documento', 'Destino']],
            body: activities,
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
        doc.save(`Relatorio_PGDI_${new Date().toISOString().slice(0,10)}.pdf`);
    }
    </script>
</body>
</html>