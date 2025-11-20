<?php
/**
 * Cron Job para Publicar Posts Programados
 * Ejecutar cada minuto: * * * * * php /ruta/al/proyecto/cron/publish-scheduled-posts.php
 */

require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/services/PostService.php';
require_once __DIR__ . '/../src/services/NotificationService.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Log
$logFile = __DIR__ . '/../logs/publish-cron.log';
$logMessage = date('Y-m-d H:i:s') . " - Iniciando publicación de posts programados\n";

try {
    $db = new Database();
    $postService = new PostService($db);
    $notificationService = new NotificationService($db);
    
    // Procesar hasta 20 posts pendientes
    $results = $postService->processScheduledPosts(20);
    
    $successCount = 0;
    $failedCount = 0;
    
    foreach ($results as $result) {
        if ($result['status'] === 'success') {
            $successCount++;
            $logMessage .= "  ✓ Post ID {$result['post_id']} publicado exitosamente\n";
            
            // Notificar publicación exitosa
            $post = $postService->post->findById($result['post_id']);
            if ($post) {
                $notificationService->notifyPostPublished($post['created_by'], $result['post_id']);
            }
        } else {
            $failedCount++;
            $logMessage .= "  ✗ Post ID {$result['post_id']} falló: {$result['error']}\n";
            
            // Notificar fallo
            $post = $postService->post->findById($result['post_id']);
            if ($post) {
                $notificationService->notifyPostFailed($post['created_by'], $result['post_id'], $result['error']);
            }
        }
    }
    
    $logMessage .= "Resumen: {$successCount} exitosos, {$failedCount} fallidos\n";
    
} catch (Exception $e) {
    $logMessage .= "ERROR: " . $e->getMessage() . "\n";
}

$logMessage .= "---\n";

// Escribir log
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Si se ejecuta desde CLI, mostrar resultado
if (php_sapi_name() === 'cli') {
    echo $logMessage;
}

