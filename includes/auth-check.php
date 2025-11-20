<?php
/**
 * Verificación de Autenticación
 * Incluir en páginas que requieren autenticación
 */

require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/helpers/UrlHelper.php';
require_once __DIR__ . '/../src/services/AuthService.php';

$db = new Database();
$auth = new AuthService($db);

if (!$auth->isLoggedIn()) {
    UrlHelper::redirect('index.php');
}

// Obtener usuario actual
$currentUser = $auth->getCurrentUser();

