<?php
/**
 * Modelo Report
 * Gestiona reportes generados
 */

require_once __DIR__ . '/../helpers/Database.php';

class Report {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Busca un reporte por ID
     */
    public function findById($id) {
        $sql = "SELECT r.*, c.business_name as client_name, u.name as generated_by_name
                FROM reports r
                LEFT JOIN clients c ON r.client_id = c.id
                LEFT JOIN users u ON r.generated_by = u.id
                WHERE r.id = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Crea un nuevo reporte
     */
    public function create($data) {
        $sql = "INSERT INTO reports (
            client_id, generated_by, type, period_start, period_end,
            file_path, sent_at, created_at, updated_at
        ) VALUES (
            :client_id, :generated_by, :type, :period_start, :period_end,
            :file_path, :sent_at, NOW(), NOW()
        )";
        
        $params = [
            'client_id' => $data['client_id'],
            'generated_by' => $data['generated_by'],
            'type' => $data['type'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'file_path' => $data['file_path'] ?? null,
            'sent_at' => $data['sent_at'] ?? null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza un reporte
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = ['file_path', 'sent_at'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "updated_at = NOW()";
        $sql = "UPDATE reports SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Lista reportes
     */
    public function list($filters = []) {
        $sql = "SELECT r.*, c.business_name as client_name, u.name as generated_by_name
                FROM reports r
                LEFT JOIN clients c ON r.client_id = c.id
                LEFT JOIN users u ON r.generated_by = u.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['client_id']) && $filters['client_id']) {
            $sql .= " AND r.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (isset($filters['type']) && $filters['type'] !== '') {
            $sql .= " AND r.type = :type";
            $params['type'] = $filters['type'];
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        // Paginación
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 20;
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = str_replace('SELECT r.*, c.business_name', 'SELECT COUNT(*) as total', $sql);
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $reports = $this->db->query($sql, $params);
        
        return [
            'data' => $reports,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}

