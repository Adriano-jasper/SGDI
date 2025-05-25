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
$dadosChefe = mysqli_fetch_assoc($resultado);

// Obter ID do departamento do chefe
$sqlDepartamento = "SELECT d.* FROM departamentos d WHERE d.Id_Chefe = '$id'";
$resultadoDepartamento = mysqli_query($mysqli, $sqlDepartamento);
$dadosDepartamento = mysqli_fetch_assoc($resultadoDepartamento);

// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];

include 'ConfigUpload.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PGDI - Upload de Documentos</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="app sidebar-mini">
    <!-- Navbar-->
    <header class="app-header">
        <a class="app-header__logo" href="index.php">PGDI</a>
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications">
                    <i class="fa fa-bell-o fa-lg"></i>
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-right">
                    <li class="app-notification__title">Notificações</li>
                    <div class="app-notification__content">
                    </div>
                    <li class="app-notification__footer"><a href="#">Ver todas as notificações</a></li>
                </ul>
            </li>
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
                <p class="app-sidebar__user-name"><?php echo $dadosChefe['Nome']; ?></p>
                <p class="app-sidebar__user-designation">Chefe de Departamento</p>
            </div>
        </div>
        <ul class="app-menu">
            <li>
                <a class="app-menu__item" href="index.php">
                    <i class="app-menu__icon fa fa-dashboard"></i>
                    <span class="app-menu__label">Dashboard</span>
                </a>
            </li>
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
            <li><a class="app-menu__item active" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
            <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
            <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
        </ul>
    </aside>

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-upload"></i> Upload de Documentos</h1>
                <p>Faça upload de documentos para o sistema</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Upload de Documentos</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Upload de Documentos</h3>
                    <div class="tile-body">
                        <form action="ConfigUpload.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="titulo">Título do Documento</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="documento">Selecione o Documento</label>
                                <input type="file" class="form-control-file" id="documento" name="documento" required>
                                <small class="form-text text-muted">Formatos aceitos: PDF, DOC, DOCX, XLS, XLSX</small>
                            </div>
                            <div class="tile-footer">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-fw fa-lg fa-upload"></i>Fazer Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Essential javascripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
</body>
</html>