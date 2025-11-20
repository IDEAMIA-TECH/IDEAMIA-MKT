<?php
/**
 * Servicio para interactuar con Meta Graph API
 * Facebook e Instagram
 */

class MetaAPIService {
    private $config;
    private $appId;
    private $appSecret;
    private $apiVersion;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->appId = $this->config['META_APP_ID'];
        $this->appSecret = $this->config['META_APP_SECRET'];
        $this->apiVersion = $this->config['META_API_VERSION'];
    }
    
    /**
     * Genera URL de autorización OAuth
     */
    public function getAuthUrl($redirectUri, $state = null) {
        $scopes = [
            'pages_read_engagement',
            'pages_manage_posts',
            'pages_read_user_content',
            'instagram_basic',
            'instagram_content_publish',
            'ads_read'
        ];
        
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => $state ?: bin2hex(random_bytes(16))
        ];
        
        return 'https://www.facebook.com/' . $this->apiVersion . '/dialog/oauth?' . http_build_query($params);
    }
    
    /**
     * Intercambia código de autorización por access token
     */
    public function exchangeCodeForToken($code, $redirectUri) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/oauth/access_token';
        
        $params = [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        
        if (isset($response['access_token'])) {
            return [
                'access_token' => $response['access_token'],
                'token_type' => $response['token_type'] ?? 'bearer',
                'expires_in' => $response['expires_in'] ?? null
            ];
        }
        
        throw new Exception('Error al obtener access token: ' . ($response['error']['message'] ?? 'Error desconocido'));
    }
    
    /**
     * Obtiene información del usuario autenticado
     */
    public function getMe($accessToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/me';
        $params = ['access_token' => $accessToken];
        
        return $this->makeRequest($url, $params, 'GET');
    }
    
    /**
     * Obtiene las páginas de Facebook del usuario
     */
    public function getPages($accessToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/me/accounts';
        $params = [
            'access_token' => $accessToken,
            'fields' => 'id,name,access_token,category,tasks'
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        return $response['data'] ?? [];
    }
    
    /**
     * Obtiene la cuenta de Instagram Business asociada a una página
     */
    public function getInstagramAccount($pageId, $pageAccessToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $pageId;
        $params = [
            'access_token' => $pageAccessToken,
            'fields' => 'instagram_business_account'
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        
        if (isset($response['instagram_business_account']['id'])) {
            $igAccountId = $response['instagram_business_account']['id'];
            
            // Obtener información de la cuenta de Instagram
            $igUrl = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $igAccountId;
            $igParams = [
                'access_token' => $pageAccessToken,
                'fields' => 'id,username,name,profile_picture_url'
            ];
            
            $igResponse = $this->makeRequest($igUrl, $igParams, 'GET');
            return $igResponse;
        }
        
        return null;
    }
    
    /**
     * Publica en una página de Facebook
     */
    public function publishToPage($pageId, $pageAccessToken, $message, $link = null) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $pageId . '/feed';
        
        $params = [
            'access_token' => $pageAccessToken,
            'message' => $message
        ];
        
        if ($link) {
            $params['link'] = $link;
        }
        
        return $this->makeRequest($url, $params, 'POST');
    }
    
    /**
     * Publica en Instagram
     */
    public function publishToInstagram($igAccountId, $pageAccessToken, $imageUrl, $caption) {
        // Paso 1: Crear contenedor de media
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $igAccountId . '/media';
        
        $params = [
            'access_token' => $pageAccessToken,
            'image_url' => $imageUrl,
            'caption' => $caption
        ];
        
        $response = $this->makeRequest($url, $params, 'POST');
        
        if (!isset($response['id'])) {
            throw new Exception('Error al crear contenedor de media');
        }
        
        $creationId = $response['id'];
        
        // Paso 2: Publicar
        $publishUrl = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $igAccountId . '/media_publish';
        $publishParams = [
            'access_token' => $pageAccessToken,
            'creation_id' => $creationId
        ];
        
        return $this->makeRequest($publishUrl, $publishParams, 'POST');
    }
    
    /**
     * Obtiene métricas de una página
     */
    public function getPageInsights($pageId, $pageAccessToken, $metrics = ['page_fans', 'page_engaged_users']) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $pageId . '/insights';
        
        $params = [
            'access_token' => $pageAccessToken,
            'metric' => implode(',', $metrics),
            'period' => 'day'
        ];
        
        return $this->makeRequest($url, $params, 'GET');
    }
    
    /**
     * Verifica si un token es válido
     */
    public function verifyToken($accessToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/me';
        $params = ['access_token' => $accessToken];
        
        try {
            $response = $this->makeRequest($url, $params, 'GET');
            return isset($response['id']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Refresca un token de larga duración
     */
    public function refreshLongLivedToken($shortLivedToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/oauth/access_token';
        
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        
        if (isset($response['access_token'])) {
            return [
                'access_token' => $response['access_token'],
                'expires_in' => $response['expires_in'] ?? null
            ];
        }
        
        throw new Exception('Error al refrescar token');
    }
    
    /**
     * Obtiene Ad Accounts del usuario
     */
    public function getAdAccounts($accessToken) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/me/adaccounts';
        $params = [
            'access_token' => $accessToken,
            'fields' => 'id,name,account_id,currency,timezone_name'
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        return $response['data'] ?? [];
    }
    
    /**
     * Obtiene campañas de un Ad Account
     */
    public function getCampaigns($adAccountId, $accessToken, $fields = null) {
        if (!$fields) {
            $fields = 'id,name,objective,status,daily_budget,lifetime_budget,start_time,end_time,created_time';
        }
        
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $adAccountId . '/campaigns';
        $params = [
            'access_token' => $accessToken,
            'fields' => $fields
        ];
        
        $response = $this->makeRequest($url, $params, 'GET');
        return $response['data'] ?? [];
    }
    
    /**
     * Obtiene métricas de una campaña
     */
    public function getCampaignInsights($campaignId, $accessToken, $dateFrom = null, $dateTo = null) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $campaignId . '/insights';
        
        $params = [
            'access_token' => $accessToken,
            'fields' => 'impressions,reach,clicks,ctr,cpc,cpm,spend,conversions',
            'level' => 'campaign',
            'time_range' => [
                'since' => $dateFrom ?: date('Y-m-d', strtotime('-30 days')),
                'until' => $dateTo ?: date('Y-m-d')
            ]
        ];
        
        // Meta API requiere time_range como JSON string
        $params['time_range'] = json_encode($params['time_range']);
        
        $response = $this->makeRequest($url, $params, 'GET');
        return $response['data'] ?? [];
    }
    
    /**
     * Obtiene métricas diarias de una campaña
     */
    public function getCampaignInsightsDaily($campaignId, $accessToken, $dateFrom = null, $dateTo = null) {
        $url = 'https://graph.facebook.com/' . $this->apiVersion . '/' . $campaignId . '/insights';
        
        $params = [
            'access_token' => $accessToken,
            'fields' => 'impressions,reach,clicks,ctr,cpc,cpm,spend,conversions',
            'level' => 'campaign',
            'time_increment' => 1, // Diario
            'time_range' => [
                'since' => $dateFrom ?: date('Y-m-d', strtotime('-30 days')),
                'until' => $dateTo ?: date('Y-m-d')
            ]
        ];
        
        $params['time_range'] = json_encode($params['time_range']);
        
        $response = $this->makeRequest($url, $params, 'GET');
        return $response['data'] ?? [];
    }
    
    /**
     * Realiza una petición HTTP
     */
    private function makeRequest($url, $params = [], $method = 'GET') {
        $ch = curl_init();
        
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Error cURL: ' . $error);
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $data['error']['message'] ?? 'Error HTTP ' . $httpCode;
            throw new Exception($errorMsg);
        }
        
        return $data;
    }
}

