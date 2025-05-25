<?php
// Conexão com o banco de dados
include_once 'conexão.php';

// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logado'])) {
    header('Location: ../login.php');
    exit();
}

// Obtém o ID do administrador logado
$id = $_SESSION['id_Admin'];
$sql = "SELECT * FROM usuario WHERE Id = '$id'";
$resultado = mysqli_query($mysqli, $sql);
$dados = mysqli_fetch_array($resultado);

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
    header("Location: ListarUser.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Usuários - PGDI Admin</title>
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
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
    </style>
</head>
<body class="app sidebar-mini rtl">
    <!-- Navbar -->
    <header class="app-header">
        <a class="app-header__logo" href="index.php">PGDI</a>
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="fa fa-user fa-lg"></i>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    <li><a class="dropdown-item" href="page-user.php"><i class="fa fa-user fa-lg"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out fa-lg"></i> Logout</a></li>
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

    <!-- Conteúdo Principal -->
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Listar Usuários</h1>
                <p>Painel do Administrador</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Usuários</li>
                <li class="breadcrumb-item active"><a href="#">Cards</a></li>
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
                        // Consulta para obter todos os usuários
                        $sql = "SELECT usuario.*, departamentos.Nome as departamento_nome 
                                FROM usuario 
                                LEFT JOIN departamentos ON departamentos.id = usuario.Id_Departamento";
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
                                        <button type="submit" name="deletar">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i> Deletar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <!-- Scripts -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    
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
</body>
</html>