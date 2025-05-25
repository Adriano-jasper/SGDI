<?php 

include 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])):
    header('Location:../login.php');
endif;

$id = $_SESSION['id_Admin'];
$sql = " SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

include 'DeleteDoc.php';

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
    <title>Listar - PGDI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- jsPDF library -->
  </head>


<style>

        /* Estilos para o visualizador de documentos */
    #documentViewerContainer {
    width: 100%;
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    }

    #documentViewer {
    width: 100%;
    height: calc(100% - 40px);
    border: none;
    flex-grow: 1;
    }

    /* Estilo para imagens no visualizador */
    .img-viewer {
    max-width: 100%;
    max-height: 100%;
    margin: auto;
    display: block;
    }

    /* Estilo para mensagem de tipo não suportado */
    .unsupported-type {
    text-align: center;
    padding: 20px;
    color: #666;
    }

    /* Estilo para o modal de visualizações */
    #viewersModal .modal-content {
    position: relative;
    background: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 5px;
    max-width: 600px;
  }

  #viewersModal .modal-close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 24px;
    cursor: pointer;
    color: black;
  }

  #viewersList {
    max-height: 60vh;
    overflow-y: auto;
  }

  #viewersList table {
    width: 100%;
    margin-top: 15px;
  }

  #viewersList table th {
    background-color: #f8f9fa;
    padding: 10px;
    text-align: left;
  }

  #viewersList table td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
  }
</style>

  <style>
     /* Estilos para o modal de visualização */
     #viewDocumentModal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        
        #viewDocumentModal .modal-container {
            position: relative;
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 1200px;
            height: 80%;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        
        .modal-close {
            position: absolute;
            right: 25px;
            top: 10px;
            color: white;
            font-size: 35px;
            font-weight: bold;
            z-index: 100;
            cursor: pointer;
        }
        
        .modal-close:hover {
            color: #ccc;
        }
        
        #documentViewerContainer {
            width: 100%;
            height: 100%;
            padding: 20px;
        }
        
        #documentViewer {
            width: 100%;
            height: 100%;
            border: none;
        }

    /* Estilo geral do card */
    .document-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #ffffff;
        overflow: hidden;
    }

    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    /* Cabeçalho do card */
    .card-header {
        background-color: #00467f; /* Azul formal */
        color: white;
        padding: 15px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    .card-header small {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    /* Corpo do card */
    .card-body {
        padding: 20px;
        background-color: #f9f9f9;
    }

    .card-text {
        font-size: 0.95rem;
        color: #333;
        line-height: 1.5;
    }

    /* Rodapé do card */
    .card-footer {
        background-color: #ffffff;
        padding: 15px;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        border-top: 1px solid #e0e0e0;
    }

    .btn-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-icon {
        background: none;
        border: none;
        color: #00467f; /* Azul formal */
        font-size: 1rem;
        padding: 8px;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-icon:hover {
        background-color: #00467f; /* Azul formal */
        color: white;
    }

    .btn-icon.btn-danger {
        color: #dc3545; /* Vermelho para ações perigosas */
    }

    .btn-icon.btn-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    
 .view-count-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #17a2b8;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

  <body class="app sidebar-mini rtl">

    <!-- Navbar-->
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
          <h1><i class="fa fa-th-list"></i> Listar Documentos</h1>
          <p>Painel do Administardor</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item">Listar</li>
          <li class="breadcrumb-item active"><a href="#">Listar Documentos</a></li>
        </ul>
      </div>

            <div class="row">
            <?php
            $idUser = $dados['Id'];
            $sql = "SELECT * FROM documentos";
            $resultado = mysqli_query($mysqli, $sql);
            while ($dado = mysqli_fetch_array($resultado)):
                $caminho_completo = '../Arquivos/' . $dado['Caminho_Doc'];
            ?>
            <div class="col-md-4 mb-4">
                <div class="card document-card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo $dado['Titulo']; ?></h5>
                        <small class="text-muted"><?php echo $dado['Data']; ?></small>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo $dado['Descricao']; ?></p>
                    </div>
                    <div class="card-footer">
                    <div class="btn-group">
                       <?php 
                          $icon = 'fa-file';
        if (strpos($dado['Caminho_Doc'], '.pdf') !== false) $icon = 'fa-file-pdf-o';
        elseif (strpos($dado['Caminho_Doc'], '.doc') !== false) $icon = 'fa-file-word-o';
        elseif (strpos($dado['Caminho_Doc'], '.xls') !== false) $icon = 'fa-file-excel-o';
        elseif (strpos($dado['Caminho_Doc'], '.ppt') !== false) $icon = 'fa-file-powerpoint-o';
        elseif (strpos($dado['Caminho_Doc'], '.jpg') !== false || strpos($dado['Caminho_Doc'], '.png') !== false || strpos($dado['Caminho_Doc'], '.gif') !== false) $icon = 'fa-file-image-o';
                         ?>
                 <span class="file-type-icon" style="margin-right: auto;">
                   <i class="fa <?php echo $icon; ?>"></i>
                    </span>
        
        <button class="btn btn-icon" onclick="viewDocument('<?php echo $caminho_completo; ?>')" title="Visualizar">
            <i class="fa fa-eye"></i>
        </button>
        <a href="download.php?id=<?php echo $dado['Id']; ?>" class="btn btn-icon" title="Download">
                                <i class="fa fa-download"></i>
                            </a>
                            <button class="btn btn-icon" onclick="showViewers(<?php echo $dado['Id']; ?>)" title="Visualizações">
                                 <i class="fa fa-eye"></i>
                                    <span class="view-count-badge"><?php $sql_views = "SELECT COUNT(*) as total FROM historico_visualizacao WHERE id_documento = ".$dado['Id'];
                                    $result_views = mysqli_query($mysqli, $sql_views);
                                    $views = mysqli_fetch_assoc($result_views);
                                    echo $views['total'];?></span>
                            </button>
                            <button class="btn btn-icon" onclick="editDocument(<?php echo $dado['Id']; ?>)" title="Editar">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-icon btn-danger" onclick="deleteDocument(<?php echo $dado['Id']; ?>)" title="Excluir">
                                <i class="fa fa-trash"></i>
                            </button>
                     </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

        </div>
      

                         <!-- Modal de Visualizações -->
<div id="viewersModal" class="modal">
    <div class="modal-content" style="width: 60%; max-height: 80vh; overflow-y: auto;">
        <span class="modal-close" onclick="fecharModalViewers()">&times;</span>
        <h4>Histórico de Visualizações</h4>
        <div id="viewersList">
            <p>Carregando informações...</p>
        </div>
    </div>
</div>

      <!-- Modal para visualização de documentos -->
  <div id="viewDocumentModal" class="modal">
    <span class="modal-close" onclick="fecharModalViewers1()">&times;</span>
    <div class="modal-container">
        <div id="documentViewerContainer">
            <iframe id="documentViewer"></iframe>
            <div id="unsupportedViewer" style="display: none; text-align: center; padding: 20px;">
                <p>Visualização não disponível para este tipo de arquivo.</p>
                <button class="btn-confirmar" onclick="downloadCurrentFile()">Download</button>
            </div>
        </div>
    </div>
</div>
 
    <!-- Essential javascripts for application to work-->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="../js/plugins/pace.min.js"></script>
    <!-- Page specific javascripts-->
    <!-- Data table plugin-->
    <script type="text/javascript" src="../js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#sampleTable').DataTable();</script>
    <!-- Google analytics script-->
<script type="text/javascript">
       // Função para visualizar documento

    
               
        function getFileType(filename) {
    const extension = filename.split('.').pop().toLowerCase();
    const types = {
        'pdf': 'pdf',
        'doc': 'word',
        'docx': 'word',
        'xls': 'excel',
        'xlsx': 'excel',
        'ppt': 'powerpoint',
        'pptx': 'powerpoint',
        'jpg': 'image',
        'jpeg': 'image',
        'png': 'image',
        'gif': 'image',
        'txt': 'text'
    };
    return types[extension] || 'unknown';
  } 
       function viewDocument(caminho) {
    const extensao = caminho.split('.').pop().toLowerCase();
    const modal = $('#viewDocumentModal');
    const viewer = $('#documentViewer');
    
    // Limpar o visualizador primeiro
    viewer.attr('src', 'about:blank');
    
    // Verificar o tipo de arquivo
    if (['pdf'].includes(extensao)) {
        // PDF - pode ser visualizado diretamente no iframe
        viewer.attr('src', caminho);
        modal.fadeIn();
    } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extensao)) {
        // Documentos do Office - usar o Visualizador do Office Online
        const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(window.location.origin + '/' + caminho)}`;
        viewer.attr('src', officeViewerUrl);
        modal.fadeIn();
    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extensao)) {
        // Imagens - mostrar diretamente
        viewer.attr('src', caminho);
        modal.fadeIn();
    } else {
        // Tipo não suportado
        alert('Visualização não disponível para este tipo de arquivo. Por favor, faça o download.');
        return;
    }
    
    // Verificar se o arquivo existe
    fetch(caminho)
        .then(response => {
            if (!response.ok) {
                viewer.attr('src', 'about:blank');
                alert('Documento não encontrado no servidor');
                modal.fadeOut();
            }
        })
        .catch(() => {
            viewer.attr('src', 'about:blank');
            alert('Erro ao carregar o documento, verifique se ele realmente existe');
            modal.fadeOut();
        });
  }


    function editDocument(id) {
        alert('Editar documento com ID: ' + id);
    }

    function deleteDocument(id) {
        if (confirm('Tem certeza que deseja excluir este documento?')) {
            window.location.href = 'Delete.php?id=' + id;
        }
    }
      
    function showViewers(docId) {
    // Carrega a lista de visualizações via AJAX
    $.ajax({
        url: 'get_viewers.php',
        method: 'POST',
        data: { docId: docId },
        success: function(response) {
            $('#viewersList').html(response);
            $('#viewersModal').fadeIn();
        },
        error: function() {
            $('#viewersList').html('<p>Erro ao carregar as visualizações</p>');
            $('#viewersModal').fadeIn();
        }
    });
  }

  function fecharModalViewers() {
    $('#viewersModal').fadeOut();
  }


    function fecharModalViewers1() {
    $('#viewDocumentModal').fadeOut();
    }
      if(document.location.hostname == 'pratikborsadiya.in') {
      	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      	ga('create', 'UA-72504830-1', 'auto');
      	ga('send', 'pageview');
      }

      function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();  }

        doc.save('relatorio.pdf');
    
   </script>
  

</body>
</html>