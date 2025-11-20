<?php
/**
 * Modelo SocialAccount
 * Gestiona cuentas de redes sociales conectadas
 */

require_once __DIR__ . '/../helpers/Database.php';

class SocialAccount {
    private $db;
    private $config;
    
    public function __construct($db) {
        $this->db = $db;
        $this->config = require __DIR__ . '/../../config/config.php';
    }
    
    /**
     * Busca una cuenta por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM social_accounts WHERE id = :id";
        $account = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($account) {
            // Desencriptar tokens
            $account['access_token'] = $this->decrypt($account['access_token']);
            $account['refresh_token'] = $account['refresh_token'] ? $this->decrypt($account['refresh_token']) : null;
        }
        
        return $account;
    }
    
    /**
     * Busca cuentas por cliente
     */
    public function findByClientId($clientId) {
        $sql = "SELECT id, client_id, platform, account_id, account_name, token_expires_at, status, created_at 
                FROM social_accounts 
                WHERE client_id = :client_id 
                ORDER BY platform, account_name";
        return $this->db->query($sql, ['client_id' => $clientId]);
    }
    
    /**
     * Crea una nueva cuenta de red social
     */
    public function create($data) {
        $sql = "INSERT INTO social_accounts (
            client_id, platform, account_id, account_name,
            access_token, refresh_token, token_expires_at,
            status, permissions, created_at, updated_at
        ) VALUES (
            :client_id, :platform, :account_id, :account_name,
            :access_token, :refresh_token, :token_expires_at,
            :status, :permissions, NOW(), NOW()
        )";
        
        $params = [
            'client_id' => $data['client_id'],
            'platform' => $data['platform'],
            'account_id' => $data['account_id'],
            'account_name' => $data['account_name'],
            'access_token' => $this->encrypt($data['access_token']),
            'refresh_token' => isset($data['refresh_token']) ? $this->encrypt($data['refresh_token']) : null,
            'token_expires_at' => $data['token_expires_at'] ?? null,
            'status' => $data['status'] ?? 'connected',
            'permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza una cuenta
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'account_name', 'access_token', 'refresh_token',
            'token_expires_at', 'status', 'permissions'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'access_token' || $field === 'refresh_token') {
                    $updates[] = "{$field} = :{$field}";
                    $params[$field] = $this->encrypt($data[$field]);
                } elseif ($field === 'permissions') {
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
        $sql = "UPDATE social_accounts SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Elimina una cuenta
     */
    public function delete($id) {
        $sql = "DELETE FROM social_accounts WHERE id = :id";
        $result = $this->db->execute($sql, ['id' => $id]);
        return $result['success'];
    }
    
    /**
     * Verifica el estado de una cuenta
     */
    public function checkStatus($id) {
        $account = $this->findById($id);
        
        if (!$account) {
            return null;
        }
        
        // Verificar si el token está expirado
        $isExpired = false;
        if ($account['token_expires_at']) {
            $expiresAt = new DateTime($account['token_expires_at']);
            $now = new DateTime();
            $isExpired = $now >= $expiresAt;
        }
        
        return [
            'id' => $account['id'],
            'status' => $isExpired ? 'expired' : $account['status'],
            'expires_at' => $account['token_expires_at'],
            'is_expired' => $isExpired,
            'days_until_expiry' => $account['token_expires_at'] ? 
                (new DateTime($account['token_expires_at']))->diff(new DateTime())->days : null
        ];
    }
    
    /**
     * Obtiene el access token desencriptado
     */
    public function getAccessToken($id) {
        $account = $this->findById($id);
        return $account ? $account['access_token'] : null;
    }
    
    /**
     * Encripta un token
     */
    private function encrypt($data) {
        $key = $this->config['ENCRYPTION_KEY'];
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Desencripta un token
     */
    private function decrypt($data) {
        $key = $this->config['ENCRYPTION_KEY'];
        $method = 'AES-256-CBC';
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
    }
}

