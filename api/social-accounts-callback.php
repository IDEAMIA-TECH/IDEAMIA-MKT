<?php
/**
 * Callback de OAuth para Redes Sociales
 * Este endpoint NO requiere autenticación ya que es llamado por Meta
 */

session_start();
require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/helpers/UrlHelper.php';
require_once __DIR__ . '/../src/models/SocialAccount.php';
require_once __DIR__ . '/../src/services/MetaAPIService.php';

$db = new Database();
$socialAccount = new SocialAccount($db);
$metaAPI = new MetaAPIService();

// Procesar callback de OAuth
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

if ($error) {
    $errorDescription = $_GET['error_description'] ?? 'Error desconocido';
    UrlHelper::redirect('pages/clients.php?error=' . urlencode($errorDescription));
}

if (!$code) {
    UrlHelper::redirect('pages/clients.php?error=' . urlencode('Código de autorización no recibido'));
}

// Verificar state
if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
    UrlHelper::redirect('pages/clients.php?error=' . urlencode('State inválido'));
}

$clientId = $_SESSION['oauth_client_id'] ?? null;
$platform = $_SESSION['oauth_platform'] ?? 'facebook';

if (!$clientId) {
    UrlHelper::redirect('pages/clients.php?error=' . urlencode('ID de cliente no encontrado'));
}

try {
    // Intercambiar código por token
    $config = require __DIR__ . '/../config/config.php';
    $redirectUri = $config['APP_URL'] . '/api/social-accounts-callback.php';
    $tokenData = $metaAPI->exchangeCodeForToken($code, $redirectUri);
    
    // Obtener token de larga duración
    $longLivedToken = $metaAPI->refreshLongLivedToken($tokenData['access_token']);
    
    // Obtener información del usuario
    $userInfo = $metaAPI->getMe($longLivedToken['access_token']);
    
    // Obtener páginas
    $pages = $metaAPI->getPages($longLivedToken['access_token']);
    
    $accountsCreated = [];
    
    foreach ($pages as $page) {
        // Calcular fecha de expiración (60 días para tokens de larga duración)
        $expiresAt = null;
        if (isset($longLivedToken['expires_in'])) {
            $expiresAt = date('Y-m-d H:i:s', time() + $longLivedToken['expires_in']);
        } else {
            // Si no hay expires_in, asumir 60 días
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
        }
        
        // Crear cuenta de Facebook
        $accountData = [
            'client_id' => $clientId,
            'platform' => 'facebook',
            'account_id' => $page['id'],
            'account_name' => $page['name'],
            'access_token' => $page['access_token'],
            'token_expires_at' => $expiresAt,
            'status' => 'connected',
            'permissions' => $page['tasks'] ?? []
        ];
        
        // Verificar si ya existe
        $existing = $socialAccount->findByClientId($clientId);
        $exists = false;
        foreach ($existing as $acc) {
            if ($acc['account_id'] === $page['id'] && $acc['platform'] === 'facebook') {
                // Actualizar
                $socialAccount->update($acc['id'], [
                    'access_token' => $page['access_token'],
                    'token_expires_at' => $expiresAt,
                    'status' => 'connected'
                ]);
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $socialAccount->create($accountData);
        }
        
        // Intentar obtener cuenta de Instagram
        try {
            $igAccount = $metaAPI->getInstagramAccount($page['id'], $page['access_token']);
            
            if ($igAccount) {
                $igAccountData = [
                    'client_id' => $clientId,
                    'platform' => 'instagram',
                    'account_id' => $igAccount['id'],
                    'account_name' => $igAccount['username'] ?? $igAccount['name'] ?? 'Instagram',
                    'access_token' => $page['access_token'], // Usa el mismo token de la página
                    'token_expires_at' => $expiresAt,
                    'status' => 'connected',
                    'permissions' => []
                ];
                
                // Verificar si ya existe
                $igExists = false;
                foreach ($existing as $acc) {
                    if ($acc['account_id'] === $igAccount['id'] && $acc['platform'] === 'instagram') {
                        $socialAccount->update($acc['id'], [
                            'access_token' => $page['access_token'],
                            'token_expires_at' => $expiresAt,
                            'status' => 'connected'
                        ]);
                        $igExists = true;
                        break;
                    }
                }
                
                if (!$igExists) {
                    $socialAccount->create($igAccountData);
                }
            }
        } catch (Exception $e) {
            // Instagram no disponible para esta página, continuar
            error_log('Instagram no disponible: ' . $e->getMessage());
        }
    }
    
    // Limpiar sesión OAuth
    unset($_SESSION['oauth_client_id']);
    unset($_SESSION['oauth_platform']);
    unset($_SESSION['oauth_state']);
    
    // Redirigir a la página del cliente
    UrlHelper::redirect('pages/clients-detail.php?id=' . $clientId . '&connected=1');
    
} catch (Exception $e) {
    error_log('Error en callback OAuth: ' . $e->getMessage());
    UrlHelper::redirect('pages/clients.php?error=' . urlencode($e->getMessage()));
}

