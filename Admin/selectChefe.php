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

if(isset($_GET['id'])):
  $idDepart = mysqli_escape_string($mysqli, $_GET['id']);
endif;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta name="description" content="PGDI - Sistema de Gestão Documental">
    <title>Selecionar Chefe - PGDI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .user-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: white;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .user-header {
            background-color: #00467f;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #00467f;
            border: 3px solid white;
        }
        .user-body {
            padding: 15px;
        }
        .user-details {
            margin-bottom: 15px;
        }
        .user-details p {
            margin-bottom: 5px;
        }
        .user-details i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
            color: #00467f;
        }
        .action-button {
            width: 100%;
            background-color: #00467f;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #003366;
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
    </style>
</head>
<body class="app sidebar-mini rtl">
    <!-- Navbar (mantido igual) -->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
      <ul class="app-nav">
        <!--Notification Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i></a>
          <ul class="app-notification dropdown-menu dropdown-menu-right">
            <li class="app-notification__title">You have 4 new notifications.</li>
            <div class="app-notification__content">
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Lisa sent you a mail</p>
                    <p class="app-notification__meta">2 min ago</p>
                  </div></a></li>
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Mail server not working</p>
                    <p class="app-notification__meta">5 min ago</p>
                  </div></a></li>
              <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                  <div>
                    <p class="app-notification__message">Transaction complete</p>
                    <p class="app-notification__meta">2 days ago</p>
                  </div></a></li>
              <div class="app-notification__content">
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Lisa sent you a mail</p>
                      <p class="app-notification__meta">2 min ago</p>
                    </div></a></li>
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Mail server not working</p>
                      <p class="app-notification__meta">5 min ago</p>
                    </div></a></li>
                <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-success"></i><i class="fa fa-money fa-stack-1x fa-inverse"></i></span></span>
                    <div>
                      <p class="app-notification__message">Transaction complete</p>
                      <p class="app-notification__meta">2 days ago</p>
                    </div></a></li>
              </div>
            </div>
            <li class="app-notification__footer"><a href="#">See all notifications.</a></li>
          </ul>
        </li>
        <!-- User Menu-->
        <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
          <ul class="dropdown-menu settings-menu dropdown-menu-right">
            <li><a class="dropdown-item" href="page-user.php"><i class="fa fa-cog fa-lg"></i> Settings</a></li>
            <li><a class="dropdown-item" href="page-user.php"><i class="fa fa-user fa-lg"></i> Profile</a></li>
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
          <h1><i class="fa fa-users"></i> Selecionar Chefe de Departamento</h1>
          <p>Usuários disponíveis para designar como chefe</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">Departamentos</li>
          <li class="breadcrumb-item active"><a href="#">Selecionar Chefe</a></li>
        </ul>
      </div>
      
      <div class="row">
        <div class="col-md-12">
          <!-- Barra de Pesquisa -->
          <div class="search-container">
            <div class="input-group">
              <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar usuários...">
              <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="searchButton">
                  <i class="fa fa-search"></i> Pesquisar
                </button>
              </div>
            </div>
          </div>
          
          <!-- Lista de Usuários em Cards -->
          <div class="row" id="usersContainer">
            <?php
            $sql = "SELECT * FROM usuario WHERE Permissao = '1' AND Id_Departamento IS NULL";
            $resultado = mysqli_query($mysqli, $sql);
            while($dados = mysqli_fetch_array($resultado)):
            ?>
            <div class="col-md-4 col-sm-6 user-item">
              <div class="user-card">
                <div class="user-header">
                  <div class="user-avatar">
                    <?php echo strtoupper(substr($dados['Nome'], 0, 1)); ?>
                  </div>
                  <h5><?php echo $dados['Nome']; ?></h5>
                </div>
                <div class="user-body">
                  <div class="user-details">
                    <p><i class="fa fa-envelope"></i> <?php echo $dados['Email']; ?></p>
                    <p><i class="fa fa-phone"></i> <?php echo $dados['Telefone']; ?></p>
                    <p><i class="fa fa-venus-mars"></i> 
                      <?php 
                        if($dados['Genero'] == 'M') echo 'Masculino';
                        elseif($dados['Genero'] == 'F') echo 'Feminino';
                        else echo 'Outro';
                      ?>
                    </p>
                  </div>
                  <form action="ConfigSelectChefe.php?idDep=<?php echo $idDepart?>" method="POST">
                    <input type="hidden" name="NomeChefe" value="<?php echo $dados['Nome']; ?>">
                    <input type="hidden" name="idDep" value="<?php echo $idDepart; ?>">
                    <input type="hidden" name="id" value="<?php echo $dados['Id']; ?>">
                    <button type="submit" class="action-button" name="AdicionarChefe">
                      <i class="fa fa-user-plus"></i> Designar como Chefe
                    </button>
                  </form>
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
          filterUsers();
        });
        
        $('#searchInput').keyup(function(e) {
          if(e.keyCode == 13) {
            filterUsers();
          }
        });
        
        function filterUsers() {
          const searchTerm = $('#searchInput').val().toLowerCase();
          
          $('.user-item').each(function() {
            const $item = $(this);
            const name = $item.find('h5').text().toLowerCase();
            const email = $item.find('.user-details p:nth-child(1)').text().toLowerCase();
            const phone = $item.find('.user-details p:nth-child(2)').text().toLowerCase();
            
            if(searchTerm === '' || 
               name.includes(searchTerm) || 
               email.includes(searchTerm) || 
               phone.includes(searchTerm)) {
              $item.show();
            } else {
              $item.hide();
            }
          });
        }
      });
    </script>
  </body>
</html>