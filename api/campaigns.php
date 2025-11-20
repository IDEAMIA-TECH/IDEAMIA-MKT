<?php
/**
 * Endpoint de Campañas
 * Maneja CRUD de campañas y sincronización
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Campaign.php';
require_once __DIR__ . '/../src/models/CampaignMetrics.php';
require_once __DIR__ . '/../src/services/CampaignService.php';

$db = new Database();
$campaign = new Campaign($db);
$campaignService = new CampaignService($db);

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
                'order_by' => $_GET['order_by'] ?? 'created_at',
                'order_dir' => $_GET['order_dir'] ?? 'DESC'
            ];
            
            $result = $campaign->list($filters);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de campaña requerido');
            }
            
            $campaignData = $campaign->findById($id);
            
            if (!$campaignData) {
                throw new Exception('Campaña no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $campaignData
            ]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $result = $campaign->create($input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Campaña creada exitosamente'
                ]);
            } else {
                throw new Exception('Error al crear campaña');
            }
            break;
            
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de campaña requerido');
            }
            
            $result = $campaign->update($id, $input);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Campaña actualizada exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar campaña');
            }
            break;
            
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de campaña requerido');
            }
            
            $result = $campaign->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Campaña eliminada exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar campaña');
            }
            break;
            
        case 'summary':
            $id = $_GET['id'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de campaña requerido');
            }
            
            $summary = $campaignService->getCampaignSummary($id, $dateFrom, $dateTo);
            
            if (!$summary) {
                throw new Exception('Campaña no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $summary
            ]);
            break;
            
        case 'metrics':
            $id = $_GET['id'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de campaña requerido');
            }
            
            $metricsModel = new CampaignMetrics($db);
            $metrics = $metricsModel->getAggregated($id, 'day', $dateFrom, $dateTo);
            
            echo json_encode([
                'success' => true,
                'data' => $metrics
            ]);
            break;
            
        case 'sync':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $input['id'] ?? null;
            $clientId = $input['client_id'] ?? null;
            $adAccountId = $input['ad_account_id'] ?? null;
            
            if ($id) {
                // Sincronizar métricas de una campaña específica
                $synced = $campaignService->syncCampaignMetrics($id);
                echo json_encode([
                    'success' => true,
                    'message' => "Se sincronizaron {$synced} días de métricas"
                ]);
            } elseif ($clientId && $adAccountId) {
                // Sincronizar campañas desde Meta
                $synced = $campaignService->syncCampaignsFromMeta($clientId, $adAccountId);
                echo json_encode([
                    'success' => true,
                    'message' => "Se sincronizaron " . count($synced) . " campañas",
                    'data' => $synced
                ]);
            } else {
                throw new Exception('Parámetros insuficientes para sincronizar');
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

