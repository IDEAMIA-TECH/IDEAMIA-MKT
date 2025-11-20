<?php
/**
 * Servicio de Reportes
 * Genera reportes consolidados
 */

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/PostMetrics.php';
require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/CampaignMetrics.php';
require_once __DIR__ . '/../models/Client.php';

class ReportService {
    private $db;
    private $post;
    private $campaign;
    private $client;
    
    public function __construct($db) {
        $this->db = $db;
        $this->post = new Post($db);
        $this->campaign = new Campaign($db);
        $this->client = new Client($db);
    }
    
    /**
     * Genera métricas consolidadas para un cliente
     */
    public function generateMetrics($clientId, $dateFrom, $dateTo) {
        $client = $this->client->findById($clientId);
        
        if (!$client) {
            throw new Exception('Cliente no encontrado');
        }
        
        // Métricas de publicaciones orgánicas
        $organicMetrics = $this->getOrganicMetrics($clientId, $dateFrom, $dateTo);
        
        // Métricas de campañas
        $adsMetrics = $this->getAdsMetrics($clientId, $dateFrom, $dateTo);
        
        // Crecimiento de seguidores (simulado - requeriría API adicional)
        $followerGrowth = $this->getFollowerGrowth($clientId, $dateFrom, $dateTo);
        
        // Mejores publicaciones
        $topPosts = $this->getTopPosts($clientId, $dateFrom, $dateTo, 5);
        
        // Mejores campañas
        $topCampaigns = $this->getTopCampaigns($clientId, $dateFrom, $dateTo, 3);
        
        return [
            'client' => $client,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'organic' => $organicMetrics,
            'ads' => $adsMetrics,
            'follower_growth' => $followerGrowth,
            'top_posts' => $topPosts,
            'top_campaigns' => $topCampaigns,
            'summary' => $this->generateSummary($organicMetrics, $adsMetrics, $followerGrowth)
        ];
    }
    
    /**
     * Obtiene métricas orgánicas
     */
    private function getOrganicMetrics($clientId, $dateFrom, $dateTo) {
        $sql = "SELECT 
                COUNT(DISTINCT p.id) as total_posts,
                SUM(pm.likes) as total_likes,
                SUM(pm.comments) as total_comments,
                SUM(pm.shares) as total_shares,
                SUM(pm.reach) as total_reach,
                SUM(pm.saves) as total_saves
                FROM posts p
                LEFT JOIN post_metrics pm ON p.id = pm.post_id
                WHERE p.client_id = :client_id
                AND p.status = 'published'
                AND DATE(p.published_at) BETWEEN :date_from AND :date_to
                AND p.deleted_at IS NULL";
        
        $metrics = $this->db->queryOne($sql, [
            'client_id' => $clientId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $totalEngagement = ($metrics['total_likes'] ?? 0) + 
                          ($metrics['total_comments'] ?? 0) + 
                          ($metrics['total_shares'] ?? 0);
        
        $engagementRate = 0;
        if ($metrics['total_reach'] > 0) {
            $engagementRate = ($totalEngagement / $metrics['total_reach']) * 100;
        }
        
        return [
            'total_posts' => (int)($metrics['total_posts'] ?? 0),
            'total_likes' => (int)($metrics['total_likes'] ?? 0),
            'total_comments' => (int)($metrics['total_comments'] ?? 0),
            'total_shares' => (int)($metrics['total_shares'] ?? 0),
            'total_reach' => (int)($metrics['total_reach'] ?? 0),
            'total_saves' => (int)($metrics['total_saves'] ?? 0),
            'total_engagement' => $totalEngagement,
            'engagement_rate' => round($engagementRate, 2)
        ];
    }
    
    /**
     * Obtiene métricas de anuncios
     */
    private function getAdsMetrics($clientId, $dateFrom, $dateTo) {
        $sql = "SELECT 
                COUNT(DISTINCT c.id) as total_campaigns,
                SUM(cm.impressions) as total_impressions,
                SUM(cm.reach) as total_reach,
                SUM(cm.clicks) as total_clicks,
                AVG(cm.ctr) as avg_ctr,
                AVG(cm.cpc) as avg_cpc,
                AVG(cm.cpm) as avg_cpm,
                SUM(cm.spend) as total_spend,
                SUM(cm.conversions) as total_conversions
                FROM campaigns c
                LEFT JOIN campaign_metrics cm ON c.id = cm.campaign_id
                WHERE c.client_id = :client_id
                AND cm.date BETWEEN :date_from AND :date_to";
        
        $metrics = $this->db->queryOne($sql, [
            'client_id' => $clientId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $calculatedCTR = 0;
        if ($metrics['total_impressions'] > 0 && $metrics['total_clicks'] > 0) {
            $calculatedCTR = ($metrics['total_clicks'] / $metrics['total_impressions']) * 100;
        }
        
        $calculatedCPC = 0;
        if ($metrics['total_clicks'] > 0 && $metrics['total_spend'] > 0) {
            $calculatedCPC = $metrics['total_spend'] / $metrics['total_clicks'];
        }
        
        return [
            'total_campaigns' => (int)($metrics['total_campaigns'] ?? 0),
            'total_impressions' => (int)($metrics['total_impressions'] ?? 0),
            'total_reach' => (int)($metrics['total_reach'] ?? 0),
            'total_clicks' => (int)($metrics['total_clicks'] ?? 0),
            'avg_ctr' => round((float)($metrics['avg_ctr'] ?? $calculatedCTR), 2),
            'avg_cpc' => round((float)($metrics['avg_cpc'] ?? $calculatedCPC), 2),
            'avg_cpm' => round((float)($metrics['avg_cpm'] ?? 0), 2),
            'total_spend' => round((float)($metrics['total_spend'] ?? 0), 2),
            'total_conversions' => (int)($metrics['total_conversions'] ?? 0)
        ];
    }
    
    /**
     * Obtiene crecimiento de seguidores (simulado)
     */
    private function getFollowerGrowth($clientId, $dateFrom, $dateTo) {
        // Esto requeriría una integración adicional con Meta API
        // Por ahora retornamos datos simulados
        return [
            'initial' => 0,
            'final' => 0,
            'growth' => 0,
            'growth_percentage' => 0
        ];
    }
    
    /**
     * Obtiene mejores publicaciones
     */
    private function getTopPosts($clientId, $dateFrom, $dateTo, $limit = 5) {
        $sql = "SELECT p.id, p.content, p.published_at, p.platform,
                SUM(pm.likes + pm.comments + pm.shares) as total_engagement,
                SUM(pm.reach) as total_reach
                FROM posts p
                LEFT JOIN post_metrics pm ON p.id = pm.post_id
                WHERE p.client_id = :client_id
                AND p.status = 'published'
                AND DATE(p.published_at) BETWEEN :date_from AND :date_to
                AND p.deleted_at IS NULL
                GROUP BY p.id
                ORDER BY total_engagement DESC
                LIMIT :limit";
        
        return $this->db->query($sql, [
            'client_id' => $clientId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit
        ]);
    }
    
    /**
     * Obtiene mejores campañas
     */
    private function getTopCampaigns($clientId, $dateFrom, $dateTo, $limit = 3) {
        $sql = "SELECT c.id, c.name, c.objective,
                SUM(cm.clicks) as total_clicks,
                SUM(cm.spend) as total_spend,
                SUM(cm.conversions) as total_conversions
                FROM campaigns c
                LEFT JOIN campaign_metrics cm ON c.id = cm.campaign_id
                WHERE c.client_id = :client_id
                AND cm.date BETWEEN :date_from AND :date_to
                GROUP BY c.id
                ORDER BY total_conversions DESC, total_clicks DESC
                LIMIT :limit";
        
        return $this->db->query($sql, [
            'client_id' => $clientId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit
        ]);
    }
    
    /**
     * Genera resumen ejecutivo
     */
    private function generateSummary($organic, $ads, $followerGrowth) {
        return [
            'total_posts' => $organic['total_posts'],
            'total_engagement' => $organic['total_engagement'],
            'total_reach_organic' => $organic['total_reach'],
            'total_spend_ads' => $ads['total_spend'],
            'total_clicks_ads' => $ads['total_clicks'],
            'total_reach_ads' => $ads['total_reach'],
            'total_reach_combined' => $organic['total_reach'] + $ads['total_reach'],
            'follower_growth' => $followerGrowth['growth']
        ];
    }
    
    /**
     * Genera archivo PDF del reporte
     */
    public function generatePDF($reportData, $outputPath) {
        // Requiere librería para PDF (TCPDF, FPDF, o similar)
        // Por ahora retornamos el path donde se guardaría
        
        // TODO: Implementar generación de PDF
        // Por ahora solo guardamos los datos
        
        return $outputPath;
    }
}

