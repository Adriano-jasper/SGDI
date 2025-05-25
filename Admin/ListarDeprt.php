<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])):
    header('Location:../login.php');
endif;

$id = $_SESSION['id_Admin'];
$sql = " SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

// Consulta para contar usuários por departamento
$userCountQuery = "SELECT Id_Departamento, COUNT(*) as user_count FROM usuario GROUP BY Id_Departamento";
$userCountResult = mysqli_query($mysqli, $userCountQuery);
$userCounts = [];
while ($row = mysqli_fetch_assoc($userCountResult)) {
    $userCounts[$row['Id_Departamento']] = $row['user_count'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta name="description" content="PGDI - Sistema de Gestão Documental">
    <title>Departamentos - PGDI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
      .department-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
      }
      .department-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
      }
      .department-header {
        background-color: #00467f;
        color: white;
        padding: 15px;
      }
      .department-body {
        padding: 15px;
        background-color: white;
      }
      .department-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
      }
      .stat-item {
        text-align: center;
        flex: 1;
      }
      .stat-value {
        font-weight: bold;
        font-size: 1.2em;
      }
      .stat-label {
        font-size: 0.8em;
        color: #666;
      }
      .search-container {
        margin-bottom: 20px;
      }
      .filter-container {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
      }
      .badge-active {
        background-color: #28a745;
      }
      .badge-inactive {
        background-color: #dc3545;
      }
      .action-buttons {
        display: flex;
        gap: 5px;
      }
      .action-buttons a {
        color: white;
        padding: 5px 8px;
        border-radius: 3px;
        font-size: 0.9em;
      }
    </style>
</head>
<body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
        <!--Notification Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i></a>
          <ul class="app-notification dropdown-menu dropdown-menu-right">
            <li class="app-notification__title">Você tem 4 novas notificações.</li>
            <div class="app-notification__content">
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Lisa enviou um e-mail</p>
                    <p class="app-notification__meta">2 min atrás</p>
                  </div></a></li>
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Servidor de e-mail não está funcionando</p>
                    <p class="app-notification__meta">5 min atrás</p>
                  </div></a></li>
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Transação concluída</p>
                    <p class="app-notification__meta">2 dias atrás</p>
                  </div></a></li>
              <div class="app-notification__content">
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Lisa enviou um e-mail</p>
                      <p class="app-notification__meta">2 min atrás</p>
                    </div></a></li>
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Servidor de e-mail não está funcionando</p>
                      <p class="app-notification__meta">5 min atrás</p>
                    </div></a></li>
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Transação concluída</p>
                      <p class="app-notification__meta">2 dias atrás</p>
                    </div></a></li>
              </div>
            </div>
            <li class="app-notification__footer"><a href="#">Ver todas as notificações</a></li>
          </ul>
        </li>
        <!-- User Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
          <ul class="dropdown-menu settings-menu dropdown-menu-right">
            <li><a class="dropdown-item" href="page-user.php"><i class="fa fa-cog fa-lg"></i> Configurações</a></li>
            <li><a class="dropdown-item" href="page-user.php"><i class="fa fa-user fa-lg"></i> Perfil</a></li>
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
          <h1><i class="fa fa-sitemap"></i> Departamentos</h1>
          <p>Painel do Administrador</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">Listar</li>
          <li class="breadcrumb-item active"><a href="#">Departamentos</a></li>
        </ul>
      </div>
      
      <div class="row">
        <div class="col-md-12">
          <!-- Barra de Pesquisa -->
          <div class="search-container">
            <div class="input-group">
              <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar departamentos...">
              <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="searchButton">
                  <i class="fa fa-search"></i> Pesquisar
                </button>
              </div>
            </div>
          </div>
          
          <!-- Filtros -->
          <div class="filter-container">
            <div class="row">
              <div class="col-md-3">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter" class="form-control">
                  <option value="all">Todos</option>
                  <option value="active">Ativos</option>
                  <option value="inactive">Inativos</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="dateFilter">Ordenar por data:</label>
                <select id="dateFilter" class="form-control">
                  <option value="newest">Mais recentes primeiro</option>
                  <option value="oldest">Mais antigos primeiro</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="sizeFilter">Ordenar por tamanho:</label>
                <select id="sizeFilter" class="form-control">
                  <option value="none">Padrão</option>
                  <option value="largest">Maior departamento</option>
                  <option value="smallest">Menor departamento</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="chefeFilter">Com chefe:</label>
                <select id="chefeFilter" class="form-control">
                  <option value="all">Todos</option>
                  <option value="with">Com chefe</option>
                  <option value="without">Sem chefe</option>
                </select>
              </div>
            </div>
          </div>
          
          <!-- Contadores -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-sitemap fa-3x"></i>
                <div class="info">
                  <h4>Total Departamentos</h4>
                  <p><b><?php 
                    $totalDepts = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM departamentos");
                    echo mysqli_fetch_assoc($totalDepts)['total'];
                  ?></b></p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="widget-small info coloured-icon">
                <i class="icon fa fa-users fa-3x"></i>
                <div class="info">
                  <h4>Total Usuários</h4>
                  <p><b><?php 
                    $totalUsers = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM usuario");
                    echo mysqli_fetch_assoc($totalUsers)['total'];
                  ?></b></p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="widget-small warning coloured-icon">
                <i class="icon fa fa-user-circle fa-3x"></i>
                <div class="info">
                  <h4>Chefes</h4>
                  <p><b><?php 
                    $totalChefes = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM departamentos WHERE Id_Chefe IS NOT NULL");
                    echo mysqli_fetch_assoc($totalChefes)['total'];
                  ?></b></p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="widget-small danger coloured-icon">
                <i class="icon fa fa-archive fa-3x"></i>
                <div class="info">
                  <h4>Inativos</h4>
                  <p><b><?php 
                    $totalInativos = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM departamentos WHERE Ativo = 0");
                    echo mysqli_fetch_assoc($totalInativos)['total'];
                  ?></b></p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Lista de Departamentos em Cards -->
          <div class="row" id="departmentsContainer">
            <?php
            $sql = "SELECT d.*, u.Nome as ChefeNome 
                    FROM departamentos d 
                    LEFT JOIN usuario u ON d.Id_Chefe = u.Id 
                    ORDER BY d.Data_criacao DESC";
            $resultado = mysqli_query($mysqli, $sql);
            while($dados = mysqli_fetch_array($resultado)):
              $userCount = isset($userCounts[$dados['id']]) ? $userCounts[$dados['id']] : 0;
            ?>
            <div class="col-md-4 department-item" 
                 data-status="<?php echo $dados['Ativo'] ? 'active' : 'inactive'; ?>"
                 data-date="<?php echo strtotime($dados['Data_criacao']); ?>"
                 data-size="<?php echo $userCount; ?>"
                 data-chefe="<?php echo $dados['Id_Chefe'] ? 'with' : 'without'; ?>">
              <div class="department-card">
                <div class="department-header">
                  <h4><?php echo $dados['Nome']; ?></h4>
                  <span class="badge <?php echo $dados['Ativo'] ? 'badge-active' : 'badge-inactive'; ?>">
                    <?php echo $dados['Ativo'] ? 'Ativo' : 'Inativo'; ?>
                  </span>
                </div>
                <div class="department-body">
                  <p><?php echo $dados['Descricao']; ?></p>
                  
                  <div class="department-stats">
                    <div class="stat-item">
                      <div class="stat-value"><?php echo $userCount; ?></div>
                      <div class="stat-label">Usuários</div>
                    </div>
                    <div class="stat-item">
                      <div class="stat-value">
                        <?php if($dados['Id_Chefe']): ?>
                          <i class="fa fa-check text-success"></i>
                        <?php else: ?>
                          <i class="fa fa-times text-danger"></i>
                        <?php endif; ?>
                      </div>
                      <div class="stat-label">Chefe</div>
                    </div>
                    <div class="stat-item">
                      <div class="stat-value">
                        <?php 
                          $docCountQuery = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM documento_departamento WHERE Id_departamento = ".$dados['id']);
                          echo mysqli_fetch_assoc($docCountQuery)['total'];
                        ?>
                      </div>
                      <div class="stat-label">Documentos</div>
                    </div>
                  </div>
                  
                  <hr>
                  
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      Criado em: <?php echo date('d/m/Y', strtotime($dados['Data_criacao'])); ?>
                    </small>
                    <div class="action-buttons">
                      <form action="selectChefe.php?id=<?php echo $dados['id']?>" method="POST">
                        <button type="submit" class="btn btn-sm btn-primary" name="AdicionarChefe">
                          <i class="fa fa-user-plus"></i> Chefe
                        </button>
                      </form>
                      <a href="editarDepartamento.php?id=<?php echo $dados['id']?>" class="btn btn-sm btn-info">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a href="toggleDepartamento.php?id=<?php echo $dados['id']?>&status=<?php echo $dados['Ativo'] ? 0 : 1; ?>" class="btn btn-sm <?php echo $dados['Ativo'] ? 'btn-warning' : 'btn-success'; ?>">
                        <i class="fa <?php echo $dados['Ativo'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
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
      $(document).ready(function() {
        // Função de pesquisa
        $('#searchButton').click(function() {
          filterDepartments();
        });
        
        $('#searchInput').keyup(function(e) {
          if(e.keyCode == 13) {
            filterDepartments();
          }
        });
        
        // Função de filtro quando qualquer filtro muda
        $('.filter-container select').change(function() {
          filterDepartments();
        });
        
        function filterDepartments() {
          const searchTerm = $('#searchInput').val().toLowerCase();
          const statusFilter = $('#statusFilter').val();
          const dateFilter = $('#dateFilter').val();
          const sizeFilter = $('#sizeFilter').val();
          const chefeFilter = $('#chefeFilter').val();
          
          $('.department-item').each(function() {
            const $item = $(this);
            const name = $item.find('.department-header h4').text().toLowerCase();
            const status = $item.data('status');
            const date = $item.data('date');
            const size = parseInt($item.data('size'));
            const chefe = $item.data('chefe');
            
            // Aplicar filtros
            let matches = true;
            
            // Filtro de pesquisa
            if(searchTerm && !name.includes(searchTerm)) {
              matches = false;
            }
            
            // Filtro de status
            if(statusFilter !== 'all' && status !== statusFilter) {
              matches = false;
            }
            
            // Filtro de chefe
            if(chefeFilter !== 'all' && chefe !== chefeFilter) {
              matches = false;
            }
            
            if(matches) {
              $item.show();
            } else {
              $item.hide();
            }
          });
          
          // Ordenação por data
          if(dateFilter !== 'none') {
            const $container = $('#departmentsContainer');
            const $items = $container.find('.department-item:visible').get();
            
            $items.sort(function(a, b) {
              const aDate = $(a).data('date');
              const bDate = $(b).data('date');
              
              if(dateFilter === 'newest') {
                return bDate - aDate;
              } else {
                return aDate - bDate;
              }
            });
            
            $.each($items, function(idx, item) {
              $container.append(item);
            });
          }
          
          // Ordenação por tamanho
          if(sizeFilter !== 'none') {
            const $container = $('#departmentsContainer');
            const $items = $container.find('.department-item:visible').get();
            
            $items.sort(function(a, b) {
              const aSize = $(a).data('size');
              const bSize = $(b).data('size');
              
              if(sizeFilter === 'largest') {
                return bSize - aSize;
              } else {
                return aSize - bSize;
              }
            });
            
            $.each($items, function(idx, item) {
              $container.append(item);
            });
          }
        }
      });
    </script>
  </body>
</html>