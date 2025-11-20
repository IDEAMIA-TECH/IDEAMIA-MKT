<?php
/**
 * Script para Verificar Usuario Admin
 * 
 * Verifica si el usuario admin existe y muestra información
 */

require_once __DIR__ . '/../src/helpers/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Verificando usuario admin...\n\n";
    
    // Buscar usuario admin
    $stmt = $conn->prepare("SELECT id, name, email, role, password, created_at FROM users WHERE email = 'admin@ideamia.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "✗ Usuario admin@ideamia.com NO encontrado.\n";
        echo "\nEjecuta: php database/fix-admin-password.php\n";
        exit(1);
    }
    
    echo "✓ Usuario encontrado:\n";
    echo "  ID: {$user['id']}\n";
    echo "  Nombre: {$user['name']}\n";
    echo "  Email: {$user['email']}\n";
    echo "  Rol: {$user['role']}\n";
    echo "  Creado: {$user['created_at']}\n";
    echo "  Hash contraseña: " . substr($user['password'], 0, 30) . "...\n\n";
    
    // Verificar contraseña
    echo "Probando contraseña 'admin123'...\n";
    if (password_verify('admin123', $user['password'])) {
        echo "✓ La contraseña 'admin123' es CORRECTA\n";
    } else {
        echo "✗ La contraseña 'admin123' es INCORRECTA\n";
        echo "\nEjecuta: php database/fix-admin-password.php para corregirla\n";
    }
    
    // Verificar formato del hash
    echo "\nVerificando formato del hash...\n";
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        echo "⚠ El hash necesita ser actualizado\n";
    } else {
        echo "✓ El formato del hash es correcto\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

