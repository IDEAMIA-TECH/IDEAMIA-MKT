<?php
/**
 * Script para Corregir Contraseña del Usuario Admin
 * 
 * Este script actualiza la contraseña del usuario admin a "admin123"
 * 
 * Uso: php database/fix-admin-password.php
 */

require_once __DIR__ . '/../src/helpers/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar si existe el usuario admin
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = 'admin@ideamia.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "✗ Usuario admin@ideamia.com no encontrado.\n";
        echo "  Creando usuario admin...\n";
        
        // Crear usuario admin
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) 
                                VALUES ('Administrador', 'admin@ideamia.com', :password, 'admin', NOW(), NOW())");
        $stmt->execute(['password' => $password]);
        
        echo "✓ Usuario admin creado exitosamente.\n";
    } else {
        echo "→ Usuario admin encontrado (ID: {$user['id']})\n";
        echo "→ Actualizando contraseña...\n";
        
        // Actualizar contraseña
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE email = 'admin@ideamia.com'");
        $stmt->execute(['password' => $password]);
        
        echo "✓ Contraseña actualizada exitosamente.\n";
    }
    
    // Verificar que la contraseña funciona
    echo "\n→ Verificando contraseña...\n";
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = 'admin@ideamia.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('admin123', $user['password'])) {
        echo "✓ Verificación exitosa. La contraseña funciona correctamente.\n\n";
        echo "Credenciales:\n";
        echo "  Email: admin@ideamia.com\n";
        echo "  Contraseña: admin123\n";
    } else {
        echo "✗ Error: La verificación de contraseña falló.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

