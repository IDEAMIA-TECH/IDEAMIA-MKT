<?php
/**
 * Endpoint de Publicaciones
 * Maneja CRUD de publicaciones y calendario
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Post.php';
require_once __DIR__ . '/../src/services/PostService.php';
require_once __DIR__ . '/../src/helpers/Validator.php';

$db = new Database();
$post = new Post($db);
$postService = new PostService($db);

// Obtener acción
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $filters = [
                'page' => $_GET['page'] ?? 1,
                'per_page' => $_GET['per_page'] ?? 20,
                'client_id' => $_GET['client_id'] ?? '',
                'status' => $_GET['status'] ?? '',
                'platform' => $_GET['platform'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'order_by' => $_GET['order_by'] ?? 'scheduled_at',
                'order_dir' => $_GET['order_dir'] ?? 'ASC'
            ];
            
            $result = $post->list($filters);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'calendar':
            $filters = [
                'client_id' => $_GET['client_id'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'status' => $_GET['status'] ?? ''
            ];
            
            $events = $post->getCalendarEvents($filters);
            echo json_encode([
                'success' => true,
                'data' => $events
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de publicación requerido');
            }
            
            $postData = $post->findById($id);
            
            if (!$postData) {
                throw new Exception('Publicación no encontrada');
            }
            
            // Decodificar JSON fields
            if ($postData['media_urls']) {
                $postData['media_urls'] = json_decode($postData['media_urls'], true);
            }
            if ($postData['tags']) {
                $postData['tags'] = json_decode($postData['tags'], true);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $postData
            ]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            // Validar
            $validation = $postService->validate($input);
            if (!$validation['valid']) {
                throw new Exception('Errores de validación: ' . implode(', ', $validation['errors']));
            }
            
            // Agregar usuario actual
            $input['created_by'] = $currentUser['id'];
            
            $result = $post->create($input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Publicación creada exitosamente'
                ]);
            } else {
                throw new Exception('Error al crear publicación');
            }
            break;
            
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de publicación requerido');
            }
            
            // Validar si hay cambios en contenido
            if (isset($input['content']) || isset($input['platform'])) {
                $existing = $post->findById($id);
                $validationData = array_merge($existing, $input);
                $validation = $postService->validate($validationData);
                if (!$validation['valid']) {
                    throw new Exception('Errores de validación: ' . implode(', ', $validation['errors']));
                }
            }
            
            $result = $post->update($id, $input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Publicación actualizada exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar publicación');
            }
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de publicación requerido');
            }
            
            $result = $post->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Publicación eliminada exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar publicación');
            }
            break;
            
        case 'duplicate':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            $newDate = $input['new_date'] ?? null;
            
            if (!$id || !$newDate) {
                throw new Exception('ID y nueva fecha requeridos');
            }
            
            $result = $post->duplicate($id, $newDate);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Publicación duplicada exitosamente'
                ]);
            } else {
                throw new Exception('Error al duplicar publicación');
            }
            break;
            
        case 'publish_now':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de publicación requerido');
            }
            
            $result = $postService->publishNow($id);
            
            echo json_encode([
                'success' => true,
                'data' => $result,
                'message' => 'Publicación realizada exitosamente'
            ]);
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

