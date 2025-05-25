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
$dados = mysqli_fetch_array($resultado);

// Consulta para obter todas as notificações do sistema (admin)
$sql_notificacoes = "SELECT 
                        n.*, 
                        d.Titulo as DocumentoTitulo,
                        u.Nome as UsuarioNome,
                        dep.Nome as DepartamentoNome,
                        u_remetente.Nome as RemetenteNome
                     FROM notificacoes n
                     LEFT JOIN documentos d ON n.Id_origem = d.Id
                     LEFT JOIN usuario u ON d.Id_usuario = u.Id
                     LEFT JOIN documento_departamento doc_dep ON d.Id = doc_dep.Id_documento
                     LEFT JOIN departamentos dep ON doc_dep.Id_departamento = dep.id
                     LEFT JOIN usuario u_remetente ON n.Id_usuario = u_remetente.Id
                     ORDER BY n.Data DESC";
$resultado_notificacoes = mysqli_query($mysqli, $sql_notificacoes);



// Buscar notificações não lidas para o usuário atual
$notificacoes_query = mysqli_query($mysqli, "SELECT * FROM notificacoes 
                                           WHERE Id_usuario = '$id' AND Visualizada = 0
                                           ORDER BY Data DESC LIMIT 3");
$notificacoes_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) AS total 
                                                              FROM notificacoes 
                                                              WHERE Id_usuario = '$id' AND Visualizada = 0"))['total'];


?>
<style>
    /* Estilo para o ponto de notificação */

    .notification-badgee {
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
</style>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Painel do Administrador</title>
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" href="../css1/style.css">
    <link rel="stylesheet" href="../css1/bootstrap.min.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .notification-card.system {
            border-left-color: #6c757d;
        }
        .notification-card.request {
            border-left-color: #ffc107;
        }
        .notification-card.approval {
            border-left-color: #28a745;
        }
        .notification-card.rejection {
            border-left-color: #dc3545;
        }
        .notification-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .notification-badge {
            font-size: 0.7rem;
            margin-left: 5px;
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
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
        <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
       <!--Notification Menu-->
       <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications">
            <i class="fa fa-bell-o fa-lg"></i>
            <?php if($notificacoes_count > 0): ?>
            <span class="notification-badgee"><?php echo $notificacoes_count; ?></span>
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
                <h1><i class="fa fa-bell"></i> Notificações do Sistema</h1>
                <p>Todas as atividades do sistema</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h3 class="tile-title">Histórico Completo</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">Todas</button>
                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="Requisicao">Solicitações</button>
                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="Aprovacao">Aprovações</button>
                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="Sistema">Sistema</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="notification-container">
                            <?php while($notificacao = mysqli_fetch_array($resultado_notificacoes)): 
                                // Determinar a classe CSS baseada no tipo
                                $card_class = '';
                                $icon = '';
                                $badge_class = '';
                                
                                if($notificacao['Tipo'] == 'Requisicao') {
                                    $card_class = 'request';
                                    $icon = 'fa-share-alt';
                                    $badge_class = 'warning';
                                } elseif($notificacao['Tipo'] == 'Aprovacao') {
                                    $card_class = $notificacao['Estado'] == 'Aceite' ? 'approval' : 'rejection';
                                    $icon = $notificacao['Estado'] == 'Aceite' ? 'fa-check-circle' : 'fa-times-circle';
                                    $badge_class = $notificacao['Estado'] == 'Aceite' ? 'success' : 'danger';
                                } else {
                                    $card_class = 'system';
                                    $icon = 'fa-info-circle';
                                    $badge_class = 'info';
                                }
                                
                                // Construir mensagem baseada no tipo e estado
                                $mensagem = '';
                                $detalhes = '';
                                
                                if($notificacao['Tipo'] == 'Requisicao') {
                                    if($notificacao['Estado'] == 'Pendente') {
                                        $mensagem = "Nova solicitação de partilha";
                                        $detalhes = $notificacao['UsuarioNome'] . " solicitou acesso ao documento '" . $notificacao['DocumentoTitulo'] . "' no departamento " . $notificacao['DepartamentoNome'];
                                    } elseif($notificacao['Estado'] == 'Aceite') {
                                        $mensagem = "Solicitação aceita";
                                        $detalhes = "A solicitação para o documento '" . $notificacao['DocumentoTitulo'] . "' foi aceita";
                                    } elseif($notificacao['Estado'] == 'Negada') {
                                        $mensagem = "Solicitação recusada";
                                        $detalhes = "A solicitação para o documento '" . $notificacao['DocumentoTitulo'] . "' foi recusada";
                                    }
                                } elseif($notificacao['Tipo'] == 'Aprovacao') {
                                    if($notificacao['Estado'] == 'Aceite') {
                                        $mensagem = "Documento aprovado";
                                        $detalhes = "O documento '" . $notificacao['DocumentoTitulo'] . "' foi aprovado por " . $notificacao['RemetenteNome'];
                                    } elseif($notificacao['Estado'] == 'Negada') {
                                        $mensagem = "Documento recusado";
                                        $detalhes = "O documento '" . $notificacao['DocumentoTitulo'] . "' foi recusado por " . $notificacao['RemetenteNome'];
                                    }
                                } else {
                                    $mensagem = "Notificação do sistema";
                                    $detalhes = $notificacao['Descricao'];
                                }
                            ?>
                            <div class="card notification-card <?php echo $card_class; ?>" data-type="<?php echo $notificacao['Tipo']; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">
                                                <i class="fa <?php echo $icon; ?>"></i> 
                                                <?php echo $mensagem; ?>
                                                <?php if(!$notificacao['Visualizada']): ?>
                                                    <span class="badge badge-pill badge-primary notification-badge">Nova</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="card-text"><?php echo $detalhes; ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="notification-time">
                                                <?php echo date('d/m/Y H:i', strtotime($notificacao['Data'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
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
    <script>
        $(document).ready(function() {
            // Filtro de notificações
            $('.filter-btn').click(function() {
                var filter = $(this).data('filter');
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                if(filter == 'all') {
                    $('.notification-card').show();
                } else {
                    $('.notification-card').hide();
                    $('.notification-card[data-type="' + filter + '"]').show();
                }
            });
        });
    </script>
</body>
</html>