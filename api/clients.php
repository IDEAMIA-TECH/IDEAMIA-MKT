<?php
/**
 * Endpoint de Clientes
 * Maneja CRUD de clientes
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Client.php';
require_once __DIR__ . '/../src/helpers/Validator.php';

$db = new Database();
$client = new Client($db);

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
                'status' => $_GET['status'] ?? '',
                'sector' => $_GET['sector'] ?? '',
                'search' => $_GET['search'] ?? '',
                'order_by' => $_GET['order_by'] ?? 'created_at',
                'order_dir' => $_GET['order_dir'] ?? 'DESC'
            ];
            
            $result = $client->list($filters);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cliente requerido');
            }
            
            $clientData = $client->findById($id);
            
            if (!$clientData) {
                throw new Exception('Cliente no encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $clientData
            ]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            // Validar datos
            $validation = Validator::validate($input, [
                'business_name' => ['required', 'min' => 3],
                'contact_name' => ['required', 'min' => 2],
                'contact_email' => ['required', 'email']
            ]);
            
            if (!$validation['valid']) {
                throw new Exception('Datos inválidos: ' . json_encode($validation['errors']));
            }
            
            $result = $client->create($input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Cliente creado exitosamente'
                ]);
            } else {
                throw new Exception('Error al crear cliente');
            }
            break;
            
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cliente requerido');
            }
            
            // Validar datos
            if (isset($input['contact_email'])) {
                if (!Validator::email($input['contact_email'])) {
                    throw new Exception('Email inválido');
                }
            }
            
            $result = $client->update($id, $input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Cliente actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar cliente');
            }
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cliente requerido');
            }
            
            $result = $client->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente eliminado exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar cliente');
            }
            break;
            
        case 'summary':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de cliente requerido');
            }
            
            $summary = $client->getSummary($id);
            
            if (!$summary) {
                throw new Exception('Cliente no encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $summary
            ]);
            break;
            
        case 'sectors':
            $sectors = $client->getSectors();
            echo json_encode([
                'success' => true,
                'data' => $sectors
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

