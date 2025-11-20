<?php
/**
 * Endpoint de Redes Sociales
 * Maneja conexión OAuth y gestión de cuentas
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/SocialAccount.php';
require_once __DIR__ . '/../src/services/MetaAPIService.php';

$db = new Database();
$socialAccount = new SocialAccount($db);
$metaAPI = new MetaAPIService();

// Obtener acción
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $clientId = $_GET['client_id'] ?? null;
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            $accounts = $socialAccount->findByClientId($clientId);
            
            echo json_encode([
                'success' => true,
                'data' => $accounts
            ]);
            break;
            
        case 'connect':
            // Iniciar flujo OAuth
            $clientId = $_GET['client_id'] ?? $input['client_id'] ?? null;
            $platform = $_GET['platform'] ?? $input['platform'] ?? 'facebook';
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            // Guardar client_id en sesión para el callback
            $_SESSION['oauth_client_id'] = $clientId;
            $_SESSION['oauth_platform'] = $platform;
            
            $config = require __DIR__ . '/../config/config.php';
            $redirectUri = $config['APP_URL'] . '/api/social-accounts-callback.php';
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth_state'] = $state;
            
            $authUrl = $metaAPI->getAuthUrl($redirectUri, $state);
            
            echo json_encode([
                'success' => true,
                'auth_url' => $authUrl
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cuenta requerido');
            }
            
            $account = $socialAccount->findById($id);
            
            if (!$account) {
                throw new Exception('Cuenta no encontrada');
            }
            
            // No retornar tokens en la respuesta
            unset($account['access_token']);
            unset($account['refresh_token']);
            
            echo json_encode([
                'success' => true,
                'data' => $account
            ]);
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cuenta requerido');
            }
            
            $result = $socialAccount->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cuenta eliminada exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar cuenta');
            }
            break;
            
        case 'status':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cuenta requerido');
            }
            
            $status = $socialAccount->checkStatus($id);
            
            if (!$status) {
                throw new Exception('Cuenta no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $status
            ]);
            break;
            
        case 'refresh_token':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cuenta requerido');
            }
            
            // Obtener cuenta
            $account = $socialAccount->findById($id);
            
            if (!$account) {
                throw new Exception('Cuenta no encontrada');
            }
            
            // Intentar refrescar token
            try {
                $newToken = $metaAPI->refreshLongLivedToken($account['access_token']);
                
                $expiresAt = null;
                if (isset($newToken['expires_in'])) {
                    $expiresAt = date('Y-m-d H:i:s', time() + $newToken['expires_in']);
                } else {
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
                }
                
                $socialAccount->update($id, [
                    'access_token' => $newToken['access_token'],
                    'token_expires_at' => $expiresAt,
                    'status' => 'connected'
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Token refrescado exitosamente'
                ]);
            } catch (Exception $e) {
                // Si falla, marcar como expirado
                $socialAccount->update($id, ['status' => 'expired']);
                throw new Exception('Error al refrescar token: ' . $e->getMessage());
            }
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

