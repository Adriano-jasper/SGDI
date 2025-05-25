<?php 

require 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])):
    header('Location:../login.php');
endif;

$id = $_SESSION['id_user'];
$sql = " SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];


include 'ConfigUpload.php';

require_once 'converter_para_pdf.php';

if(isset($_POST['enviar'])) {
    $titulo = mysqli_real_escape_string($mysqli, $_POST['titulo']);
    $descricao = mysqli_real_escape_string($mysqli, $_POST['descricao']);
    
    $formatosPermitidos = array("pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx");
    $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
    
    if(in_array($extensao, $formatosPermitidos)) {
        $pasta = "../Arquivos/";
        $temporario = $_FILES['arquivo']['tmp_name'];
        $novoNome = uniqid() . "." . $extensao;
        
        if(move_uploaded_file($temporario, $pasta . $novoNome)) {
            // Se for um documento Office, gerar versão PDF
            $pdfPath = null;
            if(isOfficeDocument($_FILES['arquivo']['name'])) {
                $resultado = convertToPDF($pasta . $novoNome, $pasta);
                if($resultado['success']) {
                    $pdfPath = $resultado['pdf_path'];
                }
            }
            
            // Inserir informações no banco de dados
            $sql = "INSERT INTO documentos (Titulo, Descricao, Caminho_Doc, Id_usuario, Caminho_PDF) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssss", $titulo, $descricao, $novoNome, $id, $pdfPath);
            
            if($stmt->execute()) {
                $id_documento = $mysqli->insert_id;
                
                // Associar documento ao departamento do usuário
                if($dados['Id_Departamento']) {
                    $sql = "INSERT INTO documento_departamento (Id_documento, Id_departamento) 
                            VALUES (?, ?)";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("ii", $id_documento, $dados['Id_Departamento']);
                    $stmt->execute();
                }
                
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Documento enviado com sucesso!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'meusUploads.php';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao enviar documento!'
                    });
                </script>";
            }
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Formato de arquivo não permitido!'
            });
        </script>";
    }
}

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
    <title>Upload - PGDI</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
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
  <body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="index.php">PGDI</a>
      <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
      <!-- Navbar Right Menu-->
    
        <!--Notification Menu-->
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
            <li><a class="app-menu__item " href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
            
            <?php if($dados['Id_Departamento'] != NULL): ?>
              
            <li><a class="app-menu__item active" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
            <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-files-o"></i><span class="app-menu__label">Meus Documentos</span></a></li>
            <li><a class="app-menu__item" href="DocumentosDoMeuDepartamento.php"><i class="app-menu__icon fa fa-share-alt"></i><span class="app-menu__label">Documentos Compartilhados</span></a></li>
        
              <?php endif; ?>
            </div>
            
        </ul>
    </aside>
    
    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-upload"></i>Fazer Upload</h1>
          <p>Usuario</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">PGDI</li>
          <li class="breadcrumb-item"><a href="upload.php">Fazer Upload</a></li>
        </ul>
      </div>
      <div class="row">
        </div>
        <div class="clearix"></div>
        <div class="col-md-12">
          <div class="tile">
            <h3 class="tile-title">Fazer Upload</h3>
            <div class="tile-body">
                <form class="form-horizontal" method="POST" action="upload.php?id=<?php echo $dados['Id']?>" enctype="multipart/form-data">
                  <div class="form-group row">
                    <label class="control-label col-md-3" >Nome</label>
                    <div class="col-md-8">
                      <input class="form-control" type="text" placeholder="Nome do documento" name="nome" required>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="control-label col-md-3">Descrição</label>
                    <div class="col-md-8">
                      <textarea class="form-control" rows="4" placeholder="Descrição do Documento" name="descricao"></textarea>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="control-label col-md-3">Carregar Documento</label>
                    <div class="col-md-8">
                      <input class="form-control" type="file" name="Documento" required>
                    </div>
                  </div>
                  <div class="tile-footer">
              <button class="btn btn-primary" type="submit" name="upload"><i class="fa fa-fw fa-lg fa-check-circle"></i>Upload</button>
            </div>
                </form>
              </div>
           
          </div>
        </div>
        </div>
      </div>
    </main>

    <?php
        // Mostrar a mensagem de sucesso ou erro usando o SweetAlert
        if (!empty($mensagem)) {
          echo '<script>
              Swal.fire({
                  icon: "success",
                  title: "aviso",
                  text: "'.$mensagem[key($mensagem)].'",
                  confirmButtonColor:"#3085d6",
                  confirmButtonText: "Ok"
              });
          </script>';
      }
        ?>

    <!-- Essential javascripts for application to work-->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="../js/plugins/pace.min.js"></script>
    <!-- Page specific javascripts-->
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