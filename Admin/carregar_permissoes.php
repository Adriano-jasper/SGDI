<?php
include_once 'conexão.php';

$userId = $_GET['user_id'];

// Consulta para obter todas as permissões disponíveis
$sql = "SELECT p.Id, p.Tipo, p.Descricao, 
        IFNULL(up.Estado, 0) as Estado
        FROM permissoes p
        LEFT JOIN usuario_permissoes up ON p.Id = up.Id_permissao AND up.Id_usuario = $userId
        ORDER BY p.Id";
$resultado = mysqli_query($mysqli, $sql);

while ($permissao = mysqli_fetch_assoc($resultado)):
?>
    <div class="permission-item">
        <div>
            <div class="permission-name"><?php echo $permissao['Tipo']; ?></div>
            <div class="permission-desc"><?php echo $permissao['Descricao']; ?></div>
        </div>
        <div>
            <input type="checkbox" name="permissoes[]" 
                   id="permission_<?php echo $permissao['Id']; ?>" 
                   value="<?php echo $permissao['Id']; ?>"
                   <?php echo $permissao['Estado'] ? 'checked' : ''; ?> 
                   style="display: none;">
            <button type="button" 
                    id="toggle_<?php echo $permissao['Id']; ?>" 
                    class="permission-toggle <?php echo $permissao['Estado'] ? 'permission-active' : 'permission-inactive'; ?>"
                    onclick="togglePermission(<?php echo $permissao['Id']; ?>, <?php echo $userId; ?>)">
                <?php echo $permissao['Estado'] ? 'Ativo' : 'Inativo'; ?>
            </button>
        </div>
    </div>
<?php endwhile;