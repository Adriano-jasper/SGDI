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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="Vali is a responsive and free admin theme built with Bootstrap 4, SASS and PUG.js. It's fully customizable and modular.">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentos Compartilhados - PGDI User</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
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
            margin-bottom: 20px;
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

        .document-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-rascunho {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-pendente {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-aprovado {
            background-color: #28a745;
            color: white;
        }
        
        .status-rejeitado {
            background-color: #dc3545;
            color: white;
        }
        
        .status-arquivado {
            background-color: #6c757d;
            color: white;
        }
    </style>
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
<body class="app sidebar-mini rtl">
     <!-- Navbar-->
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
    
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
      <div class="app-sidebar__user">
        <div>
          <p class="app-sidebar__user-name"><?php echo $dados['Nome']; ?></p>
          <p class="app-sidebar__user-designation">Chefe de Departamento</p>
        </div>
      </div>
      <ul class="app-menu">
        <li><a class="app-menu__item" href="index.php"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>
        
        <li class="treeview is-expanded">
          <a class="app-menu__item" href="#" data-toggle="treeview">
            <i class="app-menu__icon fa fa-building"></i>
            <span class="app-menu__label">Meu Departamento</span>
            <i class="treeview-indicator fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a class="treeview-item" href="listarMembros.php"><i class="icon fa fa-users"></i> Membros</a></li>
            <li><a class="treeview-item active" href="DocsCompartilhados.php"><i class="icon fa fa-files-o"></i> Documentos</a></li>
            <li><a class="treeview-item" href="Convocatorias.php"><i class="icon fa fa-bullhorn"></i> Convocatórias</a></li>
          </ul>
        </li>
        
        <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
        <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-share-alt"></i> Documentos Compartilhados</h1>
                <p>Documentos compartilhados com seu departamento</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">PGDI</li>
                <li class="breadcrumb-item active"><a href="#">Documentos Compartilhados</a></li>
            </ul>
        </div>

        <div class="row">
            <?php
            // Obter o departamento do usuário atual
            $idDepartamento = $dados['Id_Departamento'];
            
            if ($idDepartamento) {
                // Consulta para obter documentos compartilhados com o departamento do usuário
                $sql = "SELECT d.*, u.Nome as NomeUsuario 
                        FROM documentos d
                        JOIN documento_departamento dd ON d.Id = dd.Id_documento
                        JOIN usuario u ON d.Id_usuario = u.Id
                        WHERE dd.Id_departamento = '$idDepartamento' And Estado = 'Aprovado' 
                        ORDER BY d.Data DESC";
                
                $resultado = mysqli_query($mysqli, $sql);
                
                if (mysqli_num_rows($resultado)){ 
                    while ($dado = mysqli_fetch_array($resultado)) {
                        $caminho_completo = '../Arquivos/' . $dado['Caminho_Doc'];
                        $statusClass = 'status-' . strtolower($dado['Estado']);
            ?>
            <div class="col-md-4">
                <div class="card document-card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($dado['Titulo']); ?></h5>
                        <small>Enviado por: <?php echo htmlspecialchars($dado['NomeUsuario']); ?></small>
                        <div style="margin-top: 5px;">
                            <span class="document-status <?php echo $statusClass; ?>">
                                <?php echo $dado['Estado']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo htmlspecialchars($dado['Descricao']); ?></p>
                        <p class="card-text"><small class="text-muted">Compartilhado em: <?php echo date('d/m/Y H:i', strtotime($dado['Data'])); ?></small></p>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                                  <button class="btn btn-icon" onclick="viewDocument('<?php echo $caminho_completo; ?>', <?php echo $dado['Id']; ?>)" title="Visualizar">
                                    <i class="fa fa-eye"></i>
                                </button>
                            <a href="download.php?id=<?php echo $dado['Id']; ?>" class="btn btn-icon" title="Download">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">Nenhum documento compartilhado com seu departamento.</div></div>';
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-warning">Você não está associado a nenhum departamento.</div></div>';
            }
            ?>
        </div>
    </main>

    <!-- Modal para visualização de documentos -->
    <div id="viewDocumentModal" class="modal">
        <span class="modal-close">&times;</span>
        <div class="modal-container">
            <div id="documentViewerContainer">
                <iframe id="documentViewer"></iframe>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    <script>
        // Função para visualizar documento
        
        function viewDocument(caminho, docId) {
    // Primeiro registra a visualização
    $.ajax({
        url: 'registrar_visualizacao.php',
        method: 'POST',
        data: { id_documento: docId },
        dataType: 'json',
        success: function(response) {
            if(response.status !== 'success') {
                console.error('Erro ao registrar visualização:', response.message);
            }
            // Continua com a abertura do documento
            $('#documentViewer').attr('src', caminho);
            $('#viewDocumentModal').fadeIn();
            
            fetch(caminho)
                .then(response => {
                    if (!response.ok) {
                        $('#documentViewer').attr('src', 'about:blank');
                        alert('Documento não encontrado no servidor');
                        $('#viewDocumentModal').fadeOut();
                    }
                })
                .catch(() => {
                    $('#documentViewer').attr('src', 'about:blank');
                    alert('Erro ao carregar o documento');
                    $('#viewDocumentModal').fadeOut();
                });
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:', error);
            // Mesmo com erro, permite visualizar o documento
            $('#documentViewer').attr('src', caminho);
            $('#viewDocumentModal').fadeIn();
        }
    });
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
            }
        });
    </script>
</body>
</html>