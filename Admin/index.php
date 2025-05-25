<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])):
    header('Location:../login.php');
endif;

$id = $_SESSION['id_Admin'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);


// Buscar notificações não lidas para o usuário atual
$notificacoes_query = mysqli_query($mysqli, "SELECT * FROM notificacoes 
                                           WHERE Id_usuario = '$id' AND Visualizada = 0
                                           ORDER BY Data DESC LIMIT 3");
$notificacoes_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total 
                                                              FROM notificacoes 
                                                              WHERE Id_usuario = '$id' AND Visualizada = 0"))['total'];


// Dados para os gráficos
$users_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM usuario"))['total'];
$depts_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM departamentos"))['total'];
$docs_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM documentos"))['total'];
$chefes_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM usuario WHERE Permissao = '2'"))['total'];

// Últimos documentos
$last_docs = mysqli_query($mysqli, "SELECT d.*, u.Nome as usuario_nome 
                                   FROM documentos d 
                                   JOIN usuario u ON d.Id_usuario = u.Id 
                                   ORDER BY d.Data DESC LIMIT 5");

// Usuários por departamento
$users_by_dept = mysqli_query($mysqli, "SELECT d.Nome as dept_nome, COUNT(u.Id) as user_count 
                                       FROM departamentos d 
                                       LEFT JOIN usuario u ON d.id = u.Id_Departamento 
                                       GROUP BY d.id");

// Documentos por status
$docs_by_status = mysqli_query($mysqli, "SELECT Estado, COUNT(*) as count 
                                        FROM documentos 
                                        GROUP BY Estado");

// Preparar dados para gráficos
$dept_labels = [];
$dept_data = [];
while($row = mysqli_fetch_assoc($users_by_dept)) {
    $dept_labels[] = $row['dept_nome'];
    $dept_data[] = $row['user_count'];
}

$status_labels = [];
$status_data = [];
$status_colors = [
    'Rascunho' => '#FF6384',
    'Pendente' => '#36A2EB',
    'Aprovado' => '#4BC0C0',
    'Rejeitado' => '#FFCE56',
    'Arquivado' => '#9966FF'
];
while($row = mysqli_fetch_assoc($docs_by_status)) {
    $status_labels[] = $row['Estado'];
    $status_data[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta name="description" content="PGDI - Sistema de Gestão Documental">
    <title>Dashboard - PGDI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
 <style>
      /* Estilo para o ponto de notificação */

      .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    }

  /* Estilo para o dropdown de notificações */
  .app-notification {
    width: 350px;
    padding: 0;
  }

  .app-notification__title {
    padding: 8px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: bold;
    }

  .app-notification__content {
    max-height: 300px;
    overflow-y: auto;
  }

  .app-notification__item {
    display: flex;
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    transition: background-color 0.3s;
  }

  .app-notification__item:hover {
    background-color: #f8f9fa;
    text-decoration: none;
  }

  .app-notification__icon {
    margin-right: 15px;
  }

  .app-notification__message {
    margin-bottom: 0;
    font-size: 14px;
  }

  .app-notification__meta {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
  }

  .app-notification__footer {
    padding: 8px 20px;
    text-align: center;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
  }

  .app-notification__footer a {
    color: #007bff;
  }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-card .info {
            text-align: right;
        }
        .stat-card .info h4 {
            margin-bottom: 5px;
            font-weight: 300;
        }
        .stat-card .info p {
            font-size: 1.8rem;
            margin-bottom: 0;
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background-color: #00467f;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .recent-doc {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .recent-doc:last-child {
            border-bottom: none;
        }
        .doc-icon {
            width: 40px;
            height: 40px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #00467f;
        }
        .doc-info {
            flex-grow: 1;
        }
        .doc-title {
            font-weight: 500;
            margin-bottom: 3px;
        }
        .doc-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .btn-export {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-export:hover {
            background-color: #218838;
            color: white;
        }
  </style>

</head>
<script>
$(document).ready(function() {
    // Quando o dropdown de notificações é aberto
    $('.app-nav .dropdown').on('shown.bs.dropdown', function() {
        // Verificar se é o dropdown de notificações
        if($(this).find('.fa-bell-o').length) {
            // Fazer uma requisição AJAX para marcar as notificações como lidas
            $.ajax({
                url: 'marcar_notificacoes_lidas.php',
                method: 'POST',
                data: {id_usuario: <?php echo $id; ?>},
                success: function(response) {
                    // Remover o badge de notificação
                    $('.notification-badge').remove();
                }
            });
        }
    });
});
</script>

<body class="app sidebar-mini rtl">
    
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
       <!--Notification Menu-->
       <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications">
            <i class="fa fa-bell-o fa-lg"></i>
            <?php if($notificacoes_count > 0): ?>
            <span class="notification-badge"><?php echo $notificacoes_count; ?></span>
            <?php endif; ?>
          </a>
          <ul class="app-notification dropdown-menu dropdown-menu-right">
            <li class="app-notification__title">Você tem <?php echo $notificacoes_count; ?> novas notificações</li>
            <div class="app-notification__content">
              <?php 
              if(mysqli_num_rows($notificacoes_query) > 0) {
                  while($notif = mysqli_fetch_assoc($notificacoes_query)): 
              ?>
              <li>
                <a class="app-notification__item" href="Notificacoes.php">
                  <span class="app-notification__icon"><span class="fa-stack fa-lg">
                    <i class="fa fa-circle fa-stack-2x text-<?php 
                        switch($notif['Tipo']) {
                            case 'Documento': echo 'primary'; break;
                            case 'Aprovacao': echo 'success'; break;
                            case 'Sistema': echo 'warning'; break;
                            case 'Requisicao': echo 'info'; break;
                            default: echo 'secondary';
                        }
                    ?>"></i>
                    <i class="fa fa-<?php 
                        switch($notif['Tipo']) {
                            case 'Documento': echo 'file'; break;
                            case 'Aprovacao': echo 'check'; break;
                            case 'Sistema': echo 'cog'; break;
                            case 'Requisicao': echo 'share'; break;
                            default: echo 'bell';
                        }
                    ?> fa-stack-1x fa-inverse"></i>
                  </span></span>
                  <div>
                    <p class="app-notification__message"><?php echo $notif['Descricao']; ?></p>
                    <p class="app-notification__meta"><?php echo date('d/m/Y H:i', strtotime($notif['Data'])); ?></p>
                  </div>
                </a>
              </li>
              <?php 
                  endwhile;
              } else {
                  echo '<li><span class="app-notification__message">Nenhuma notificação nova</span></li>';
              }
              ?>
            </div>
            <li class="app-notification__footer">
              <a href="Notificacoes.php">Ver todas as notificações</a>
            </li>
          </ul>
        </li>
        <!-- User Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
          <ul class="dropdown-menu settings-menu dropdown-menu-right">
            <li><a class="dropdown-item" href="EditPerfiluser.php"><i class="fa fa-user fa-lg"></i> Profile</a></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out fa-lg"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </header>
    
    <!-- Sidebar menu (mantido igual) -->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name"><?php echo $dados['Nome'] ?></p>
          <p class="app-sidebar__user-designation">Admin</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item active" href="index.php"><i class="app-menu__icon fa fa-bar-chart"></i><span class="app-menu__label">Dashboard</span></a></li>
        <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Usuários</span><i class="treeview-indicator fa fa-angle-right"></i></a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="RegistroUser.php"><i class="icon fa fa-circle-o"></i> Registrar Usuários</a></li>
            <li><a class="treeview-item" href="ListarUser.php" target="_blank" rel="noopener"><i class="icon fa fa-circle-o"></i>Listar Usuários</a></li>
          </ul>
        </li>
        
        <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-sitemap"></i><span class="app-menu__label">Departamentos</span><i class="treeview-indicator fa fa-angle-right"></i></a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="RegistroDepart.php"><i class="icon fa fa-circle-o"></i> Registrar Departamentos</a></li>
            <li><a class="treeview-item" href="ListarDeprt.php" target="_blank" rel="noopener"><i class="icon fa fa-circle-o"></i>Listar Departamentos</a></li>
          </ul>
        </li>

        <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-files-o"></i><span class="app-menu__label">Documentos</span><i class="treeview-indicator fa fa-angle-right"></i></a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="ListarDoc.php"><i class="icon fa fa-circle-o"></i>Listar Documentos</a></li>
          </ul>
        </li>
          
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-bell-o"></i><span class="app-menu__label">Relatórios</span></a></li>        
      </ul>
    </aside>
    
    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
          <p>Visão geral do sistema</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
        </ul>
      </div>
      
      <div class="row">
        <!-- Cartões de Estatísticas -->
        <div class="col-md-6 col-lg-3">
          <div class="stat-card" style="background: linear-gradient(135deg, #00467f, #0062a3);">
            <i class="fa fa-users"></i>
            <div class="info">
              <h4>Usuários</h4>
              <p><?php echo $users_count; ?></p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #5cb85c);">
            <i class="fa fa-sitemap"></i>
            <div class="info">
              <h4>Departamentos</h4>
              <p><?php echo $depts_count; ?></p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
            <i class="fa fa-files-o"></i>
            <div class="info">
              <h4>Documentos</h4>
              <p><?php echo $docs_count; ?></p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #e4606d);">
            <i class="fa fa-user-circle"></i>
            <div class="info">
              <h4>Chefes</h4>
              <p><?php echo $chefes_count; ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <!-- Gráfico de Documentos por Status -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Documentos por Status</h5>
            </div>
            <div class="card-body">
              <canvas id="statusChart" height="250"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Gráfico de Usuários por Departamento -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Usuários por Departamento</h5>
            </div>
            <div class="card-body">
              <canvas id="deptChart" height="250"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <!-- Últimos Documentos -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Últimos Documentos</h5>
            </div>
            <div class="card-body">
              <?php while($doc = mysqli_fetch_assoc($last_docs)): ?>
              <div class="recent-doc">
                <div class="doc-icon">
                  <i class="fa fa-file-text-o"></i>
                </div>
                <div class="doc-info">
                  <div class="doc-title"><?php echo $doc['Titulo']; ?></div>
                  <div class="doc-meta">
                    <span><?php echo $doc['usuario_nome']; ?></span> • 
                    <span><?php echo date('d/m/Y H:i', strtotime($doc['Data'])); ?></span>
                  </div>
                </div>
                <span class="badge-status" style="background-color: <?php echo $status_colors[$doc['Estado']]; ?>">
                  <?php echo $doc['Estado']; ?>
                </span>
              </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
        
        <!-- Ações Rápidas -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Ações Rápidas</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <a href="RegistroUser.php" class="btn btn-primary btn-block">
                    <i class="fa fa-user-plus"></i> Novo Usuário
                  </a>
                </div>
                <div class="col-md-6 mb-3">
                  <a href="RegistroDepart.php" class="btn btn-success btn-block">
                    <i class="fa fa-sitemap"></i> Novo Departamento
                  </a>
                </div>
                <div class="col-md-6 mb-3">
                  <a href="ListarDoc.php" class="btn btn-info btn-block">
                    <i class="fa fa-file-text"></i> Ver Documentos
                  </a>
                </div>
                <div class="col-md-6 mb-3">
                  <button onclick="downloadPDF()" class="btn btn-warning btn-block">
                    <i class="fa fa-file-pdf-o"></i> Exportar Relatório
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Essential javascripts for application to work-->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="../js/plugins/pace.min.js"></script>
    
    <script>
      // Gráfico de Status de Documentos
      const statusCtx = document.getElementById('statusChart').getContext('2d');
      const statusChart = new Chart(statusCtx, {
          type: 'doughnut',
          data: {
              labels: <?php echo json_encode($status_labels); ?>,
              datasets: [{
                  data: <?php echo json_encode($status_data); ?>,
                  backgroundColor: [
                      '#FF6384',
                      '#36A2EB',
                      '#4BC0C0',
                      '#FFCE56',
                      '#9966FF'
                  ],
                  borderWidth: 1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      position: 'right',
                  }
              }
          }
      });

      // Gráfico de Usuários por Departamento
      const deptCtx = document.getElementById('deptChart').getContext('2d');
      const deptChart = new Chart(deptCtx, {
          type: 'bar',
          data: {
              labels: <?php echo json_encode($dept_labels); ?>,
              datasets: [{
                  label: 'Usuários',
                  data: <?php echo json_encode($dept_data); ?>,
                  backgroundColor: '#00467f',
                  borderWidth: 1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                  y: {
                      beginAtZero: true,
                      ticks: {
                          stepSize: 1
                      }
                  }
              }
          }
      });

      // Função para exportar PDF
      function downloadPDF() {
          const { jsPDF } = window.jspdf;
          const doc = new jsPDF();
          
          // Data atual
          const today = new Date();
          const date = today.toLocaleDateString();
          
          // Adicionar conteúdo ao PDF
          doc.setFontSize(18);
          doc.text("Relatório do Sistema PGDI", 20, 20);
          
          doc.setFontSize(12);
          doc.text(`Data do Relatório: ${date}`, 20, 30);
          doc.text(`Total de Usuários: ${<?php echo $users_count; ?>}`, 20, 40);
          doc.text(`Total de Departamentos: ${<?php echo $depts_count; ?>}`, 20, 50);
          doc.text(`Total de Documentos: ${<?php echo $docs_count; ?>}`, 20, 60);
          doc.text(`Total de Chefes: ${<?php echo $chefes_count; ?>}`, 20, 70);
          
          // Adicionar gráfico de status de documentos
          doc.addPage();
          doc.setFontSize(16);
          doc.text("Distribuição de Documentos por Status", 20, 20);
          doc.addImage(statusChart.toBase64Image(), 'PNG', 20, 30, 170, 100);
          
          // Adicionar gráfico de usuários por departamento
          doc.addPage();
          doc.setFontSize(16);
          doc.text("Usuários por Departamento", 20, 20);
          doc.addImage(deptChart.toBase64Image(), 'PNG', 20, 30, 170, 100);
          
          // Salvar o PDF
          doc.save(`relatorio_pgdi_${date.replace(/\//g, '-')}.pdf`);
      }
    </script>
  </body>
</html>