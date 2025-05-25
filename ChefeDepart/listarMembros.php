<?php

include_once 'conexão.php';
include 'RemoveUser.php';
session_start();

if (!isset($_SESSION['logado'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_SESSION['id_userChefe'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

$sql = "SELECT * FROM departamentos WHERE Id_Chefe = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dado = mysqli_fetch_array($resultado);
$idep = $dado['id']; // ID do departamento


// Verificar se há notificações não visualizadas
$sql_notificacoes = "SELECT COUNT(*) as total FROM notificacoes WHERE Id_usuario = '$id' AND Visualizada = 0";
$result_notificacoes = mysqli_query($mysqli, $sql_notificacoes);
$notificacoes = mysqli_fetch_assoc($result_notificacoes);
$total_notificacoes = $notificacoes['total'];


// Processar alterações de permissões se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_permissoes'])) {
    $userId = $_POST['user_id'];
    $permissoes = $_POST['permissoes'] ?? [];
    
    // Primeiro, desativar todas as permissões do usuário
    $sql = "UPDATE usuario_permissoes SET Estado = 0 WHERE Id_usuario = $userId";
    mysqli_query($mysqli, $sql);
    
    // Ativar apenas as permissões selecionadas
    foreach ($permissoes as $permissaoId) {
        $sql = "UPDATE usuario_permissoes SET Estado = 1 
                WHERE Id_usuario = $userId AND Id_permissao = $permissaoId";
        mysqli_query($mysqli, $sql);
    }
    
    $_SESSION['mensagem'] = "Permissões atualizadas com sucesso!";
    header("Location: ListarMembros.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
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
    <title>Add - PGDI</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        
        /* Estilos para a modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .permission-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .permission-name {
            font-weight: bold;
        }
        .permission-toggle {
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .permission-active {
            background-color: #4CAF50;
            color: white;
        }
        .permission-inactive {
            background-color: #f44336;
            color: white;
        }
        .btn-permissoes {
            background-color: #6c757d !important;
        }
        .btn-permissoes:hover {
            background-color: #5a6268 !important;
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
      
    
        .user-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin: 10px;
            width: 300px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background-color: #fff;
        }
        .user-card:hover {
            transform: scale(1.05);
        }
        .user-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }
        .user-card h3 {
            margin: 0;
            font-size: 1.5em;
            color: #333;
        }
        .user-card p {
            margin: 5px 0;
            color: #666;
        }
        .user-actions {
            margin-top: 10px;
        }
        .user-actions button {
            background-color: #00467f;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }
        .user-actions button:hover {
            background-color: #003366;
        }
        .user-actions a {
            color: white;
            text-decoration: none;
        }
        #modalUserList {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        #modalUserList .user-card {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
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
          <p class="app-sidebar__user-designation"><?php echo $dado['Nome']; ?></p>
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
            <li><a class="treeview-item active" href="listarMembros.php"><i class="icon fa fa-users"></i> Membros</a></li>
            <li><a class="treeview-item" href="DocsCompartilhados.php"><i class="icon fa fa-files-o"></i> Documentos</a></li>
            <li><a class="treeview-item" href="Convocatorias.php"><i class="icon fa fa-bullhorn"></i> Convocatórias</a></li>
          </ul>
        </li>
        
        <li><a class="app-menu__item" href="upload.php"><i class="app-menu__icon fa fa-upload"></i><span class="app-menu__label">Fazer Uploads</span></a></li>
        <li><a class="app-menu__item" href="meusUploads.php"><i class="app-menu__icon fa fa-clipboard"></i><span class="app-menu__label">Meus Uploads</span></a></li>
        <li><a class="app-menu__item" href="Relatorio.php"><i class="app-menu__icon fa fa-file-text"></i><span class="app-menu__label">Relatório</span></a></li>
      </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Adição de Usuário ao meu departamento</h1>
                <p>PGDI</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">PGDI</li>
                <li class="breadcrumb-item"><a href="#">Adição de Usuário ao meu departamento</a></li>
            </ul>
        </div>

             <?php if (isset($_SESSION['mensagem'])): ?>
                 <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?>
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
             <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                    <?php
if (isset($_GET['success'])) {
    if ($_GET['success'] == '1') {
        echo '<div class="alert alert-success">Usuário adicionado com sucesso!</div>';
    } elseif ($_GET['success'] == '2') {
        echo '<div class="alert alert-success">Usuário removido do departamento com sucesso!</div>';
    }
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1': $msg = 'Erro ao processar a requisição.'; break;
        case '2': $msg = 'Usuário não encontrado.'; break;
        case '3': $msg = 'Você não tem um departamento associado.'; break;
        case '4': $msg = 'Este usuário não pertence ao seu departamento.'; break;
        case '5': $msg = 'Acesso não autorizado.'; break;
        default: $msg = 'Erro desconhecido.'; break;
    }
    echo '<div class="alert alert-danger">' . $msg . '</div>';
}
?>
                        <div class="form-group">
                            <input type="text" id="searchBar" class="form-control" placeholder="Pesquisar membros...">
                        </div>
                        <button class="btn btn-primary" id="addMemberBtn">Adicionar Novo Membro</button>
                        
                           <div class="tile-body">
                        <?php
                        // Consulta para obter todos os usuários
                            
                            $sql = "SELECT * FROM usuario WHERE Id_Departamento = '$idep' And Permissao != '2' ";
                            $resultado = mysqli_query($mysqli, $sql);
                            while ($dados = mysqli_fetch_array($resultado)):
                            ?>

                            <div class="user-card">
                                <img src="../Usuário/Fotos/<?php 
                                    if(empty($dados['Caminho_da_Ft'])) {
                                        echo "silhueta.webp"; 
                                    } else {
                                        echo $dados['Caminho_da_Ft']; 
                                    }
                                ?>" alt="Foto de Perfil">
                                <h3><?php echo $dados['Nome']; ?></h3>
                                <p><strong>Email:</strong> <?php echo $dados['Email']; ?></p>
                                <p><strong>Telefone:</strong> <?php echo $dados['Telefone']; ?></p>
                                <p><strong>Departamento:</strong> <?php echo $dados['departamento_nome'] ?? 'Nenhum'; ?></p>
                                <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y', strtotime($dados['Created_at'])); ?></p>
                                <p><strong>Tipo de Usuário:</strong> 
                                    <?php 
                                        switch($dados['Permissao']) {
                                            case '0': echo 'Administrador'; break;
                                            case '1': echo 'Normal'; break;
                                            case '2': echo 'Chefe'; break;
                                            default: echo $dados['Permissao'];
                                        }
                                    ?>
                                </p>
                                <div class="user-actions">
                                    <button>
                                        <a href="EditarUser.php?Id=<?php echo $dados['Id']; ?>" style="color: white; text-decoration: none;">
                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar
                                        </a>
                                    </button>
                                    <button class="btn-permissoes" onclick="openPermissionsModal(<?php echo $dados['Id']; ?>, '<?php echo $dados['Nome']; ?>')">
                                        <i class="fa fa-cog" aria-hidden="true"></i> Permissões
                                    </button>
                                    <form action="Delete.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $dados['Id']; ?>">
                                        <button type="submit" name="Remover">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i> Deletar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    
    <!-- Modal de Permissões -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Gerenciar Permissões para <span id="userName"></span></h2>
            <form id="permissionsForm" method="POST">
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="atualizar_permissoes" value="1">
                <div id="permissionsList">
                    <!-- As permissões serão carregadas aqui via AJAX -->
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" onclick="closeModal()" style="background-color: #6c757d; margin-right: 10px;">Cancelar</button>
                    <button type="submit" style="background-color: #28a745;">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Adicionar Novo Membro -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="ConfigAdd.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMemberModalLabel">Adicionar Novo Membro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="departmentId" value="<?php echo $idep; ?>">
                        <input type="text" id="searchModalBar" class="form-control" placeholder="Pesquisar usuários...">
                        <div id="modalUserList">
                            <?php
                            $sql = "SELECT * FROM usuario WHERE Id_Departamento is null And Permissao != '0' ";
                            $resultado = mysqli_query($mysqli, $sql);
                            while ($dados = mysqli_fetch_array($resultado)):
                            ?>
                                <div class="user-card">
                                    <img src="../Usuário/Fotos/<?php if(empty($dados['Caminho_da_Ft'])):echo "silhueta.webp"; else:echo $dados['Caminho_da_Ft']; endif;?>" alt="Foto de Perfil">
                                    <h3><?php echo $dados['Nome']; ?></h3>
                                    <p><strong>Email:</strong> <?php echo $dados['Email']; ?></p>
                                    <p><strong>Telefone:</strong> <?php echo $dados['Telefone']; ?></p>
                                    <p><strong>Data de Criação:</strong> <?php echo $dados['Created_at']; ?></p>
                                    <p><strong>Tipo de Usuário:</strong> <?php echo $dados['Permissao']; ?></p>
                                    <button type="submit" name="userId" value="<?php echo $dados['Id']; ?>" class="btn btn-primary btn-sm">Adicionar</button>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </form>
            </div>
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
        // Função para abrir a modal de permissões
        function openPermissionsModal(userId, userName) {
            document.getElementById('userName').textContent = userName;
            document.getElementById('modalUserId').value = userId;
            
            // Carregar as permissões via AJAX
            $.ajax({
                url: 'carregar_permissoes.php',
                type: 'GET',
                data: { user_id: userId },
                success: function(response) {
                    $('#permissionsList').html(response);
                    $('#permissionsModal').show();
                },
                error: function() {
                    alert('Erro ao carregar permissões.');
                }
            });
        }
        
        // Função para fechar a modal
        function closeModal() {
            $('#permissionsModal').hide();
        }
        
        // Fechar a modal ao clicar fora dela
        window.onclick = function(event) {
            const modal = document.getElementById('permissionsModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Função para alternar permissões
        function togglePermission(permissionId, userId) {
            const checkbox = document.getElementById('permission_' + permissionId);
            const button = document.getElementById('toggle_' + permissionId);
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                button.className = 'permission-toggle permission-active';
                button.textContent = 'Ativo';
            } else {
                button.className = 'permission-toggle permission-inactive';
                button.textContent = 'Inativo';
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            // Barra de pesquisa dinâmica
            $('#searchBar').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.user-card').each(function() {
                    const userName = $(this).find('h3').text().toLowerCase();
                    if (userName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Abrir modal ao clicar no botão de adicionar membro
            $('#addMemberBtn').click(function() {
                $('#addMemberModal').modal('show');
            });

            // Barra de pesquisa dinâmica na modal
            $('#searchModalBar').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('#modalUserList .user-card').each(function() {
                    const userName = $(this).find('h3').text().toLowerCase();
                    if (userName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>

<script>
    // Função para esconder as mensagens de alerta após 10 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 10000); // 10000 milissegundos = 10 segundos
</script>
</body>
</html>