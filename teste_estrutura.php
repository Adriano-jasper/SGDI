<?php
echo "<h2>Verificação de Estrutura</h2>";

// Verificar diretório atual
echo "Diretório atual: " . getcwd() . "<br>";

// Verificar se a pasta uploads existe
$uploadsDir = 'uploads/';
if (file_exists($uploadsDir)) {
    echo "✅ Pasta uploads existe<br>";
    echo "Caminho completo: " . realpath($uploadsDir) . "<br>";
    echo "Permissões: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "<br>";
    
    // Listar arquivos
    echo "<h3>Arquivos na pasta uploads:</h3>";
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "- " . htmlspecialchars($file) . " (" . filesize($uploadsDir . $file) . " bytes)<br>";
        }
    }
} else {
    echo "❌ Pasta uploads não existe<br>";
    echo "Tentando criar...<br>";
    if (mkdir($uploadsDir, 0777, true)) {
        echo "✅ Pasta uploads criada com sucesso<br>";
    } else {
        echo "❌ Erro ao criar pasta uploads<br>";
    }
}

// Verificar extensões PHP necessárias
echo "<h3>Extensões PHP:</h3>";
$required_extensions = ['zip', 'gd', 'xml'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ {$ext} está instalada<br>";
    } else {
        echo "❌ {$ext} não está instalada<br>";
    }
}

// Verificar permissões de escrita
if (is_writable($uploadsDir)) {
    echo "✅ Pasta uploads tem permissão de escrita<br>";
} else {
    echo "❌ Pasta uploads não tem permissão de escrita<br>";
} 