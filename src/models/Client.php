<?php
/**
 * Modelo Client
 * Gestiona clientes del sistema
 */

require_once __DIR__ . '/../helpers/Database.php';

class Client {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Busca un cliente por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM clients WHERE id = :id AND deleted_at IS NULL";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Crea un nuevo cliente
     */
    public function create($data) {
        $sql = "INSERT INTO clients (
            business_name, legal_name, contact_name, contact_email, 
            contact_phone, contact_whatsapp, sector, monthly_budget, 
            notes, status, created_at, updated_at
        ) VALUES (
            :business_name, :legal_name, :contact_name, :contact_email,
            :contact_phone, :contact_whatsapp, :sector, :monthly_budget,
            :notes, :status, NOW(), NOW()
        )";
        
        $params = [
            'business_name' => $data['business_name'],
            'legal_name' => $data['legal_name'] ?? null,
            'contact_name' => $data['contact_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
            'sector' => $data['sector'] ?? null,
            'monthly_budget' => $data['monthly_budget'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'active'
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza un cliente
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'business_name', 'legal_name', 'contact_name', 'contact_email',
            'contact_phone', 'contact_whatsapp', 'sector', 'monthly_budget',
            'notes', 'status'
        ];
        
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
        $sql = "UPDATE clients SET " . implode(', ', $updates) . " WHERE id = :id AND deleted_at IS NULL";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Elimina un cliente (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE clients SET deleted_at = NOW() WHERE id = :id";
        $result = $this->db->execute($sql, ['id' => $id]);
        return $result['success'];
    }
    
    /**
     * Lista clientes con paginación y filtros
     */
    public function list($filters = []) {
        $sql = "SELECT * FROM clients WHERE deleted_at IS NULL";
        $params = [];
        
        // Filtro por estado
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Filtro por sector
        if (isset($filters['sector']) && $filters['sector'] !== '') {
            $sql .= " AND sector = :sector";
            $params['sector'] = $filters['sector'];
        }
        
        // Búsqueda por nombre o email
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (business_name LIKE :search OR legal_name LIKE :search OR contact_name LIKE :search OR contact_email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = strtoupper($filters['order_dir'] ?? 'DESC');
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Contar total (para paginación)
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        // Paginación
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 20;
        $offset = ($page - 1) * $perPage;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $clients = $this->db->query($sql, $params);
        
        return [
            'data' => $clients,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtiene resumen de un cliente
     */
    public function getSummary($id) {
        $client = $this->findById($id);
        
        if (!$client) {
            return null;
        }
        
        // Contar redes sociales conectadas
        $sql = "SELECT COUNT(*) as total FROM social_accounts WHERE client_id = :client_id AND status = 'connected'";
        $socialResult = $this->db->queryOne($sql, ['client_id' => $id]);
        $socialAccounts = $socialResult['total'] ?? 0;
        
        // Contar campañas activas
        $sql = "SELECT COUNT(*) as total FROM campaigns WHERE client_id = :client_id AND status = 'active'";
        $campaignResult = $this->db->queryOne($sql, ['client_id' => $id]);
        $activeCampaigns = $campaignResult['total'] ?? 0;
        
        // Contar publicaciones próximas (próximos 7 días)
        $sql = "SELECT COUNT(*) as total FROM posts 
                WHERE client_id = :client_id 
                AND status IN ('scheduled', 'approved') 
                AND scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
        $postsResult = $this->db->queryOne($sql, ['client_id' => $id]);
        $upcomingPosts = $postsResult['total'] ?? 0;
        
        // Calcular presupuesto consumido (si hay campañas)
        $sql = "SELECT COALESCE(SUM(spend), 0) as total_spend 
                FROM campaign_metrics cm
                INNER JOIN campaigns c ON cm.campaign_id = c.id
                WHERE c.client_id = :client_id 
                AND cm.date >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $budgetResult = $this->db->queryOne($sql, ['client_id' => $id]);
        $spent = $budgetResult['total_spend'] ?? 0;
        
        return [
            'client' => $client,
            'social_accounts' => $socialAccounts,
            'active_campaigns' => $activeCampaigns,
            'upcoming_posts' => $upcomingPosts,
            'budget_spent' => $spent,
            'budget_available' => $client['monthly_budget'] ? ($client['monthly_budget'] - $spent) : null
        ];
    }
    
    /**
     * Obtiene sectores únicos (para filtros)
     */
    public function getSectors() {
        $sql = "SELECT DISTINCT sector FROM clients WHERE sector IS NOT NULL AND deleted_at IS NULL ORDER BY sector";
        $results = $this->db->query($sql);
        return array_column($results, 'sector');
    }
}

