<?php
/**
 * Modelo CampaignMetrics
 * Gestiona métricas de campañas
 */

require_once __DIR__ . '/../helpers/Database.php';

class CampaignMetrics {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Guarda o actualiza métricas de una campaña para una fecha
     */
    public function upsert($campaignId, $date, $metrics) {
        // Verificar si ya existe
        $sql = "SELECT id FROM campaign_metrics WHERE campaign_id = :campaign_id AND date = :date";
        $existing = $this->db->queryOne($sql, [
            'campaign_id' => $campaignId,
            'date' => $date
        ]);
        
        if ($existing) {
            // Actualizar
            $sql = "UPDATE campaign_metrics SET
                impressions = :impressions,
                reach = :reach,
                clicks = :clicks,
                ctr = :ctr,
                cpc = :cpc,
                cpm = :cpm,
                spend = :spend,
                conversions = :conversions
                WHERE id = :id";
            
            $params = array_merge($metrics, ['id' => $existing['id']]);
        } else {
            // Insertar
            $sql = "INSERT INTO campaign_metrics (
                campaign_id, date, impressions, reach, clicks,
                ctr, cpc, cpm, spend, conversions, created_at
            ) VALUES (
                :campaign_id, :date, :impressions, :reach, :clicks,
                :ctr, :cpc, :cpm, :spend, :conversions, NOW()
            )";
            
            $params = array_merge([
                'campaign_id' => $campaignId,
                'date' => $date
            ], $metrics);
        }
        
        $result = $this->db->execute($sql, $params);
        return $result['success'];
    }
    
    /**
     * Obtiene métricas de una campaña en un rango de fechas
     */
    public function getByCampaign($campaignId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM campaign_metrics WHERE campaign_id = :campaign_id";
        $params = ['campaign_id' => $campaignId];
        
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
    
    /**
     * Obtiene métricas agregadas por periodo
     */
    public function getAggregated($campaignId, $period = 'day', $dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                DATE(date) as period_date,
                SUM(impressions) as impressions,
                SUM(reach) as reach,
                SUM(clicks) as clicks,
                AVG(ctr) as ctr,
                AVG(cpc) as cpc,
                AVG(cpm) as cpm,
                SUM(spend) as spend,
                SUM(conversions) as conversions
                FROM campaign_metrics
                WHERE campaign_id = :campaign_id";
        $params = ['campaign_id' => $campaignId];
        
        if ($dateFrom) {
            $sql .= " AND date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND date <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " GROUP BY DATE(date) ORDER BY period_date ASC";
        
        return $this->db->query($sql, $params);
    }
}

