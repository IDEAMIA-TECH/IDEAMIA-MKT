<?php
/**
 * Modelo Notification
 * Gestiona notificaciones del sistema
 */

require_once __DIR__ . '/../helpers/Database.php';

class Notification {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Crea una nueva notificación
     */
    public function create($data) {
        $sql = "INSERT INTO notifications (
            user_id, type, title, message, data, created_at
        ) VALUES (
            :user_id, :type, :title, :message, :data, NOW()
        )";
        
        $params = [
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => isset($data['data']) ? json_encode($data['data']) : null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Busca una notificación por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM notifications WHERE id = :id";
        $notification = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($notification && $notification['data']) {
            $notification['data'] = json_decode($notification['data'], true);
        }
        
        return $notification;
    }
    
    /**
     * Lista notificaciones de un usuario
     */
    public function list($userId, $filters = []) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if (isset($filters['unread_only']) && $filters['unread_only']) {
            $sql .= " AND read_at IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Límite
        $limit = $filters['limit'] ?? 50;
        $sql .= " LIMIT :limit";
        $params['limit'] = $limit;
        
        $notifications = $this->db->query($sql, $params);
        
        // Decodificar JSON fields
        foreach ($notifications as &$notification) {
            if ($notification['data']) {
                $notification['data'] = json_decode($notification['data'], true);
            }
        }
        
        return $notifications;
    }
    
    /**
     * Obtiene el conteo de notificaciones no leídas
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND read_at IS NULL";
        $result = $this->db->queryOne($sql, ['user_id' => $userId]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Marca una notificación como leída
     */
    public function markAsRead($id, $userId) {
        $sql = "UPDATE notifications SET read_at = NOW() WHERE id = :id AND user_id = :user_id";
        $result = $this->db->execute($sql, ['id' => $id, 'user_id' => $userId]);
        return $result['success'];
    }
    
    /**
     * Marca todas las notificaciones de un usuario como leídas
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications SET read_at = NOW() WHERE user_id = :user_id AND read_at IS NULL";
        $result = $this->db->execute($sql, ['user_id' => $userId]);
        return $result['success'];
    }
    
    /**
     * Elimina una notificación
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
        $result = $this->db->execute($sql, ['id' => $id, 'user_id' => $userId]);
        return $result['success'];
    }
}

