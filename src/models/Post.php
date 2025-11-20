<?php
/**
 * Modelo Post
 * Gestiona publicaciones programadas
 */

require_once __DIR__ . '/../helpers/Database.php';

class Post {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Busca una publicación por ID
     */
    public function findById($id) {
        $sql = "SELECT p.*, c.business_name as client_name, sa.account_name as social_account_name
                FROM posts p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN social_accounts sa ON p.social_account_id = sa.id
                WHERE p.id = :id AND p.deleted_at IS NULL";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Crea una nueva publicación
     */
    public function create($data) {
        $sql = "INSERT INTO posts (
            client_id, social_account_id, created_by, platform,
            scheduled_at, content, media_urls, link_url,
            status, tags, campaign_id, created_at, updated_at
        ) VALUES (
            :client_id, :social_account_id, :created_by, :platform,
            :scheduled_at, :content, :media_urls, :link_url,
            :status, :tags, :campaign_id, NOW(), NOW()
        )";
        
        $params = [
            'client_id' => $data['client_id'],
            'social_account_id' => $data['social_account_id'] ?? null,
            'created_by' => $data['created_by'],
            'platform' => $data['platform'],
            'scheduled_at' => $data['scheduled_at'],
            'content' => $data['content'],
            'media_urls' => isset($data['media_urls']) ? json_encode($data['media_urls']) : null,
            'link_url' => $data['link_url'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'tags' => isset($data['tags']) ? json_encode($data['tags']) : null,
            'campaign_id' => $data['campaign_id'] ?? null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza una publicación
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'social_account_id', 'platform', 'scheduled_at',
            'content', 'media_urls', 'link_url', 'status',
            'tags', 'campaign_id', 'error_message'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'media_urls' || $field === 'tags') {
                    $updates[] = "{$field} = :{$field}";
                    $params[$field] = json_encode($data[$field]);
                } else {
                    $updates[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "updated_at = NOW()";
        $sql = "UPDATE posts SET " . implode(', ', $updates) . " WHERE id = :id AND deleted_at IS NULL";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Marca una publicación como publicada
     */
    public function markAsPublished($id, $postId = null) {
        $updates = [
            'status' => 'published',
            'published_at' => 'NOW()'
        ];
        
        if ($postId) {
            $updates['meta_post_id'] = $postId;
        }
        
        $sql = "UPDATE posts SET status = 'published', published_at = NOW()";
        if ($postId) {
            $sql .= ", meta_post_id = :meta_post_id";
            $params = ['id' => $id, 'meta_post_id' => $postId];
        } else {
            $params = ['id' => $id];
        }
        
        $sql .= " WHERE id = :id";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'];
    }
    
    /**
     * Marca una publicación como fallida
     */
    public function markAsFailed($id, $errorMessage) {
        $sql = "UPDATE posts SET status = 'failed', error_message = :error_message WHERE id = :id";
        $result = $this->db->execute($sql, [
            'id' => $id,
            'error_message' => $errorMessage
        ]);
        return $result['success'];
    }
    
    /**
     * Elimina una publicación (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE posts SET deleted_at = NOW() WHERE id = :id";
        $result = $this->db->execute($sql, ['id' => $id]);
        return $result['success'];
    }
    
    /**
     * Lista publicaciones con filtros
     */
    public function list($filters = []) {
        $sql = "SELECT p.*, c.business_name as client_name, sa.account_name as social_account_name,
                u.name as created_by_name
                FROM posts p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN social_accounts sa ON p.social_account_id = sa.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.deleted_at IS NULL";
        $params = [];
        
        // Filtro por cliente
        if (isset($filters['client_id']) && $filters['client_id']) {
            $sql .= " AND p.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        // Filtro por estado
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Filtro por plataforma
        if (isset($filters['platform']) && $filters['platform'] !== '') {
            $sql .= " AND p.platform = :platform";
            $params['platform'] = $filters['platform'];
        }
        
        // Filtro por rango de fechas
        if (isset($filters['date_from']) && $filters['date_from']) {
            $sql .= " AND DATE(p.scheduled_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $sql .= " AND DATE(p.scheduled_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'scheduled_at';
        $orderDir = strtoupper($filters['order_dir'] ?? 'ASC');
        $sql .= " ORDER BY p.{$orderBy} {$orderDir}";
        
        // Paginación
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 50;
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = str_replace('SELECT p.*, c.business_name', 'SELECT COUNT(*) as total', $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $posts = $this->db->query($sql, $params);
        
        // Decodificar JSON fields
        foreach ($posts as &$post) {
            if ($post['media_urls']) {
                $post['media_urls'] = json_decode($post['media_urls'], true);
            }
            if ($post['tags']) {
                $post['tags'] = json_decode($post['tags'], true);
            }
        }
        
        return [
            'data' => $posts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtiene publicaciones para el calendario
     */
    public function getCalendarEvents($filters = []) {
        $sql = "SELECT p.id, p.client_id, p.platform, p.scheduled_at, p.content, 
                p.status, p.media_urls, c.business_name as client_name,
                sa.account_name as social_account_name
                FROM posts p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN social_accounts sa ON p.social_account_id = sa.id
                WHERE p.deleted_at IS NULL";
        $params = [];
        
        if (isset($filters['client_id']) && $filters['client_id']) {
            $sql .= " AND p.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $sql .= " AND DATE(p.scheduled_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $sql .= " AND DATE(p.scheduled_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY p.scheduled_at ASC";
        
        $posts = $this->db->query($sql, $params);
        
        // Formatear para FullCalendar
        $events = [];
        foreach ($posts as $post) {
            $statusColors = [
                'draft' => '#6c757d',
                'pending_approval' => '#ffc107',
                'approved' => '#17a2b8',
                'scheduled' => '#007bff',
                'published' => '#28a745',
                'rejected' => '#dc3545',
                'failed' => '#dc3545'
            ];
            
            $events[] = [
                'id' => $post['id'],
                'title' => substr($post['content'], 0, 50) . (strlen($post['content']) > 50 ? '...' : ''),
                'start' => $post['scheduled_at'],
                'backgroundColor' => $statusColors[$post['status']] ?? '#6c757d',
                'borderColor' => $statusColors[$post['status']] ?? '#6c757d',
                'extendedProps' => [
                    'client_name' => $post['client_name'],
                    'platform' => $post['platform'],
                    'status' => $post['status'],
                    'content' => $post['content']
                ]
            ];
        }
        
        return $events;
    }
    
    /**
     * Obtiene publicaciones pendientes de publicar
     */
    public function getPendingPosts($limit = 10) {
        $sql = "SELECT p.*, sa.access_token, sa.account_id, sa.platform as account_platform
                FROM posts p
                INNER JOIN social_accounts sa ON p.social_account_id = sa.id
                WHERE p.status = 'scheduled'
                AND p.scheduled_at <= NOW()
                AND p.deleted_at IS NULL
                AND sa.status = 'connected'
                ORDER BY p.scheduled_at ASC
                LIMIT :limit";
        
        $posts = $this->db->query($sql, ['limit' => $limit]);
        
        // Decodificar JSON y desencriptar tokens
        foreach ($posts as &$post) {
            if ($post['media_urls']) {
                $post['media_urls'] = json_decode($post['media_urls'], true);
            }
            // El access_token se desencriptará en el servicio
        }
        
        return $posts;
    }
    
    /**
     * Duplica una publicación
     */
    public function duplicate($id, $newDate) {
        $original = $this->findById($id);
        
        if (!$original) {
            return false;
        }
        
        $data = [
            'client_id' => $original['client_id'],
            'social_account_id' => $original['social_account_id'],
            'created_by' => $original['created_by'],
            'platform' => $original['platform'],
            'scheduled_at' => $newDate,
            'content' => $original['content'],
            'media_urls' => json_decode($original['media_urls'], true),
            'link_url' => $original['link_url'],
            'status' => 'draft',
            'tags' => json_decode($original['tags'], true),
            'campaign_id' => $original['campaign_id']
        ];
        
        return $this->create($data);
    }
}

