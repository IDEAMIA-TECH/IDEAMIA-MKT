<?php
/**
 * Cron Job para Verificar Tokens Próximos a Expirar
 * Ejecutar diariamente: 0 9 * * * php /ruta/al/proyecto/cron/check-expiring-tokens.php
 */

require_once __DIR__ . '/../src/helpers/Database.php';
require_once __DIR__ . '/../src/models/SocialAccount.php';
require_once __DIR__ . '/../src/services/NotificationService.php';

date_default_timezone_set('America/Mexico_City');

$logFile = __DIR__ . '/../logs/tokens-cron.log';
$logMessage = date('Y-m-d H:i:s') . " - Verificando tokens próximos a expirar\n";

try {
    $db = new Database();
    $socialAccount = new SocialAccount($db);
    $notificationService = new NotificationService($db);
    
    // Buscar tokens que expiran en los próximos 7 días
    $sql = "SELECT sa.*, c.id as client_id 
            FROM social_accounts sa
            INNER JOIN clients c ON sa.client_id = c.id
            WHERE sa.status = 'connected'
            AND sa.token_expires_at IS NOT NULL
            AND sa.token_expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
    
    $expiringAccounts = $db->query($sql);
    
    $notifiedCount = 0;
    
    foreach ($expiringAccounts as $account) {
        $expiresAt = new DateTime($account['token_expires_at']);
        $now = new DateTime();
        $daysUntilExpiry = $expiresAt->diff($now)->days;
        
        // Notificar a administradores (por simplicidad, notificamos al primer admin)
        // En producción, notificar a todos los admins del cliente
        $sql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $admin = $db->queryOne($sql);
        
        if ($admin) {
            $notificationService->notifyTokenExpiring(
                $admin['id'],
                $account['id'],
                $daysUntilExpiry
            );
            
            $notifiedCount++;
            $logMessage .= "  ✓ Notificación enviada para cuenta {$account['account_name']} (expira en {$daysUntilExpiry} días)\n";
        }
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

