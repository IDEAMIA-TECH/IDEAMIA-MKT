<?php
/**
 * Endpoint de Notificaciones
 * Maneja notificaciones del sistema
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Notification.php';

$db = new Database();
$notification = new Notification($db);

// Obtener acción
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            $limit = $_GET['limit'] ?? 50;
            
            $notifications = $notification->list($currentUser['id'], [
                'unread_only' => $unreadOnly,
                'limit' => $limit
            ]);
            
            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
            break;
            
        case 'unread_count':
            $count = $notification->getUnreadCount($currentUser['id']);
            echo json_encode([
                'success' => true,
                'data' => ['count' => $count]
            ]);
            break;
            
        case 'mark_read':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de notificación requerido');
            }
            
            $result = $notification->markAsRead($id, $currentUser['id']);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación marcada como leída'
                ]);
            } else {
                throw new Exception('Error al marcar notificación');
            }
            break;
            
        case 'mark_all_read':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $result = $notification->markAllAsRead($currentUser['id']);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Todas las notificaciones marcadas como leídas'
                ]);
            } else {
                throw new Exception('Error al marcar notificaciones');
            }
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de notificación requerido');
            }
            
            $result = $notification->delete($id, $currentUser['id']);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación eliminada'
                ]);
            } else {
                throw new Exception('Error al eliminar notificación');
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

