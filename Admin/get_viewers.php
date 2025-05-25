<?php
require 'conexão.php';

if(isset($_POST['docId'])) {
    $docId = $_POST['docId'];
    
    $sql = "SELECT u.Nome, hv.data_visualizacao 
            FROM historico_visualizacao hv
            JOIN usuario u ON hv.id_usuario = u.Id
            WHERE hv.id_documento = ?
            ORDER BY hv.data_visualizacao DESC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $docId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Usuário</th><th>Data/Hora</th></tr></thead>';
        echo '<tbody>';
        
        while($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($row['Nome']).'</td>';
            echo '<td>'.date('d/m/Y H:i', strtotime($row['data_visualizacao'])).'</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhuma visualização registrada para este documento.</p>';
    }
} else {
    echo '<p>ID do documento não especificado.</p>';
}
?>