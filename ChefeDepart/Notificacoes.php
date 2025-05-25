<?php 
include_once 'conexão.php';

session_start();

if(!isset($_SESSION['logado'])){
    header('Location:../login.php');
    exit();
}

$id = $_SESSION['id_userChefe'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

$sql = "SELECT * FROM departamentos WHERE Id_Chefe = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dado = mysqli_fetch_array($resultado);

// Consulta para obter notificações pendentes do tipo Requisicao com caminho do documento
$sql_notificacoes = "SELECT n.*, d.Titulo as DocumentoTitulo, d.Caminho_Doc 
                     FROM notificacoes n
                     LEFT JOIN documentos d ON n.Id_origem = d.Id
                     WHERE n.Id_usuario = '$id' 
                     ORDER BY n.Data DESC";
$resultado_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta name="description" content="Sistema de Gerenciamento de Documentos">
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
            margin: 2% auto;
            padding: 20px;
            width: 90%;
            max-width: 1200px;
            height: 90%;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        
        .modal-close {
            position: absolute;
            right: 25px;
            top: 10px;
            color: #aaa;
            font-size: 35px;
            font-weight: bold;
            z-index: 100;
            cursor: pointer;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .modal-toolbar {
            padding: 10px;
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            text-align: right;
        }
        
        #documentViewerContainer {
            width: 100%;
            height: calc(100% - 40px);
            margin-top: 20px;
        }
        
        #documentViewer {
            width: 100%;
            height: 100%;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        #documentViewer.loading {
            background: url('../img/loading.gif') center center no-repeat;
            background-size: 50px 50px;
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
                    <li class="app-notification__title">Você tem novas notificações.</li>
                    <div class="app-notification__content">
                        <li><a class="app-notification__item" href="javascript:;"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
                            <div>
                                <p class="app-notification__message">Nova solicitação de documento</p>
                                <p class="app-notification__meta">2 min atrás</p>
                            </div></a></li>
                    </div>
                    <li class="app-notification__footer"><a href="Notificacoes.php">Ver todas notificações.</a></li>
                </ul>
            </li>
            <!-- User Menu-->
            <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    <li><a class="dropdown-item" href="EditPerfiluser.php"><i class="fa fa-cog fa-lg"></i> Configurações</a></li>
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
            <div>
                <p class="app-sidebar__user-name"><?php echo htmlspecialchars($dados['Nome']) ?></p>
                <p class="app-sidebar__user-designation"><?php echo htmlspecialchars($dado['Nome'] ?? '') ?></p>
            </div>
        </div>
        <ul class="app-menu">
            <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-bar-chart"></i><span class="app-menu__label">Dashboard</span></a></li>
            <li><a class="app-menu__item" href="listarMembros.php"><i class="app-menu__icon fa fa-user-plus"></i><span class="app-menu__label">Membros Do meu Dep</span></a></li>
            <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
            <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
            <li><a class="app-menu__item active" href="Notificacoes.php"><i class="app-menu__icon fa fa-bell-o"></i><span class="app-menu__label">Notificações</span></a></li>
            <li><a class="app-menu__item" href="DocsCompartilhados.php"><i class="app-menu__icon fa fa-files-o"></i><span class="app-menu__label">Documentos</span></a></li>
        </ul>
    </aside>
    
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
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($notificacao = mysqli_fetch_array($resultado_notificacoes)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($notificacao['Descricao']) ?></td>
                                    <td><?php echo htmlspecialchars($notificacao['Tipo']) ?></td>
                                    <td><?php echo htmlspecialchars($notificacao['Estado']) ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($notificacao['Data'])) ?></td>
                                    <td style="display:flex; gap:5px;">
                                        <?php if(($notificacao['Estado'] == 'Pendente' && $notificacao['Tipo'] == 'Requisicao') ||($notificacao['Estado'] == 'Pendente' && $notificacao['Tipo'] == 'Partilha')  ): ?>
                                            <button class="btn btn-sm btn-info" onclick="viewDocument('../Arquivos/<?php echo htmlspecialchars($notificacao['Caminho_Doc'] ?? ''); ?>')" 
                                                 title="Visualizar Documento">
                                                 <i class="fa fa-eye"></i>
                                              </button>
                                            <form method="post" action="ConfigAceitarSolicitacao.php" style="display: inline;">
                                                <input type="hidden" name="notificacao_id" value="<?php echo $notificacao['Id'] ?>">
                                                <input type="hidden" name="Destino" value="<?php echo $notificacao['Para'] ?>">
                                                <input type="hidden" name="documento_id" value="<?php echo $notificacao['Id_origem'] ?>">
                                                <input type="hidden" name="acao" value="Aceite">
                                                <button type="submit" class="btn btn-sm btn-success" title="Aceitar">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="post" action="ConfigNegarSolicitação.php" style="display: inline;">
                                                <input type="hidden" name="notificacao_id" value="<?php echo $notificacao['Id'] ?>">
                                                <input type="hidden" name="documento_id" value="<?php echo $notificacao['Id_origem'] ?>">
                                                <input type="hidden" name="acao" value="Negada">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Rejeitar">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para visualização de documentos -->
    <div id="viewDocumentModal" class="modal">
        <span class="modal-close">&times;</span>
        <div class="modal-container">
            <div class="modal-toolbar">
                <button id="downloadBtn" class="btn btn-sm btn-primary">
                    <i class="fa fa-download"></i> Download
                </button>
            </div>
            <div id="documentViewerContainer">
                <iframe id="documentViewer"></iframe>
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
    <script type="text/javascript" src="../js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#sampleTable').DataTable({
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "Nenhum registro encontrado",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "Nenhum registro disponível",
                    "infoFiltered": "(filtrado de _MAX_ registros totais)",
                    "search": "Pesquisar:",
                    "paginate": {
                        "first": "Primeiro",
                        "last": "Último",
                        "next": "Próximo",
                        "previous": "Anterior"
                    }
                }
            });
            
            // Função para visualizar documento
            function viewDocument(caminho) {
                if (!caminho) {
                    alert('Documento não disponível para visualização');
                    return;
                }
                
                const viewer = $('#documentViewer');
                const modal = $('#viewDocumentModal');
                const extension = caminho.split('.').pop().toLowerCase();
                
                // Mostra a modal com indicador de carregamento
                viewer.addClass('loading');
                modal.fadeIn();
                
                // Verifica se é um tipo de arquivo suportado para visualização direta
                const supportedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
                if (!supportedTypes.includes(extension)) {
                    // Para tipos não suportados, oferece opção de download
                    if (confirm('Este tipo de arquivo não pode ser visualizado. Deseja baixá-lo?')) {
                        window.location.href = caminho;
                    }
                    modal.fadeOut();
                    return;
                }
                
                // Tenta carregar o documento
                fetch(caminho)
                    .then(response => {
                        if (!response.ok) throw new Error('Documento não encontrado');
                        return response.blob();
                    })
                    .then(blob => {
                        viewer.removeClass('loading');
                        if (extension === 'pdf') {
                            // Para PDF, usa visualizador embutido do navegador
                            viewer.attr('src', URL.createObjectURL(blob) + '#toolbar=1&navpanes=1');
                        } else {
                            // Para imagens, exibe diretamente
                            viewer.attr('src', URL.createObjectURL(blob));
                        }
                    })
                    .catch(error => {
                        viewer.removeClass('loading');
                        alert('Erro ao carregar o documento: ' + error.message);
                        modal.fadeOut();
                    });
            }
            
            // Fechar modal ao clicar no X ou fora do conteúdo
            $('.modal-close').click(function() {
                $('#viewDocumentModal').fadeOut();
                $('#documentViewer').attr('src', '').removeClass('loading');
            });
            
            $(window).click(function(event) {
                if ($(event.target).is('#viewDocumentModal')) {
                    $('#viewDocumentModal').fadeOut();
                    $('#documentViewer').attr('src', '').removeClass('loading');
                }
            });
            
            // Botão de download
            $('#downloadBtn').click(function() {
                const src = $('#documentViewer').attr('src');
                if (src && src !== 'about:blank') {
                    window.location.href = src.replace(/#.*$/, '');
                }
            });
            
            // Expor a função globalmente para ser chamada no HTML
            window.viewDocument = viewDocument;

        });
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