<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_userChefe'];
$sqlUsuario = "SELECT * FROM usuario WHERE Id = '$id'";
$resultadoUsuario = mysqli_query($mysqli, $sqlUsuario);
$dadosUsuario = mysqli_fetch_assoc($resultadoUsuario);

// Obter informações do departamento
$sqlDepartamento = "SELECT * FROM departamentos WHERE Id_Chefe = '$id'";
$resultadoDepartamento = mysqli_query($mysqli, $sqlDepartamento);
$dadosDepartamento = mysqli_fetch_assoc($resultadoDepartamento);

// Obter estatísticas do departamento
$sqlDocumentos = "SELECT COUNT(*) as total FROM documentos d
                 JOIN documento_departamento dd ON d.Id = dd.Id_documento
                 WHERE dd.Id_departamento = '".$dadosDepartamento['id']."'";
$resultadoDocumentos = mysqli_query($mysqli, $sqlDocumentos);
$totalDocumentos = mysqli_fetch_assoc($resultadoDocumentos)['total'];

$sqlMembros = "SELECT COUNT(*) as total FROM usuario 
              WHERE Id_Departamento = '".$dadosDepartamento['id']."'";
$resultadoMembros = mysqli_query($mysqli, $sqlMembros);
$totalMembros = mysqli_fetch_assoc($resultadoMembros)['total'];

$sqlNotificacoes = "SELECT COUNT(*) as total FROM notificacoes 
                   WHERE Id_usuario = '$id' AND Visualizada = 0";
$resultadoNotificacoes = mysqli_query($mysqli, $sqlNotificacoes);
$totalNotificacoes = mysqli_fetch_assoc($resultadoNotificacoes)['total'];

$sqlFluxo = "SELECT COUNT(*) as total FROM fluxo_trabalho
            WHERE Id_para = '$id' AND Tipo_destino = 'Usuario'";
$resultadoFluxo = mysqli_query($mysqli, $sqlFluxo);
$totalPendencias = mysqli_fetch_assoc($resultadoFluxo)['total'];

// Obter documentos recentes
$sqlDocsRecentes = "SELECT d.*, u.Nome as autor 
                   FROM documentos d
                   JOIN documento_departamento dd ON d.Id = dd.Id_documento
                   JOIN usuario u ON d.Id_usuario = u.Id
                   WHERE dd.Id_departamento = '".$dadosDepartamento['id']."'
                   ORDER BY d.Data DESC LIMIT 5";
$resultadoDocsRecentes = mysqli_query($mysqli, $sqlDocsRecentes);
$documentosRecentes = mysqli_fetch_all($resultadoDocsRecentes, MYSQLI_ASSOC);

// Obter contagem de documentos por estado
$sqlStatusDocs = "SELECT d.Estado, COUNT(*) as total 
                 FROM documentos d
                 JOIN documento_departamento dd ON d.Id = dd.Id_documento
                 WHERE dd.Id_departamento = '".$dadosDepartamento['id']."'
                 GROUP BY d.Estado";
$resultadoStatusDocs = mysqli_query($mysqli, $sqlStatusDocs);
$statusDocumentos = mysqli_fetch_all($resultadoStatusDocs, MYSQLI_ASSOC);

// Preparar dados para o gráfico
$estados = ['Aprovado', 'Pendente', 'Rejeitado', 'Rascunho', 'Arquivado'];
$contagemEstados = array_fill_keys($estados, 0);

foreach($statusDocumentos as $status) {
    $contagemEstados[$status['Estado']] = $status['total'];
}

// Obter atividades recentes (fluxo de trabalho + notificações)
$sqlAtividades = "(SELECT 'fluxo' as tipo, ft.Data, ft.Acao, d.Titulo, ft.Comentario, NULL as Descricao
                  FROM fluxo_trabalho ft
                  JOIN documentos d ON ft.Id_documento = d.Id
                  WHERE ft.Id_para = '$id' AND ft.Tipo_destino = 'Usuario'
                  ORDER BY ft.Data DESC LIMIT 5)
                  
                  UNION
                  
                  (SELECT 'notificacao' as tipo, n.Data, NULL as Acao, NULL as Titulo, NULL as Comentario, n.Descricao
                  FROM notificacoes n
                  WHERE n.Id_usuario = '$id'
                  ORDER BY n.Data DESC LIMIT 5)
                  
                  ORDER BY Data DESC LIMIT 10";

$resultadoAtividades = mysqli_query($mysqli, $sqlAtividades);
$atividadesRecentes = mysqli_fetch_all($resultadoAtividades, MYSQLI_ASSOC);

// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];


// Função para formatar data
function formatarData($dataTimestamp) {
    if(empty($dataTimestamp)) return 'N/A';
    $data = new DateTime($dataTimestamp);
    return $data->format('d/m/Y H:i');
}

// Função para traduzir ações
function traduzirAcao($acao) {
    $traducoes = [
        'Enviar' => 'Documento enviado',
        'Aprovar' => 'Documento aprovado',
        'Rejeitar' => 'Documento rejeitado',
        'Devolver' => 'Documento devolvido'
    ];
    return $traducoes[$acao] ?? $acao;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="description" content="PGDI - Sistema de Gestão Documental">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Dashboard</title>
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" href="../css1/style.css">
    <link rel="stylesheet" href="../css1/bootstrap.min.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
   
  <style>
        /* Estilo para o badge de notificações */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .notification-wrapper {
            position: relative;
            display: inline-block;
        }
      
    </style>
  <body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
            <!-- Notificações -->
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications">
                    <div class="notification-wrapper">
                        <i class="fa fa-bell-o fa-lg"></i>
                        <?php if($total_notificacoes > 0): ?>
                            <span class="notification-badge"><?php echo $total_notificacoes; ?></span>
                        <?php endif; ?>
                    </div>
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-right">
                    <li class="app-notification__title">Você tem <?php echo $total_notificacoes; ?> novas notificações</li>
                    <div class="app-notification__content">
                        <?php 
                        $sql_recentes = "SELECT * FROM notificacoes WHERE Id_usuario = '$id' ORDER BY Data DESC LIMIT 5";
                        $result_recentes = mysqli_query($mysqli, $sql_recentes);
                        
                        if(mysqli_num_rows($result_recentes) > 0) {
                            while($notificacao = mysqli_fetch_assoc($result_recentes)): 
                        ?>
                        <li>
                            <a class="app-notification__item" href="Notificacoes.php">
                                <span class="app-notification__icon">
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                                        <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
                                    </span>
                                </span>
                                <div>
                                    <p class="app-notification__message"><?php echo htmlspecialchars($notificacao['Descricao']); ?></p>
                                    <p class="app-notification__meta"><?php echo date('d/m/Y H:i', strtotime($notificacao['Data'])); ?></p>
                                </div>
                            </a>
                        </li>
                        <?php 
                            endwhile;
                        } else {
                            echo '<li><a class="app-notification__item" href="javascript:void(0)"><span>Nenhuma notificação</span></a></li>';
                        }
                        ?>
                    </div>
                    <li class="app-notification__footer"><a href="Notificacoes.php">Ver todas as notificações</a></li>
                </ul>
            </li>
            
            <!-- Perfil do Usuário -->
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
          <p class="app-sidebar__user-name"><?php echo $dadosUsuario['Nome']; ?></p>
          <p class="app-sidebar__user-designation">Chefe de Departamento</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item active" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
        
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
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
    </aside>
    
    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
          <p>Bem-vindo, <?php echo htmlspecialchars($dadosUsuario['Nome']); ?> - Chefe do Departamento de <?php echo htmlspecialchars($dadosDepartamento['Nome']); ?></p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
        </ul>
      </div>
      
      <!-- Cards de Estatísticas -->
      <div class="row">
        <div class="col-md-6 col-lg-3">
          <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
              <h4>Membros</h4>
              <p><b><?php echo $totalMembros; ?></b></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="widget-small info coloured-icon">
            <i class="icon fa fa-file-text fa-3x"></i>
            <div class="info">
              <h4>Documentos</h4>
              <p><b><?php echo $totalDocumentos; ?></b></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-bell fa-3x"></i>
            <div class="info">
              <h4>Notificações</h4>
              <p><b><?php echo $totalNotificacoes; ?></b></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-tasks fa-3x"></i>
            <div class="info">
              <h4>Pendências</h4>
              <p><b><?php echo $totalPendencias; ?></b></p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Gráficos e Documentos Recentes -->
      <div class="row">
        <!-- Gráfico de Documentos por Estado -->
        <div class="col-md-6">
          <div class="tile">
            <h3 class="tile-title">Documentos por Estado</h3>
            <div class="embed-responsive embed-responsive-16by9">
              <canvas class="embed-responsive-item" id="docStatusChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Documentos Recentes -->
        <div class="col-md-6">
          <div class="tile">
            <h3 class="tile-title">Documentos Recentes</h3>
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Data</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($documentosRecentes) > 0): ?>
                    <?php foreach($documentosRecentes as $documento): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($documento['Titulo']); ?></td>
                        <td><?php echo htmlspecialchars($documento['autor']); ?></td>
                        <td><?php echo formatarData($documento['Data']); ?></td>
                        <td>
                          <span class="badge 
                            <?php 
                            switch($documento['Estado']) {
                              case 'Aprovado': echo 'badge-success'; break;
                              case 'Pendente': echo 'badge-warning'; break;
                              case 'Rejeitado': echo 'badge-danger'; break;
                              case 'Rascunho': echo 'badge-secondary'; break;
                              case 'Arquivado': echo 'badge-info'; break;
                              default: echo 'badge-light';
                            }
                            ?>">
                            <?php echo $documento['Estado']; ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="text-center">Nenhum documento recente</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Seção de Atividades Recentes -->
      <div class="row">
        <div class="col-md-12">
          <div class="tile">
            <h3 class="tile-title">Atividades Recentes</h3>
            <div class="timeline">
              <?php if(count($atividadesRecentes) > 0): ?>
                <?php foreach($atividadesRecentes as $atividade): ?>
                  <div class="timeline-item">
                    <div class="timeline-point timeline-point-<?php 
                      if($atividade['tipo'] == 'notificacao') echo 'info';
                      elseif($atividade['Acao'] == 'Aprovar') echo 'success';
                      elseif($atividade['Acao'] == 'Rejeitar') echo 'danger';
                      else echo 'primary';
                    ?>">
                      <i class="fa <?php 
                        if($atividade['tipo'] == 'notificacao') echo 'fa-bell';
                        else echo 'fa-file-text';
                      ?>"></i>
                    </div>
                    <div class="timeline-event">
                      <div class="timeline-heading">
                        <h4>
                          <?php if($atividade['tipo'] == 'notificacao'): ?>
                            Nova Notificação
                          <?php else: ?>
                            <?php echo traduzirAcao($atividade['Acao']); ?>
                            <?php if(!empty($atividade['Titulo'])): ?>
                              - <?php echo htmlspecialchars($atividade['Titulo']); ?>
                            <?php endif; ?>
                          <?php endif; ?>
                        </h4>
                      </div>
                      <div class="timeline-body">
                        <p>
                          <?php if($atividade['tipo'] == 'notificacao'): ?>
                            <?php echo htmlspecialchars($atividade['Descricao']); ?>
                          <?php elseif(!empty($atividade['Comentario'])): ?>
                            <?php echo htmlspecialchars($atividade['Comentario']); ?>
                          <?php else: ?>
                            Ação realizada no documento
                          <?php endif; ?>
                        </p>
                      </div>
                      <div class="timeline-footer">
                        <small class="text-muted"><?php echo formatarData($atividade['Data']); ?></small>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="alert alert-info">Nenhuma atividade recente</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="app-footer">
      <div class="container">
        <span>&copy; <?php echo date('Y'); ?> PGDI - Plataforma de Gestão Documental Integrada. Todos os direitos reservados.</span>
        <div class="social-icons">
          <a href="#"><i class="fa fa-facebook"></i></a>
          <a href="#"><i class="fa fa-twitter"></i></a>
          <a href="#"><i class="fa fa-linkedin"></i></a>
        </div>
      </div>
    </footer>

    <!-- Essential javascripts for application to work-->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="../js/plugins/pace.min.js"></script>
    
    <script>
      // Gráfico de Status de Documentos
      const ctx = document.getElementById('docStatusChart').getContext('2d');
      const docStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: <?php echo json_encode($estados); ?>,
          datasets: [{
            data: <?php echo json_encode(array_values($contagemEstados)); ?>,
            backgroundColor: [
              '#28a745', // Aprovado
              '#ffc107', // Pendente
              '#dc3545', // Rejeitado
              '#6c757d', // Rascunho
              '#17a2b8'  // Arquivado
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.label || '';
                  if (label) {
                    label += ': ';
                  }
                  label += context.raw + ' documentos';
                  return label;
                }
              }
            }
          }
        }
      });

      // Função para gerar PDF
      function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Adiciona título
        doc.setFontSize(20);
        doc.text('Relatório do Departamento', 105, 20, { align: 'center' });
        
        // Adiciona informações básicas
        doc.setFontSize(12);
        doc.text(`Departamento: ${'<?php echo $dadosDepartamento["Nome"]; ?>'}`, 20, 40);
        doc.text(`Chefe: ${'<?php echo $dadosUsuario["Nome"]; ?>'}`, 20, 50);
        doc.text(`Data do Relatório: ${new Date().toLocaleDateString()}`, 20, 60);
        
        // Adiciona estatísticas
        doc.setFontSize(14);
        doc.text('Estatísticas do Departamento', 20, 80);
        doc.setFontSize(12);
        doc.text(`- Total de Membros: ${'<?php echo $totalMembros; ?>'}`, 20, 90);
        doc.text(`- Total de Documentos: ${'<?php echo $totalDocumentos; ?>'}`, 20, 100);
        doc.text(`- Notificações Pendentes: ${'<?php echo $totalNotificacoes; ?>'}`, 20, 110);
        doc.text(`- Aprovações Pendentes: ${'<?php echo $totalPendencias; ?>'}`, 20, 120);
        
        // Adiciona distribuição de estados
        doc.setFontSize(14);
        doc.text('Distribuição de Documentos por Estado', 20, 140);
        doc.setFontSize(12);
        
        let yPos = 150;
        <?php foreach($contagemEstados as $estado => $total): ?>
          doc.text(`- ${'<?php echo $estado; ?>'}: ${'<?php echo $total; ?>'} documentos`, 20, yPos);
          yPos += 10;
        <?php endforeach; ?>
        
        // Salva o PDF
        doc.save(`Relatorio_${'<?php echo $dadosDepartamento["Nome"]; ?>'}_${new Date().toISOString().split('T')[0]}.pdf`);
      }
    </script>

    <style>
      /* Estilos gerais */
      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
      }
      
      /* Cards de estatísticas */
      .widget-small {
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: transform 0.3s;
      }
      
      .widget-small:hover {
        transform: translateY(-5px);
      }
      
      .widget-small .info h4 {
        font-size: 1.1rem;
        margin-bottom: 5px;
      }
      
      .widget-small .info p {
        font-size: 1.5rem;
        font-weight: bold;
      }
      
      /* Tiles */
      .tile {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      
      .tile-title {
        font-size: 1.4rem;
        margin-bottom: 20px;
        color: #2c3e50;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
      }
      
      /* Tabela */
      .table {
        width: 100%;
      }
      
      .table th {
        background-color: #f8f9fa;
        font-weight: 600;
      }
      
      /* Timeline */
      .timeline {
        position: relative;
        padding-left: 50px;
      }
      
      .timeline-item {
        position: relative;
        margin-bottom: 20px;
      }
      
      .timeline-point {
        position: absolute;
        left: -25px;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
      }
      
      .timeline-point-primary { background-color: #007bff; }
      .timeline-point-success { background-color: #28a745; }
      .timeline-point-danger { background-color: #dc3545; }
      .timeline-point-warning { background-color: #ffc107; }
      .timeline-point-info { background-color: #17a2b8; }
      
      .timeline-event {
        background: #fff;
        border-radius: 6px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      
      .timeline-heading h4 {
        margin: 0;
        font-size: 1.1rem;
      }
      
      .timeline-body {
        margin: 10px 0;
      }
      
      /* Footer */
      .app-footer {
        background-color: #2c3e50;
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-top: 30px;
      }
      
      .app-footer .container {
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      
      .app-footer .social-icons {
        margin-top: 10px;
      }
      
      .app-footer .social-icons a {
        color: white;
        margin: 0 10px;
        font-size: 1.2rem;
      }
      
      /* Badges */
      .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
      }
      
      .badge-success {
        background-color: #28a745;
        color: white;
      }
      
      .badge-warning {
        background-color: #ffc107;
        color: #212529;
      }
      
      .badge-danger {
        background-color: #dc3545;
        color: white;
      }
      
      .badge-secondary {
        background-color: #6c757d;
        color: white;
      }
      
      .badge-info {
        background-color: #17a2b8;
        color: white;
      }
      
      .badge-light {
        background-color: #f8f9fa;
        color: #212529;
      }
    </style>
       
       <script>
        // Quando o dropdown de notificações é aberto, marca como visualizadas
        $(document).ready(function() {
            $('.app-nav .dropdown').on('show.bs.dropdown', function(e) {
                if($(this).find('.fa-bell-o').length) {
                    $.ajax({
                        url: 'marcar_notificacoes.php',
                        method: 'POST',
                        data: { userId: <?php echo $id; ?> },
                        success: function(response) {
                            if(response === 'success') {
                                $('.notification-badge').remove();
                                $('.app-notification__title').text('Você tem 0 novas notificações');
                            }
                        }
                    });
                }
            });
            
            // Atualiza as notificações a cada 30 segundos
            setInterval(function() {
                $.ajax({
                    url: 'contar_notificacoes.php',
                    method: 'POST',
                    data: { userId: <?php echo $id; ?> },
                    success: function(response) {
                        if(response > 0) {
                            if($('.notification-badge').length) {
                                $('.notification-badge').text(response);
                            } else {
                                $('.notification-wrapper').append('<span class="notification-badge">'+response+'</span>');
                            }
                            $('.app-notification__title').text('Você tem '+response+' novas notificações');
                        } else {
                            $('.notification-badge').remove();
                            $('.app-notification__title').text('Você tem 0 novas notificações');
                        }
                    }
                });
            }, 30000);
        });
    </script>
  </body>
</html>