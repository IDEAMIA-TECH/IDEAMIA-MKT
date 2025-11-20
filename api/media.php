<?php
/**
 * Endpoint de Media
 * Maneja upload y gestión de archivos
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Media.php';
require_once __DIR__ . '/../src/services/MediaService.php';

$db = new Database();
$media = new Media($db);
$mediaService = new MediaService($db);

// Obtener acción
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $filters = [
                'page' => $_GET['page'] ?? 1,
                'per_page' => $_GET['per_page'] ?? 24,
                'client_id' => $_GET['client_id'] ?? '',
                'file_type' => $_GET['file_type'] ?? '',
                'folder' => $_GET['folder'] ?? '',
                'search' => $_GET['search'] ?? '',
                'tags' => $_GET['tags'] ?? ''
            ];
            
            $result = $media->list($filters);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'upload':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            if (!isset($_FILES['file'])) {
                throw new Exception('No se recibió ningún archivo');
            }
            
            $clientId = $_POST['client_id'] ?? null;
            $folder = $_POST['folder'] ?? null;
            $tags = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
            $tags = array_map('trim', $tags);
            $tags = array_filter($tags);
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            $result = $mediaService->uploadFile(
                $_FILES['file'],
                $clientId,
                $currentUser['id'],
                $folder,
                $tags
            );
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Archivo subido exitosamente'
                ]);
            } else {
                throw new Exception('Error al subir archivo');
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de archivo requerido');
            }
            
            $mediaData = $media->findById($id);
            
            if (!$mediaData) {
                throw new Exception('Archivo no encontrado');
            }
            
            // Decodificar tags
            if ($mediaData['tags']) {
                $mediaData['tags'] = json_decode($mediaData['tags'], true);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $mediaData
            ]);
            break;
            
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de archivo requerido');
            }
            
            $result = $media->update($id, $input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Archivo actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar archivo');
            }
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de archivo requerido');
            }
            
            $result = $media->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Archivo eliminado exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar archivo');
            }
            break;
            
        case 'folders':
            $clientId = $_GET['client_id'] ?? null;
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            $folders = $media->getFolders($clientId);
            echo json_encode([
                'success' => true,
                'data' => $folders
            ]);
            break;
            
        case 'tags':
            $clientId = $_GET['client_id'] ?? null;
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            $tags = $media->getTags($clientId);
            echo json_encode([
                'success' => true,
                'data' => $tags
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

