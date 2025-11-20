<?php
/**
 * Modelo Media
 * Gestiona archivos multimedia
 */

require_once __DIR__ . '/../helpers/Database.php';

class Media {
    private $db;
    private $config;
    
    public function __construct($db) {
        $this->db = $db;
        $this->config = require __DIR__ . '/../../config/config.php';
    }
    
    /**
     * Busca un archivo por ID
     */
    public function findById($id) {
        $sql = "SELECT m.*, u.name as uploaded_by_name, c.business_name as client_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                LEFT JOIN clients c ON m.client_id = c.id
                WHERE m.id = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Crea un nuevo registro de media
     */
    public function create($data) {
        $sql = "INSERT INTO media (
            client_id, uploaded_by, filename, original_filename,
            file_path, file_type, file_size, mime_type,
            folder, tags, created_at, updated_at
        ) VALUES (
            :client_id, :uploaded_by, :filename, :original_filename,
            :file_path, :file_type, :file_size, :mime_type,
            :folder, :tags, NOW(), NOW()
        )";
        
        $params = [
            'client_id' => $data['client_id'],
            'uploaded_by' => $data['uploaded_by'],
            'filename' => $data['filename'],
            'original_filename' => $data['original_filename'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'mime_type' => $data['mime_type'],
            'folder' => $data['folder'] ?? null,
            'tags' => isset($data['tags']) ? json_encode($data['tags']) : null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza un archivo
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = ['filename', 'folder', 'tags'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'tags') {
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
        $sql = "UPDATE media SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Elimina un archivo
     */
    public function delete($id) {
        $media = $this->findById($id);
        
        if (!$media) {
            return false;
        }
        
        // Eliminar archivo físico
        $filePath = $this->config['BASE_PATH'] . $media['file_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        
        // Eliminar registro
        $sql = "DELETE FROM media WHERE id = :id";
        $result = $this->db->execute($sql, ['id' => $id]);
        return $result['success'];
    }
    
    /**
     * Lista archivos con filtros
     */
    public function list($filters = []) {
        $sql = "SELECT m.*, u.name as uploaded_by_name, c.business_name as client_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                LEFT JOIN clients c ON m.client_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['client_id']) && $filters['client_id']) {
            $sql .= " AND m.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (isset($filters['file_type']) && $filters['file_type'] !== '') {
            $sql .= " AND m.file_type = :file_type";
            $params['file_type'] = $filters['file_type'];
        }
        
        if (isset($filters['folder']) && $filters['folder'] !== '') {
            $sql .= " AND m.folder = :folder";
            $params['folder'] = $filters['folder'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (m.original_filename LIKE :search OR m.filename LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Búsqueda por tags
        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : explode(',', $filters['tags']);
            $tagConditions = [];
            foreach ($tags as $index => $tag) {
                $tagConditions[] = "m.tags LIKE :tag_{$index}";
                $params["tag_{$index}"] = '%"' . trim($tag) . '"%';
            }
            if (!empty($tagConditions)) {
                $sql .= " AND (" . implode(' OR ', $tagConditions) . ")";
            }
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        // Paginación
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 24;
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = str_replace('SELECT m.*, u.name', 'SELECT COUNT(*) as total', $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $media = $this->db->query($sql, $params);
        
        // Decodificar JSON fields
        foreach ($media as &$item) {
            if ($item['tags']) {
                $item['tags'] = json_decode($item['tags'], true);
            }
        }
        
        return [
            'data' => $media,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtiene carpetas únicas de un cliente
     */
    public function getFolders($clientId) {
        $sql = "SELECT DISTINCT folder FROM media WHERE client_id = :client_id AND folder IS NOT NULL ORDER BY folder";
        $results = $this->db->query($sql, ['client_id' => $clientId]);
        return array_column($results, 'folder');
    }
    
    /**
     * Obtiene tags únicos de un cliente
     */
    public function getTags($clientId) {
        $sql = "SELECT tags FROM media WHERE client_id = :client_id AND tags IS NOT NULL";
        $results = $this->db->query($sql, ['client_id' => $clientId]);
        
        $allTags = [];
        foreach ($results as $row) {
            $tags = json_decode($row['tags'], true);
            if (is_array($tags)) {
                $allTags = array_merge($allTags, $tags);
            }
        }
        
        return array_unique($allTags);
    }
}

