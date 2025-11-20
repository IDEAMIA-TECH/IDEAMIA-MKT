<?php
/**
 * Endpoint de Autenticación
 * Maneja login, logout, verificación de sesión
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/services/AuthService.php';

$db = new Database();
$auth = new AuthService($db);

// Obtener acción
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            $result = $auth->login($email, $password);
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(401);
            }
            
            echo json_encode($result);
            break;
            
        case 'logout':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $result = $auth->logout();
            echo json_encode($result);
            break;
            
        case 'check_session':
        case 'me':
            $user = $auth->getCurrentUser();
            
            if ($user) {
                echo json_encode([
                    'success' => true,
                    'valid' => true,
                    'user' => $user
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'valid' => false,
                    'error' => 'No hay sesión activa'
                ]);
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

