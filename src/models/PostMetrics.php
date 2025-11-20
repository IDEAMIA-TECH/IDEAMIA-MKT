<?php
/**
 * Modelo PostMetrics
 * Gestiona métricas de publicaciones
 */

require_once __DIR__ . '/../helpers/Database.php';

class PostMetrics {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Guarda o actualiza métricas de un post para una fecha
     */
    public function upsert($postId, $date, $metrics) {
        // Verificar si ya existe
        $sql = "SELECT id FROM post_metrics WHERE post_id = :post_id AND date = :date";
        $existing = $this->db->queryOne($sql, [
            'post_id' => $postId,
            'date' => $date
        ]);
        
        if ($existing) {
            // Actualizar
            $sql = "UPDATE post_metrics SET
                likes = :likes,
                comments = :comments,
                shares = :shares,
                reach = :reach,
                saves = :saves
                WHERE id = :id";
            
            $params = array_merge($metrics, ['id' => $existing['id']]);
        } else {
            // Insertar
            $sql = "INSERT INTO post_metrics (
                post_id, date, likes, comments, shares, reach, saves, created_at
            ) VALUES (
                :post_id, :date, :likes, :comments, :shares, :reach, :saves, NOW()
            )";
            
            $params = array_merge([
                'post_id' => $postId,
                'date' => $date
            ], $metrics);
        }
        
        $result = $this->db->execute($sql, $params);
        return $result['success'];
    }
    
    /**
     * Obtiene métricas de un post en un rango de fechas
     */
    public function getByPost($postId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM post_metrics WHERE post_id = :post_id";
        $params = ['post_id' => $postId];
        
        if ($dateFrom) {
            $sql .= " AND date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " ORDER BY date ASC";
        
        return $this->db->query($sql, $params);
    }
}

