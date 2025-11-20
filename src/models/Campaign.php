<?php
/**
 * Modelo Campaign
 * Gestiona campañas de anuncios
 */

require_once __DIR__ . '/../helpers/Database.php';

class Campaign {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Busca una campaña por ID
     */
    public function findById($id) {
        $sql = "SELECT c.*, cl.business_name as client_name, sa.account_name as social_account_name
                FROM campaigns c
                LEFT JOIN clients cl ON c.client_id = cl.id
                LEFT JOIN social_accounts sa ON c.social_account_id = sa.id
                WHERE c.id = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Crea una nueva campaña
     */
    public function create($data) {
        $sql = "INSERT INTO campaigns (
            client_id, social_account_id, ad_account_id, name,
            objective, daily_budget, total_budget, start_date,
            end_date, status, meta_campaign_id, created_at, updated_at
        ) VALUES (
            :client_id, :social_account_id, :ad_account_id, :name,
            :objective, :daily_budget, :total_budget, :start_date,
            :end_date, :status, :meta_campaign_id, NOW(), NOW()
        )";
        
        $params = [
            'client_id' => $data['client_id'],
            'social_account_id' => $data['social_account_id'] ?? null,
            'ad_account_id' => $data['ad_account_id'] ?? null,
            'name' => $data['name'],
            'objective' => $data['objective'] ?? null,
            'daily_budget' => $data['daily_budget'] ?? null,
            'total_budget' => $data['total_budget'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'active',
            'meta_campaign_id' => $data['meta_campaign_id'] ?? null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza una campaña
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'name', 'objective', 'daily_budget', 'total_budget',
            'start_date', 'end_date', 'status', 'meta_campaign_id'
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
        $sql = "UPDATE campaigns SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Elimina una campaña
     */
    public function delete($id) {
        $sql = "DELETE FROM campaigns WHERE id = :id";
        $result = $this->db->execute($sql, ['id' => $id]);
        return $result['success'];
    }
    
    /**
     * Lista campañas con filtros
     */
    public function list($filters = []) {
        $sql = "SELECT c.*, cl.business_name as client_name, sa.account_name as social_account_name
                FROM campaigns c
                LEFT JOIN clients cl ON c.client_id = cl.id
                LEFT JOIN social_accounts sa ON c.social_account_id = sa.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['client_id']) && $filters['client_id']) {
            $sql .= " AND c.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = strtoupper($filters['order_dir'] ?? 'DESC');
        $sql .= " ORDER BY c.{$orderBy} {$orderDir}";
        
        // Paginación
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 20;
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = str_replace('SELECT c.*, cl.business_name', 'SELECT COUNT(*) as total', $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $campaigns = $this->db->query($sql, $params);
        
        return [
            'data' => $campaigns,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtiene resumen de una campaña
     */
    public function getSummary($id, $dateFrom = null, $dateTo = null) {
        $campaign = $this->findById($id);
        
        if (!$campaign) {
            return null;
        }
        
        // Obtener métricas agregadas
        $sql = "SELECT 
                SUM(impressions) as total_impressions,
                SUM(reach) as total_reach,
                SUM(clicks) as total_clicks,
                AVG(ctr) as avg_ctr,
                AVG(cpc) as avg_cpc,
                AVG(cpm) as avg_cpm,
                SUM(spend) as total_spend,
                SUM(conversions) as total_conversions
                FROM campaign_metrics
                WHERE campaign_id = :campaign_id";
        $params = ['campaign_id' => $id];
        
        if ($dateFrom) {
            $sql .= " AND date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $metrics = $this->db->queryOne($sql, $params);
        
        // Calcular métricas adicionales
        if ($metrics['total_clicks'] > 0 && $metrics['total_impressions'] > 0) {
            $metrics['calculated_ctr'] = ($metrics['total_clicks'] / $metrics['total_impressions']) * 100;
        } else {
            $metrics['calculated_ctr'] = 0;
        }
        
        if ($metrics['total_clicks'] > 0 && $metrics['total_spend'] > 0) {
            $metrics['calculated_cpc'] = $metrics['total_spend'] / $metrics['total_clicks'];
        } else {
            $metrics['calculated_cpc'] = 0;
        }
        
        return [
            'campaign' => $campaign,
            'metrics' => $metrics
        ];
    }
}

