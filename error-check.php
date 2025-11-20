<?php
/**
 * Script de Verificación de Errores
 * Ejecutar este archivo para diagnosticar problemas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verificación de Configuración - IDEAMIA Marketing Platform</h1>";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar PHP
echo "<h2>1. Versión de PHP</h2>";
$phpVersion = phpversion();
echo "Versión: <strong>{$phpVersion}</strong><br>";
if (version_compare($phpVersion, '8.0.0', '<')) {
    $errors[] = "PHP 8.0+ requerido. Versión actual: {$phpVersion}";
    echo "<span style='color:red'>✗ Versión insuficiente</span><br>";
} else {
    $success[] = "Versión de PHP OK";
    echo "<span style='color:green'>✓ Versión OK</span><br>";
}

// 2. Verificar extensiones
echo "<h2>2. Extensiones PHP</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'openssl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color:green'>✓ {$ext}</span><br>";
        $success[] = "Extensión {$ext} cargada";
    } else {
        $errors[] = "Extensión {$ext} no está cargada";
        echo "<span style='color:red'>✗ {$ext} NO encontrada</span><br>";
    }
}

// 3. Verificar archivos
echo "<h2>3. Archivos del Sistema</h2>";
$requiredFiles = [
    'config/config.php' => 'Archivo de configuración',
    'src/helpers/Database.php' => 'Clase Database',
    'src/models/User.php' => 'Modelo User',
    'src/services/AuthService.php' => 'Servicio AuthService',
    'src/helpers/Validator.php' => 'Helper Validator',
];

foreach ($requiredFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<span style='color:green'>✓ {$file}</span> - {$description}<br>";
        $success[] = "Archivo {$file} existe";
    } else {
        $errors[] = "Archivo {$file} no encontrado";
        echo "<span style='color:red'>✗ {$file} NO encontrado</span> - {$description}<br>";
    }
}

// 4. Verificar configuración
echo "<h2>4. Configuración</h2>";
if (file_exists(__DIR__ . '/config/config.php')) {
    try {
        $config = require __DIR__ . '/config/config.php';
        
        $requiredConfig = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($requiredConfig as $key) {
            if (isset($config[$key]) && !empty($config[$key])) {
                echo "<span style='color:green'>✓ {$key} configurado</span><br>";
                $success[] = "Config {$key} OK";
            } else {
                $warnings[] = "Config {$key} no configurado o vacío";
                echo "<span style='color:orange'>⚠ {$key} no configurado</span><br>";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error al cargar config.php: " . $e->getMessage();
        echo "<span style='color:red'>✗ Error al cargar config.php</span><br>";
    }
} else {
    $errors[] = "config/config.php no existe";
}

// 5. Verificar conexión a BD
echo "<h2>5. Conexión a Base de Datos</h2>";
if (file_exists(__DIR__ . '/src/helpers/Database.php') && file_exists(__DIR__ . '/config/config.php')) {
    try {
        require_once __DIR__ . '/src/helpers/Database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        // Probar consulta simple
        $result = $conn->query("SELECT 1");
        if ($result) {
            echo "<span style='color:green'>✓ Conexión a BD exitosa</span><br>";
            $success[] = "Conexión BD OK";
            
            // Verificar tablas
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "Tablas encontradas: <strong>" . count($tables) . "</strong><br>";
            if (count($tables) > 0) {
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>{$table}</li>";
                }
                echo "</ul>";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error de conexión a BD: " . $e->getMessage();
        echo "<span style='color:red'>✗ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }
} else {
    $warnings[] = "No se puede verificar BD (archivos faltantes)";
}

// Resumen
echo "<h2>Resumen</h2>";
echo "<p><strong>Exitosos:</strong> " . count($success) . "</p>";
echo "<p><strong>Advertencias:</strong> " . count($warnings) . "</p>";
echo "<p><strong>Errores:</strong> " . count($errors) . "</p>";

if (count($errors) > 0) {
    echo "<h3 style='color:red'>Errores encontrados:</h3><ul>";
    foreach ($errors as $error) {
        echo "<li style='color:red'>{$error}</li>";
    }
    echo "</ul>";
}

if (count($warnings) > 0) {
    echo "<h3 style='color:orange'>Advertencias:</h3><ul>";
    foreach ($warnings as $warning) {
        echo "<li style='color:orange'>{$warning}</li>";
    }
    echo "</ul>";
}

if (count($errors) === 0 && count($warnings) === 0) {
    echo "<h3 style='color:green'>✓ Todo está correcto. El sistema debería funcionar.</h3>";
}

