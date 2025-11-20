<?php
/**
 * Endpoint de Reportes
 * Maneja generación y descarga de reportes
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Report.php';
require_once __DIR__ . '/../src/services/ReportService.php';

$db = new Database();
$report = new Report($db);
$reportService = new ReportService($db);

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
                'type' => $_GET['type'] ?? ''
            ];
            
            $result = $report->list($filters);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'generate':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $clientId = $input['client_id'] ?? null;
            $type = $input['type'] ?? 'executive';
            $periodStart = $input['period_start'] ?? null;
            $periodEnd = $input['period_end'] ?? null;
            
            if (!$clientId || !$periodStart || !$periodEnd) {
                throw new Exception('Cliente y periodo requeridos');
            }
            
            // Generar métricas
            $metrics = $reportService->generateMetrics($clientId, $periodStart, $periodEnd);
            
            // Crear registro de reporte
            $config = require __DIR__ . '/../config/config.php';
            $reportsDir = __DIR__ . '/../reports/';
            if (!is_dir($reportsDir)) {
                mkdir($reportsDir, 0755, true);
            }
            
            $filename = 'report_' . $clientId . '_' . date('Y-m-d_His') . '.json';
            $filePath = $reportsDir . $filename;
            
            // Guardar datos del reporte
            file_put_contents($filePath, json_encode($metrics, JSON_PRETTY_PRINT));
            
            // Crear registro en BD
            $reportData = [
                'client_id' => $clientId,
                'generated_by' => $currentUser['id'],
                'type' => $type,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'file_path' => $filePath
            ];
            
            $newReport = $report->create($reportData);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'report' => $newReport,
                    'metrics' => $metrics
                ],
                'message' => 'Reporte generado exitosamente'
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de reporte requerido');
            }
            
            $reportData = $report->findById($id);
            
            if (!$reportData) {
                throw new Exception('Reporte no encontrado');
            }
            
            // Cargar métricas del archivo
            $metrics = null;
            if ($reportData['file_path'] && file_exists($reportData['file_path'])) {
                $metrics = json_decode(file_get_contents($reportData['file_path']), true);
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'report' => $reportData,
                    'metrics' => $metrics
                ]
            ]);
            break;
            
        case 'metrics':
            $clientId = $_GET['client_id'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            if (!$clientId) {
                throw new Exception('ID de cliente requerido');
            }
            
            // Si no hay fechas, usar último mes
            if (!$dateFrom) {
                $dateFrom = date('Y-m-d', strtotime('-30 days'));
            }
            if (!$dateTo) {
                $dateTo = date('Y-m-d');
            }
            
            $metrics = $reportService->generateMetrics($clientId, $dateFrom, $dateTo);
            
            echo json_encode([
                'success' => true,
                'data' => $metrics
            ]);
            break;
            
        case 'download':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de reporte requerido');
            }
            
            $reportData = $report->findById($id);
            
            if (!$reportData || !$reportData['file_path'] || !file_exists($reportData['file_path'])) {
                throw new Exception('Archivo de reporte no encontrado');
            }
            
            // Retornar archivo JSON (en producción sería PDF)
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="report_' . $id . '.json"');
            readfile($reportData['file_path']);
            exit;
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

