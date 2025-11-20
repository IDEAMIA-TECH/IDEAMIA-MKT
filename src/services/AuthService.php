<?php
/**
 * Servicio de Autenticación
 * Maneja login, logout, sesiones
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Validator.php';

class AuthService {
    private $db;
    private $user;
    private $config;
    
    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->startSession();
    }
    
    /**
     * Inicia sesión PHP
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['SESSION_NAME']);
            session_set_cookie_params([
                'lifetime' => $this->config['SESSION_LIFETIME'],
                'path' => '/',
                'domain' => '',
                'secure' => $this->config['SESSION_SECURE'],
                'httponly' => $this->config['SESSION_HTTPONLY'],
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }
    
    /**
     * Autentica un usuario
     */
    public function login($email, $password) {
        // Validar datos
        $validation = Validator::validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => ['required', 'email'],
            'password' => ['required', 'min' => 6]
        ]);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'Datos inválidos',
                'errors' => $validation['errors']
            ];
        }
        
        // Verificar credenciales
        $user = $this->user->verifyCredentials($email, $password);
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'Email o contraseña incorrectos'
            ];
        }
        
        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['client_id'] = $user['client_id'] ?? null;
        $_SESSION['logged_in'] = true;
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'client_id' => $user['client_id']
            ]
        ];
    }
    
    /**
     * Cierra sesión
     */
    public function logout() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Verifica si hay una sesión activa
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Obtiene el usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'client_id' => $_SESSION['client_id'] ?? null
        ];
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_role'] === $role;
    }
    
    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return in_array($_SESSION['user_role'], $roles);
    }
}

