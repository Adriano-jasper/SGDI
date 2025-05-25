<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_user'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

$sql_dep = "SELECT * FROM departamentos WHERE Id_Chefe = '$id'";
$resultado_dep = mysqli_query($mysqli, $sql_dep);
$dado_dep = mysqli_fetch_array($resultado_dep);

// Marcar notificações como visualizadas ao acessar a página
$sql_marcar = "UPDATE notificacoes SET Visualizada = 1 WHERE Id_usuario = '$id'";
mysqli_query($mysqli, $sql_marcar);

// Consulta para obter todas as notificações
$sql_notificacoes = "SELECT n.*, d.Titulo as DocumentoTitulo 
                     FROM notificacoes n
                     LEFT JOIN documentos d ON n.Id_origem = d.Id
                     WHERE n.Id_usuario = '$id' 
                     ORDER BY n.Data DESC";
$resultado_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Notificações</title>
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" href="../css1/style.css">
    <link rel="stylesheet" href="../css1/bootstrap.min.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification-item.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s;
        }
        .notification-item:hover {
            background-color: #f1f1f1;
        }
        .notification-time {
            font-size: 12px;
            color: #6c757d;
        }
        .notification-icon {
            font-size: 20px;
            margin-right: 15px;
            color: #007bff;
        }
        .badge {
            margin-left: 10px;
        }
    </style>
</head>

<body class="app sidebar-mini rtl">
    <!-- Navbar-->
             <!-- Navbar -->
    <header class="app-header">
        <a class="app-header__logo" href="index.php">PGDI</a>
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        
        <!-- Menu Superior Direito -->
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
    
    <!-- Sidebar -->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            <?php if(!empty($dados['Caminho_da_Ft'])): ?>
               <img src="Fotos/<?php echo htmlspecialchars($dados['Caminho_da_Ft']); ?>" alt="Foto do perfil" class="app-sidebar__user-avatar" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;" >
          
            <?php else: ?>
                <div class="app-sidebar__user-avatar bg-primary text-white" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <?php echo strtoupper(substr($dados['Nome'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <p class="app-sidebar__user-name"><?php echo htmlspecialchars($dados['Nome']); ?></p>
                <p class="app-sidebar__user-designation">
                    <?php 
                    if($dados['Permissao'] == '0') echo 'Administrador';
                    elseif($dados['Permissao'] == '2' && isset($departamento)) echo 'Chefe de '.htmlspecialchars($departamento['Nome']);
                    else echo 'Usuário';
                    ?>
                </p>
            </div>
        </div>
        
         <div class="app-menu">
            <li><a class="app-menu__item active" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
            
            <?php if($dados['Id_Departamento'] != NULL): ?>
              
            <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
            <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-files-o"></i><span class="app-menu__label">Meus Documentos</span></a></li>
            <li><a class="app-menu__item" href="DocumentosDoMeuDepartamento.php"><i class="app-menu__icon fa fa-share-alt"></i><span class="app-menu__label">Documentos Compartilhados</span></a></li>
        
              <?php endif; ?>
            </div>
            
        </ul>
    </aside>
    
    <!-- Sidebar menu-->
    
    
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-bell-o"></i> Notificações</h1>
                <p>Gerencie suas notificações</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Notificações</li>
                <li class="breadcrumb-item active"><a href="#">Lista</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="list-group">
                            <?php while($notificacao = mysqli_fetch_array($resultado_notificacoes)): ?>
                            <div class="list-group-item list-group-item-action flex-column align-items-start notification-item <?php echo ($notificacao['Visualizada'] == 0) ? 'unread' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-bell notification-icon"></i>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($notificacao['Descricao']) ?></h5>
                                        <span class="badge badge-<?php 
                                            switch($notificacao['Tipo']) {
                                                case 'Documento': echo 'primary'; break;
                                                case 'Aprovacao': echo 'success'; break;
                                                case 'Sistema': echo 'info'; break;
                                                case 'Requisicao': echo 'warning'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>"><?php echo htmlspecialchars($notificacao['Tipo']) ?></span>
                                    </div>
                                    <small class="notification-time"><?php echo date('d/m/Y H:i', strtotime($notificacao['Data'])) ?></small>
                                </div>
                                <?php if(!empty($notificacao['DocumentoTitulo'])): ?>
                                <p class="mb-1">Documento: <?php echo htmlspecialchars($notificacao['DocumentoTitulo']) ?></p>
                                <?php endif; ?>
                                <small class="text-muted">Estado: <?php echo htmlspecialchars($notificacao['Estado']) ?></small>
                            </div>
                            <?php endwhile; ?>
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
</body>
</html>