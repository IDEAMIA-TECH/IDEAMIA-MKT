<?php
/**
 * Verificación de Autenticación
 * Incluir en páginas que requieren autenticación
 */

require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/services/AuthService.php';

$db = new Database();
$auth = new AuthService($db);

if (!$auth->isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

// Obtener usuario actual
$currentUser = $auth->getCurrentUser();

