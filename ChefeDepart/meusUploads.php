<?php

session_start();

require 'conexão.php';

if (!isset($_SESSION['logado'])) {
    header('Location: ../login.php');
    exit();
}

$id = $_SESSION['id_userChefe'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

$sql = " SELECT * FROM departamentos WHERE Id_Chefe = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dado = mysqli_fetch_array($resultado);

// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];


include 'DeleteDoc.php';

?>

<!DOCTYPE html>
<head>
    <meta name="description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="@pratikborsadiya">
    <meta property="twitter:creator" content="@pratikborsadiya">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Vali Admin">
    <meta property="og:title" content="Vali - Free Bootstrap 4 admin theme">
    <meta property="og:url" content="http://pratikborsadiya.in/blog/vali-admin">
    <meta property="og:image" content="http://pratikborsadiya.in/blog/vali-admin/hero-social.png">
    <meta property="og:description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <title>MeusUploads - PGDI User</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
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
            background-color: #00467f;
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
            color: #00467f;
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
            background-color: #00467f;
            color: white;
        }

        .btn-icon.btn-danger {
            color: #dc3545;
        }

        .btn-icon.btn-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 40%;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        }
        .btn-confirmar, .btn-cancelar {
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 48%;
        }
        .btn-confirmar {
            background-color: green;
            color: white;
        }
        .btn-cancelar {
            background-color: red;
            color: white;
        }
        .compartilhar-opcoes {
            display: flex;
            flex-direction: column;
            align-items: start;
        }
        #modalSucesso .modal-content {
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .success-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e0f8e9;
            margin: 0 auto 15px;
            border: 4px solid #28a745;
            animation: popIn 0.5s ease-out;
        }
        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        #modalSucesso .btn-confirmar {
            display: block;
            width: 50%;
            margin: 20px auto 0;
        }
    </style>

<body class="app sidebar-mini rtl">
    <!-- Navbar -->
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
    <!-- Sidebar -->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name"><?php echo $dados['Nome'] ?></p>
          <p class="app-sidebar__user-designation">Chefe de Departamento</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
        
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
        <li><a class="app-menu__item active" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
   </aside>

    <!-- Main Content -->
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Meus Uploads</h1>
                <p>PGDI</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">PGDI</li>
                <li class="breadcrumb-item active"><a href="#">Meus Uploads</a></li>
            </ul>
        </div>

        <div class="row">
            <?php
            $idUser = $dados['Id'];
            $sql = "SELECT * FROM documentos WHERE Id_usuario ='$idUser' ";
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
                            <button class="btn btn-icon" onclick="shareDocument(<?php echo $dado['Id']; ?>)" title="Compartilhar">
                                <i class="fa fa-share-alt"></i>
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
    </main>
                <!-- Modal para visualização de documentos -->
  <div id="viewDocumentModal" class="modal">
    <span class="modal-close">&times;</span>
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

    <!-- Modal de Compartilhamento-->
     <?php   ?>
    <div id="modalCompartilhar" class="modal" style="margin-left:17%; margin-Top:52px;">
        <div class="modal-content">
            <h4>Compartilhar Documento</h4>
            <form id="formCompartilhar" action="ConfigPartilharDoc.php" method="POST">
                <div class="compartilhar-opcoes">
                    <label><input type="radio" name="compartilhar" value="departamento" checked> Meu Departamento</label>
                    <label><input type="radio" name="compartilhar" value="outro_departamento"> Outros Departamentos</label>
                    <select id="departamentos" name="id_departamento" style="display: none;">
                        <option value="">Selecione o Departamento</option>
                        <?php
                        $sql = "SELECT * FROM departamentos";
                        $resultado = mysqli_query($mysqli, $sql);
                        while ($dado = mysqli_fetch_array($resultado)) :
                        ?>
                            <option value="<?php echo $dado['id']; ?>"><?php echo $dado['Nome']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="hidden" name="id_documento" id="id_documento">
                </div>
                <button type="submit" class="btn-confirmar" name="requisitar">CONFIRMAR</button>
                <button type="button" class="btn-cancelar" onclick="fecharModalCompartilhar()">CANCELAR</button>
            </form>
        </div>
    </div>

    <!-- Modal de Sucesso -->
    <div id="modalSucesso" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="success-icon"><i class="fa fa-check"></i></div>
            <h4>Compartilhado!</h4>
            <p>Documento compartilhado com sucesso.</p>
            <button class="btn-confirmar" onclick="fecharModalSucesso()">OK</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    <script src="../js/plugins/jquery.dataTables.min.js"></script>
    <script src="../js/plugins/dataTables.bootstrap.min.js"></script>
    <script>
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
            alert('Erro ao carregar o documento');
            modal.fadeOut();
        });
}

        // Função para compartilhar documento
        function shareDocument(id) {
            $('#id_documento').val(id);
            $('#modalCompartilhar').fadeIn();
        }

        function fecharModalCompartilhar() {
            $('#modalCompartilhar').fadeOut();
        }

        function fecharModalSucesso() {
            $('#modalSucesso').fadeOut();
        }

        // Fechar modais
        $(document).on('click', '.modal-close', function() {
            $('#viewDocumentModal').fadeOut();
            $('#documentViewer').attr('src', '');
        });

        $(document).on('click', '#viewDocumentModal', function(e) {
            if (e.target === this) {
                $('#viewDocumentModal').fadeOut();
                $('#documentViewer').attr('src', '');
            }
        });

        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                if ($('#viewDocumentModal').is(':visible')) {
                    $('#viewDocumentModal').fadeOut();
                    $('#documentViewer').attr('src', '');
                }
                if ($('#modalCompartilhar').is(':visible')) {
                    $('#modalCompartilhar').fadeOut();
                }
                if ($('#modalSucesso').is(':visible')) {
                    $('#modalSucesso').fadeOut();
                }
            }
        });

        // Mostrar/ocultar dropdown de departamentos
        $('input[name="compartilhar"]').change(function() {
            if ($(this).val() === 'outro_departamento') {
                $('#departamentos').show();
            } else {
                $('#departamentos').hide();
            }
        });

        function editDocument(id) {
            alert('Editar documento com ID: ' + id);
        }

        function deleteDocument(id) {
            if (confirm('Tem certeza que deseja excluir este documento?')) {
                window.location.href = 'Delete.php?id=' + id;
            }
        }


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