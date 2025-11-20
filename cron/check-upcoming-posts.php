<?php
/**
 * Cron Job para Notificar Posts Próximos
 * Ejecutar cada 15 minutos: */15 * * * * php /ruta/al/proyecto/cron/check-upcoming-posts.php
 */

require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/models/Post.php';
require_once __DIR__ . '/../src/services/NotificationService.php';

date_default_timezone_set('America/Mexico_City');

$logFile = __DIR__ . '/../logs/notifications-cron.log';
$logMessage = date('Y-m-d H:i:s') . " - Verificando posts próximos\n";

try {
    $db = new Database();
    $post = new Post($db);
    $notificationService = new NotificationService($db);
    
    // Buscar posts programados en los próximos 15 minutos
    $sql = "SELECT * FROM posts 
            WHERE status = 'scheduled' 
            AND scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            AND deleted_at IS NULL";
    
    $upcomingPosts = $db->query($sql);
    
    $notifiedCount = 0;
    
    foreach ($upcomingPosts as $postData) {
        // Verificar si ya se notificó (evitar duplicados)
        // Por simplicidad, notificamos siempre (en producción usaría un flag)
        $notificationService->notifyUpcomingPost(
            $postData['created_by'],
            $postData['id'],
            $postData['scheduled_at']
        );
        
        $notifiedCount++;
        $logMessage .= "  ✓ Notificación enviada para Post ID {$postData['id']}\n";
    }
    
    $logMessage .= "Resumen: {$notifiedCount} notificaciones enviadas\n";
    
} catch (Exception $e) {
    $logMessage .= "ERROR: " . $e->getMessage() . "\n";
}

$logMessage .= "---\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

if (php_sapi_name() === 'cli') {
    echo $logMessage;
}

