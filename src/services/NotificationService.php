<?php
/**
 * Servicio de Notificaciones
 * Crea y gestiona notificaciones del sistema
 */

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';

class NotificationService {
    private $db;
    private $notification;
    private $user;
    
    public function __construct($db) {
        $this->db = $db;
        $this->notification = new Notification($db);
        $this->user = new User($db);
    }
    
    /**
     * Crea una notificación para un usuario
     */
    public function create($userId, $type, $title, $message, $data = []) {
        return $this->notification->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Crea notificación para múltiples usuarios
     */
    public function createForUsers($userIds, $type, $title, $message, $data = []) {
        $created = [];
        foreach ($userIds as $userId) {
            $created[] = $this->create($userId, $type, $title, $message, $data);
        }
        return $created;
    }
    
    /**
     * Crea notificación para todos los usuarios de un rol
     */
    public function createForRole($role, $type, $title, $message, $data = []) {
        $users = $this->user->list(['role' => $role, 'per_page' => 1000]);
        $userIds = array_column($users['data'], 'id');
        return $this->createForUsers($userIds, $type, $title, $message, $data);
    }
    
    /**
     * Notifica sobre publicación próxima
     */
    public function notifyUpcomingPost($userId, $postId, $scheduledAt) {
        $scheduledTime = new DateTime($scheduledAt);
        $now = new DateTime();
        $minutesUntil = round(($scheduledTime->getTimestamp() - $now->getTimestamp()) / 60);
        
        return $this->create(
            $userId,
            'post_upcoming',
            'Publicación próxima',
            "Tienes una publicación programada en {$minutesUntil} minutos",
            ['post_id' => $postId, 'scheduled_at' => $scheduledAt]
        );
    }
    
    /**
     * Notifica sobre fallo en publicación
     */
    public function notifyPostFailed($userId, $postId, $errorMessage) {
        return $this->create(
            $userId,
            'post_failed',
            'Error en publicación',
            "La publicación falló: {$errorMessage}",
            ['post_id' => $postId, 'error' => $errorMessage]
        );
    }
    
    /**
     * Notifica sobre publicación exitosa
     */
    public function notifyPostPublished($userId, $postId) {
        return $this->create(
            $userId,
            'post_published',
            'Publicación exitosa',
            'Tu publicación se ha publicado correctamente',
            ['post_id' => $postId]
        );
    }
    
    /**
     * Notifica sobre token próximo a expirar
     */
    public function notifyTokenExpiring($userId, $accountId, $daysUntilExpiry) {
        return $this->create(
            $userId,
            'token_expiring',
            'Token próximo a expirar',
            "El token de una cuenta de red social expirará en {$daysUntilExpiry} días",
            ['account_id' => $accountId]
        );
    }
    
    /**
     * Notifica sobre campaña con bajo rendimiento
     */
    public function notifyLowPerformanceCampaign($userId, $campaignId, $campaignName) {
        return $this->create(
            $userId,
            'campaign_low_performance',
            'Campaña con bajo rendimiento',
            "La campaña '{$campaignName}' tiene un rendimiento bajo",
            ['campaign_id' => $campaignId]
        );
    }
    
    /**
     * Notifica sobre reporte generado
     */
    public function notifyReportReady($userId, $reportId, $clientName) {
        return $this->create(
            $userId,
            'report_ready',
            'Reporte listo',
            "El reporte para {$clientName} está listo para descargar",
            ['report_id' => $reportId]
        );
    }
}

