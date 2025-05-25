<?php
/**
 * RemoveUser.php - Script para remover usuários de departamentos
 */

// Certifique-se que a conexão está incluída e a variável $mysqli está disponível
include_once __DIR__ . '/conexão.php';

// Verifica se a conexão foi estabelecida
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die("Erro na conexão com o banco de dados");
}

/**
 * Processa a remoção de usuário do departamento
 * 
 * @param mysqli $mysqli Objeto de conexão MySQLi
 * @return string|false Código de resultado ou false se não for uma requisição de remoção
 */
function processaRemocaoUsuario($mysqli) {
    // Verifica se a requisição é POST e se o botão Remover foi clicado
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Remover'])) {
        @session_start();
        
        // Verifica se o usuário está logado como chefe
        if (!isset($_SESSION['logado']) || !isset($_SESSION['id_userChefe'])) {
            return 'error=5'; // Não autorizado
        }

        $userId = $_POST['id'];
        $chefeId = $_SESSION['id_userChefe'];
        
        // Verifica se o chefe tem departamento
        $sqlChefe = "SELECT id FROM departamentos WHERE Id_Chefe = ?";
        $stmtChefe = $mysqli->prepare($sqlChefe);
        
        if (!$stmtChefe) {
            return 'error=6'; // Erro na preparação da query
        }
        
        $stmtChefe->bind_param("i", $chefeId);
        $stmtChefe->execute();
        $resultChefe = $stmtChefe->get_result();
        $departamentoChefe = $resultChefe->fetch_assoc();
        $stmtChefe->close();
        
        if (!$departamentoChefe) {
            return 'error=3'; // Chefe não tem departamento
        }
        
        // Verifica se o usuário pertence ao departamento do chefe
        $sqlUsuario = "SELECT Id_Departamento FROM usuario WHERE Id = ?";
        $stmtUsuario = $mysqli->prepare($sqlUsuario);
        
        if (!$stmtUsuario) {
            return 'error=6'; // Erro na preparação da query
        }
        
        $stmtUsuario->bind_param("i", $userId);
        $stmtUsuario->execute();
        $resultUsuario = $stmtUsuario->get_result();
        $usuario = $resultUsuario->fetch_assoc();
        $stmtUsuario->close();
        
        if (!$usuario || $usuario['Id_Departamento'] != $departamentoChefe['id']) {
            return 'error=4'; // Usuário não pertence ao departamento
        }
        
        // Remove o usuário do departamento (define Id_Departamento como 0)
        $sqlUpdate = "UPDATE usuario SET Id_Departamento = NULL WHERE Id = ?";
        $stmtUpdate = $mysqli->prepare($sqlUpdate);
        
        if (!$stmtUpdate) {
            return 'error=6'; // Erro na preparação da query
        }
        
        $stmtUpdate->bind_param("i", $userId);
        
        if ($stmtUpdate->execute()) {
            $stmtUpdate->close();
            return 'success=2'; // Remoção bem-sucedida
        } else {
            $stmtUpdate->close();
            return 'error=1'; // Erro na execução da query
        }
    }
    return false;
}

// Processa a remoção e obtém o resultado
$resultado = processaRemocaoUsuario($mysqli);

// Se houver resultado, redireciona
if ($resultado) {
    header('Location: listarMembros.php?' . $resultado);
    exit;
}
?>