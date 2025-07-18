<?php
include_once 'api/config/database.php';

$database = new Database();
$result = $database->testConnection();

echo "<h1>Configuración de Base de Datos - Raspadinha</h1>";

if ($result['status'] === 'success') {
    echo "<p style='color: green;'>✅ " . $result['message'] . "</p>";
    
    // Verificar si las tablas existen
    $conn = $database->getConnection();
    $tables = ['users', 'transactions', 'games', 'deliveries'];
    
    echo "<h2>Estado de las Tablas:</h2>";
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla '$table' existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla '$table' no existe</p>";
        }
    }
    
    echo "<h2>Configuración de la API:</h2>";
    echo "<p><strong>URL Base:</strong> " . ApiConfig::BASE_URL . "</p>";
    echo "<p><strong>Token Nitro:</strong> " . substr(ApiConfig::NITRO_API_TOKEN, 0, 10) . "...</p>";
    echo "<p style='color: green;'>✅ Configuración de API lista</p>";
    
} else {
    echo "<p style='color: red;'>❌ " . $result['message'] . "</p>";
    echo "<h2>Para solucionar:</h2>";
    echo "<ol>";
    echo "<li>Verifica que MySQL esté ejecutándose</li>";
    echo "<li>Crea la base de datos 'raspadinha_db'</li>";
    echo "<li>Importa el archivo database.sql</li>";
    echo "<li>Verifica las credenciales en api/config/database.php</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h2>Próximos pasos:</h2>";
echo "<ol>";
echo "<li>Importar database.sql en MySQL</li>";
echo "<li>Configurar credenciales en api/config/database.php si es necesario</li>";
echo "<li>Verificar que mod_rewrite esté habilitado en Apache</li>";
echo "<li>Acceder a la aplicación</li>";
echo "</ol>";

echo "<p><strong>Usuario Admin por defecto:</strong></p>";
echo "<p>Email: admin@raspadinha.com</p>";
echo "<p>Contraseña: admin123</p>";
?>