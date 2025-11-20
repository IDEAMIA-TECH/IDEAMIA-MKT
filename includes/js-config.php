<?php
/**
 * Configuración JavaScript
 * Genera variables JavaScript con las URLs base de la aplicación
 */

require_once __DIR__ . '/../src/helpers/UrlHelper.php';

$baseUrl = UrlHelper::url();
$apiBaseUrl = UrlHelper::url('api');
?>
<script>
// Configuración de URLs para JavaScript
const APP_BASE_URL = '<?php echo $baseUrl; ?>';
const API_BASE_URL = '<?php echo $apiBaseUrl; ?>';

// Funciones helper para JavaScript
function url(path) {
    if (!path) return APP_BASE_URL || '/';
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return (APP_BASE_URL || '') + '/' + cleanPath;
}

function apiUrl(endpoint) {
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    return (API_BASE_URL || '/api') + '/' + cleanEndpoint;
}

function appUrl(path) {
    return url(path);
}
</script>
