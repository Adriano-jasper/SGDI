<?php 

include 'ConfigEditarUser.php';

if(isset($_GET['Id'])):
  $id = mysqli_escape_string($mysqli, $_GET['Id']);

  $sql= mysqli_query($mysqli, "SELECT * from usuario where id = '$id'");
  $OldDate = mysqli_fetch_array($sql);
endif;

if(!isset($_SESSION['logado'])):
    header('Location:../login.php');
endif;

$id = $_SESSION['id_Admin'];
$sql = " SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);



// Buscar notificações não lidas para o usuário atual
$notificacoes_query = mysqli_query($mysqli, "SELECT * FROM notificacoes 
                                           WHERE Id_usuario = '$id' AND Visualizada = 0
                                           ORDER BY Data DESC LIMIT 3");
$notificacoes_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total 
                                                              FROM notificacoes 
                                                              WHERE Id_usuario = '$id' AND Visualizada = 0"))['total'];

mysqli_close($mysqli);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <!-- Twitter meta-->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="@pratikborsadiya">
    <meta property="twitter:creator" content="@pratikborsadiya">
    <!-- Open Graph Meta-->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Vali Admin">
    <meta property="og:title" content="Vali - Free Bootstrap 4 admin theme">
    <meta property="og:url" content="http://pratikborsadiya.in/blog/vali-admin">
    <meta property="og:image" content="http://pratikborsadiya.in/blog/vali-admin/hero-social.png">
    <meta property="og:description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <title>Registro - PGDI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body class="app sidebar-mini rtl">
    <!-- Navbar-->
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
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name"><?php echo $dados['Nome']  ?></p>
          <p class="app-sidebar__user-designation">Admin</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item active" href="index.php"><i class="app-menu__icon fa fa-bar-chart"></i><span class="app-menu__label">Dashboard</span></a></li>
        <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Usuários</span><i class="treeview-indicator fa fa-angle-right"></i></a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="ListarUser.php" target="_blank" rel="noopener"><i class="icon fa fa-circle-o"></i>Listar Usuários</a></li>     
          </ul>
        </li>
      </ul>
    </aside>
    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-edit"></i> Editar usuario</h1>
          <p>Painel de Administardor</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">Admin</li>
          <li class="breadcrumb-item"><a href="#">Editar usuario</a></li>
        </ul>
      </div>
      <div class="row">
        </div>
        <div class="clearix"></div>
        <div class="col-md-12">
          <div class="tile">
            <h3 class="tile-title">Editar usuario</h3>
            <div class="tile-body">
              <form action="ConfigEditarUser.php" method="POST">
                
                <input type="hidden"name ='id' value="<?php echo $OldDate['Id'] ?>" >
                <div class="form-group">
                  <label class="control-label">Name</label>
                  <input class="form-control" type="text" placeholder="Nome completo" name="nome" required value=<?php echo $OldDate['Nome'] ?>>
                </div>
                <div class="form-group">
                  <label class="control-label">Email</label>
                  <input class="form-control" type="email" placeholder="Email válido" name ="email" required value=<?php echo $OldDate['Email'] ?>>
                </div>
                <div class="form-group">
                  <label class="control-label">Telefone</label>
                  <input class="form-control" type="number" placeholder="Telefone" name="telefone" required value=<?php echo $OldDate['Telefone'] ?>>
                </div>
                <div class="form-group">
                  <label class="control-label">Definir tipo Usuário</label>
                  <select class="form-control" id="chefe" name="chefe">
                    <option value="0">Admin</option>
                    <option value="1">Usuário Normal</option>
                </select>
                </div>
                <div class="form-group">
                  <label class="control-label">Género</label>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="form-check-input" type="radio" name="gender">Masculino
                    </label>
                  </div>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="form-check-input" type="radio" name="gender">Feminino
                    </label>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label">Senha</label>
                  <input class="form-control" type="password" placeholder="Senha" name="senha" required  value=<?php echo $OldDate['Senha'] ?>>
                </div>
                 <div class="tile-footer">
                    <button class="btn btn-info" type="submit" name="Editar" id="demoSwal"><i class="fa fa-fw fa-lg fa-check-circle"></i>Editar</button>
                 </div>
              </form>
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
    <!-- Page specific javascripts-->
    <script type="text/javascript" src="../js/plugins/bootstrap-notify.min.js"></script>
    <script type="text/javascript" src="../js/plugins/sweetalert.min.js"></script>
    <!-- Google analytics script-->
    <script type="text/javascript">
      if(document.location.hostname == 'pratikborsadiya.in') {
      	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      	ga('create', 'UA-72504830-1', 'auto');
      	ga('send', 'pageview');
      }
    </script>

<script type="text/javascript">
  $('#demoNotify').click(function(){
    $.notify({
      title: "Update Complete : ",
      message: "Something cool is just updated!",
      icon: 'fa fa-check' 
    },{
      type: "info"
    });
  });
  $('#demoSwal').click(function(){
    swal({
      title: "Tens a certeza?",
      text: "Desejas registrar esse Usuário?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim, desejo!",
      cancelButtonText: "Não, não desejo!",
      closeOnConfirm: false,
      closeOnCancel: false
    }, function(isConfirm) {
      if (isConfirm) {
        swal("Registrado!", "Usuário Registrado.", "success");
      } else {
        swal("Cancelado", "Usuário Cancelado:)", "error");
      }
    });
  });
</script>
  </body>
</html>