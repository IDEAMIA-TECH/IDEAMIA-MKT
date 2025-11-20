<?php
/**
 * Modelo User
 * Gestiona usuarios del sistema
 */

require_once __DIR__ . '/../helpers/Database.php';

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Busca un usuario por email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email AND deleted_at IS NULL";
        return $this->db->queryOne($sql, ['email' => $email]);
    }
    
    /**
     * Busca un usuario por ID
     */
    public function findById($id) {
        $sql = "SELECT id, name, email, role, client_id, created_at FROM users WHERE id = :id AND deleted_at IS NULL";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Verifica credenciales de login
     */
    public function verifyCredentials($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // No retornar la contraseña
        unset($user['password']);
        return $user;
    }
    
    /**
     * Crea un nuevo usuario
     */
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, role, client_id, created_at, updated_at) 
                VALUES (:name, :email, :password, :role, :client_id, NOW(), NOW())";
        
        $params = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'cm',
            'client_id' => $data['client_id'] ?? null
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result['success']) {
            return $this->findById($result['lastInsertId']);
        }
        
        return false;
    }
    
    /**
     * Actualiza un usuario
     */
    public function update($id, $data) {
        $updates = [];
        $params = ['id' => $id];
        
        if (isset($data['name'])) {
            $updates[] = "name = :name";
            $params['name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $updates[] = "email = :email";
            $params['email'] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $updates[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $updates[] = "role = :role";
            $params['role'] = $data['role'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "updated_at = NOW()";
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id AND deleted_at IS NULL";
        
        $result = $this->db->execute($sql, $params);
        return $result['success'] ? $this->findById($id) : false;
    }
    
    /**
     * Lista usuarios con paginación
     */
    public function list($filters = []) {
        $sql = "SELECT id, name, email, role, client_id, created_at FROM users WHERE deleted_at IS NULL";
        $params = [];
        
        if (isset($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Paginación
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 20;
        $offset = ($page - 1) * $perPage;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params);
    }
}

