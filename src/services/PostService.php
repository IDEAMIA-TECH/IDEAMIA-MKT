<?php
/**
 * Servicio de Publicaciones
 * Maneja lógica de negocio para publicaciones
 */

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/SocialAccount.php';
require_once __DIR__ . '/MetaAPIService.php';

class PostService {
    private $db;
    private $post;
    private $socialAccount;
    private $metaAPI;
    
    public function __construct($db) {
        $this->db = $db;
        $this->post = new Post($db);
        $this->socialAccount = new SocialAccount($db);
        $this->metaAPI = new MetaAPIService();
    }
    
    /**
     * Publica un post inmediatamente
     */
    public function publishNow($postId) {
        $post = $this->post->findById($postId);
        
        if (!$post) {
            throw new Exception('Publicación no encontrada');
        }
        
        if ($post['status'] === 'published') {
            throw new Exception('La publicación ya fue publicada');
        }
        
        // Obtener cuenta de red social
        $account = $this->socialAccount->findById($post['social_account_id']);
        
        if (!$account) {
            throw new Exception('Cuenta de red social no encontrada');
        }
        
        if ($account['status'] !== 'connected') {
            throw new Exception('La cuenta de red social no está conectada');
        }
        
        $accessToken = $account['access_token'];
        $accountId = $account['account_id'];
        $platform = $account['platform'];
        
        try {
            if ($platform === 'facebook') {
                // Publicar en Facebook
                $result = $this->metaAPI->publishToPage(
                    $accountId,
                    $accessToken,
                    $post['content'],
                    $post['link_url']
                );
                
                if (isset($result['id'])) {
                    $this->post->markAsPublished($postId, $result['id']);
                    return ['success' => true, 'post_id' => $result['id']];
                } else {
                    throw new Exception('Error al publicar en Facebook');
                }
            } elseif ($platform === 'instagram') {
                // Publicar en Instagram (requiere imagen)
                if (empty($post['media_urls']) || !isset($post['media_urls'][0])) {
                    throw new Exception('Instagram requiere al menos una imagen');
                }
                
                $imageUrl = $post['media_urls'][0];
                $result = $this->metaAPI->publishToInstagram(
                    $accountId,
                    $accessToken,
                    $imageUrl,
                    $post['content']
                );
                
                if (isset($result['id'])) {
                    $this->post->markAsPublished($postId, $result['id']);
                    return ['success' => true, 'post_id' => $result['id']];
                } else {
                    throw new Exception('Error al publicar en Instagram');
                }
            } else {
                throw new Exception('Plataforma no soportada');
            }
        } catch (Exception $e) {
            $this->post->markAsFailed($postId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Procesa publicaciones programadas pendientes
     */
    public function processScheduledPosts($limit = 10) {
        $posts = $this->post->getPendingPosts($limit);
        $results = [];
        
        foreach ($posts as $postData) {
            try {
                // Obtener access token desencriptado
                $account = $this->socialAccount->findById($postData['social_account_id']);
                $accessToken = $account['access_token'];
                
                $postId = $postData['id'];
                $accountId = $postData['account_id'];
                $platform = $postData['account_platform'];
                
                if ($platform === 'facebook') {
                    $result = $this->metaAPI->publishToPage(
                        $accountId,
                        $accessToken,
                        $postData['content'],
                        $postData['link_url']
                    );
                    
                    if (isset($result['id'])) {
                        $this->post->markAsPublished($postId, $result['id']);
                        $results[] = ['post_id' => $postId, 'status' => 'success'];
                    } else {
                        throw new Exception('Error al publicar');
                    }
                } elseif ($platform === 'instagram') {
                    if (empty($postData['media_urls']) || !isset($postData['media_urls'][0])) {
                        $this->post->markAsFailed($postId, 'Instagram requiere al menos una imagen');
                        $results[] = ['post_id' => $postId, 'status' => 'failed', 'error' => 'Sin imagen'];
                        continue;
                    }
                    
                    $imageUrl = $postData['media_urls'][0];
                    $result = $this->metaAPI->publishToInstagram(
                        $accountId,
                        $accessToken,
                        $imageUrl,
                        $postData['content']
                    );
                    
                    if (isset($result['id'])) {
                        $this->post->markAsPublished($postId, $result['id']);
                        $results[] = ['post_id' => $postId, 'status' => 'success'];
                    } else {
                        throw new Exception('Error al publicar');
                    }
                }
            } catch (Exception $e) {
                $this->post->markAsFailed($postData['id'], $e->getMessage());
                $results[] = ['post_id' => $postData['id'], 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * Valida una publicación antes de guardarla
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['content'])) {
            $errors[] = 'El contenido es requerido';
        }
        
        if (empty($data['scheduled_at'])) {
            $errors[] = 'La fecha programada es requerida';
        }
        
        if (empty($data['client_id'])) {
            $errors[] = 'El cliente es requerido';
        }
        
        if (empty($data['platform'])) {
            $errors[] = 'La plataforma es requerida';
        }
        
        // Validar límites de caracteres
        if ($data['platform'] === 'instagram' && strlen($data['content']) > 2200) {
            $errors[] = 'Instagram permite máximo 2200 caracteres';
        }
        
        if ($data['platform'] === 'facebook' && strlen($data['content']) > 5000) {
            $errors[] = 'Facebook permite máximo 5000 caracteres';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

