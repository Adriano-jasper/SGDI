

<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])){
    header('Location: ../login.php');
    exit();
}

$id = $_SESSION['id_user'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];


?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Dashboard</title>
    
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" href="../css1/style.css">
    <link rel="stylesheet" href="../css1/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        /* Estilo para a página principal */
        .hero {
            background: linear-gradient(135deg, #0069d9 0%, #004a9f 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .feature-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0069d9;
            margin-bottom: 15px;
        }
        
        .user-welcome {
            text-align: center;
            margin-bottom: 30px;
        }
        .user-welcome img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #0069d9;
        }
        
        footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body class="app sidebar-mini">
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
    
    <!-- Conteúdo Principal -->
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
                <p>Bem-vindo ao PGDI - Plataforma de Gestão Documental Integrada</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="hero">
                    <h1>Bem-vindo, <?php echo htmlspecialchars($dados['Nome']); ?>!</h1>
                    <p>Com a PGDI, sua organização tem uma solução completa para armazenamento, gerenciamento e distribuição de documentos com segurança e eficiência.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Cartões de Funcionalidades -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-cloud-upload"></i>
                    </div>
                    <h3>Upload de Documentos</h3>
                    <p>Envie seus documentos de forma segura e organizada para a plataforma.</p>
                    <a href="upload.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <h3>Busca Avançada</h3>
                    <p>Encontre rapidamente qualquer documento compartilhado no seu Departamento</p>
                    <a href="DocumentosDoMeuDepartamento.php" class="btn btn-primary">Buscar</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-shield"></i>
                    </div>
                    <h3>Segurança</h3>
                    <p>Seus documentos protegidos com criptografia e controle de acesso.</p>
                    <a href="#" class="btn btn-primary">Saiba mais</a>
                </div>
            </div>
        </div>
        
        <?php if($dados['Permissao'] == '0' || $dados['Permissao'] == '2'): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Estatísticas Rápidas</h3>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="p-3 bg-primary text-white rounded">
                                <h4>Documentos</h4>
                                <?php
                                $sql_docs = "SELECT COUNT(*) as total FROM documentos";
                                if($dados['Permissao'] == '2') {
                                    $sql_docs = "SELECT COUNT(*) as total FROM documentos d 
                                                JOIN documento_departamento dd ON d.Id = dd.Id_documento
                                                WHERE dd.Id_departamento = '".$dados['Id_Departamento']."'";
                                }
                                $result_docs = mysqli_query($mysqli, $sql_docs);
                                $docs = mysqli_fetch_assoc($result_docs);
                                ?>
                                <h2><?php echo $docs['total']; ?></h2>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="p-3 bg-success text-white rounded">
                                <h4>Usuários</h4>
                                <?php
                                $sql_users = "SELECT COUNT(*) as total FROM usuario WHERE Ativo = 1";
                                if($dados['Permissao'] == '2') {
                                    $sql_users = "SELECT COUNT(*) as total FROM usuario 
                                                WHERE Id_Departamento = '".$dados['Id_Departamento']."' AND Ativo = 1";
                                }
                                $result_users = mysqli_query($mysqli, $sql_users);
                                $users = mysqli_fetch_assoc($result_users);
                                ?>
                                <h2><?php echo $users['total']; ?></h2>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="p-3 bg-warning text-white rounded">
                                <h4>Notificações</h4>
                                <h2><?php echo $total_notificacoes; ?></h2>
                            </div>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <div class="p-3 bg-info text-white rounded">
                                <h4>Departamentos</h4>
                                <?php
                                $sql_depts = "SELECT COUNT(*) as total FROM departamentos WHERE Ativo = 1";
                                $result_depts = mysqli_query($mysqli, $sql_depts);
                                $depts = mysqli_fetch_assoc($result_depts);
                                ?>
                                <h2><?php echo $depts['total']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Rodapé -->
    <footer class="text-center">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> PGDI - Plataforma de Gestão Documental Integrada. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    
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