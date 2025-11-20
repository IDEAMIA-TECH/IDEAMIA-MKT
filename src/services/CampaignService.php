<?php
/**
 * Servicio de Campañas
 * Maneja lógica de negocio para campañas
 */

require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/CampaignMetrics.php';
require_once __DIR__ . '/../models/SocialAccount.php';
require_once __DIR__ . '/MetaAPIService.php';

class CampaignService {
    private $db;
    private $campaign;
    private $campaignMetrics;
    private $socialAccount;
    private $metaAPI;
    
    public function __construct($db) {
        $this->db = $db;
        $this->campaign = new Campaign($db);
        $this->campaignMetrics = new CampaignMetrics($db);
        $this->socialAccount = new SocialAccount($db);
        $this->metaAPI = new MetaAPIService();
    }
    
    /**
     * Sincroniza campañas desde Meta API
     */
    public function syncCampaignsFromMeta($clientId, $adAccountId) {
        // Obtener cuenta de red social del cliente
        $accounts = $this->socialAccount->findByClientId($clientId);
        
        if (empty($accounts)) {
            throw new Exception('No hay cuentas de red social conectadas para este cliente');
        }
        
        // Usar la primera cuenta conectada
        $account = $this->socialAccount->findById($accounts[0]['id']);
        $accessToken = $account['access_token'];
        
        // Obtener campañas desde Meta
        $metaCampaigns = $this->metaAPI->getCampaigns($adAccountId, $accessToken);
        
        $synced = [];
        
        foreach ($metaCampaigns as $metaCampaign) {
            // Verificar si ya existe
            $existing = $this->campaign->list([
                'client_id' => $clientId,
                'per_page' => 1000
            ]);
            
            $exists = false;
            foreach ($existing['data'] as $camp) {
                if ($camp['meta_campaign_id'] === $metaCampaign['id']) {
                    // Actualizar
                    $this->campaign->update($camp['id'], [
                        'name' => $metaCampaign['name'],
                        'objective' => $metaCampaign['objective'] ?? null,
                        'daily_budget' => isset($metaCampaign['daily_budget']) ? $metaCampaign['daily_budget'] / 100 : null,
                        'status' => strtolower($metaCampaign['status']),
                        'start_date' => $metaCampaign['start_time'] ? date('Y-m-d', strtotime($metaCampaign['start_time'])) : null,
                        'end_date' => $metaCampaign['end_time'] ? date('Y-m-d', strtotime($metaCampaign['end_time'])) : null
                    ]);
                    $exists = true;
                    $synced[] = $camp['id'];
                    break;
                }
            }
            
            if (!$exists) {
                // Crear nueva
                $campaignData = [
                    'client_id' => $clientId,
                    'social_account_id' => $accounts[0]['id'],
                    'ad_account_id' => $adAccountId,
                    'name' => $metaCampaign['name'],
                    'objective' => $metaCampaign['objective'] ?? null,
                    'daily_budget' => isset($metaCampaign['daily_budget']) ? $metaCampaign['daily_budget'] / 100 : null,
                    'total_budget' => isset($metaCampaign['lifetime_budget']) ? $metaCampaign['lifetime_budget'] / 100 : null,
                    'start_date' => $metaCampaign['start_time'] ? date('Y-m-d', strtotime($metaCampaign['start_time'])) : null,
                    'end_date' => $metaCampaign['end_time'] ? date('Y-m-d', strtotime($metaCampaign['end_time'])) : null,
                    'status' => strtolower($metaCampaign['status']),
                    'meta_campaign_id' => $metaCampaign['id']
                ];
                
                $newCampaign = $this->campaign->create($campaignData);
                if ($newCampaign) {
                    $synced[] = $newCampaign['id'];
                }
            }
        }
        
        return $synced;
    }
    
    /**
     * Sincroniza métricas de una campaña desde Meta API
     */
    public function syncCampaignMetrics($campaignId, $dateFrom = null, $dateTo = null) {
        $campaign = $this->campaign->findById($campaignId);
        
        if (!$campaign || !$campaign['meta_campaign_id']) {
            throw new Exception('Campaña no encontrada o no tiene ID de Meta');
        }
        
        // Obtener cuenta de red social
        $account = $this->socialAccount->findById($campaign['social_account_id']);
        if (!$account) {
            throw new Exception('Cuenta de red social no encontrada');
        }
        
        $accessToken = $account['access_token'];
        $metaCampaignId = $campaign['meta_campaign_id'];
        
        // Obtener métricas diarias
        $insights = $this->metaAPI->getCampaignInsightsDaily(
            $metaCampaignId,
            $accessToken,
            $dateFrom,
            $dateTo
        );
        
        $synced = 0;
        
        foreach ($insights as $insight) {
            $date = $insight['date_start'];
            
            $metrics = [
                'impressions' => (int)($insight['impressions'] ?? 0),
                'reach' => (int)($insight['reach'] ?? 0),
                'clicks' => (int)($insight['clicks'] ?? 0),
                'ctr' => (float)($insight['ctr'] ?? 0),
                'cpc' => (float)($insight['cpc'] ?? 0),
                'cpm' => (float)($insight['cpm'] ?? 0),
                'spend' => (float)($insight['spend'] ?? 0),
                'conversions' => (int)($insight['conversions'] ?? 0)
            ];
            
            if ($this->campaignMetrics->upsert($campaignId, $date, $metrics)) {
                $synced++;
            }
        }
        
        return $synced;
    }
    
    /**
     * Obtiene resumen completo de una campaña con métricas
     */
    public function getCampaignSummary($campaignId, $dateFrom = null, $dateTo = null) {
        $summary = $this->campaign->getSummary($campaignId, $dateFrom, $dateTo);
        
        if (!$summary) {
            return null;
        }
        
        // Obtener métricas diarias para gráficos
        $dailyMetrics = $this->campaignMetrics->getAggregated($campaignId, 'day', $dateFrom, $dateTo);
        
        $summary['daily_metrics'] = $dailyMetrics;
        
        return $summary;
    }
}

