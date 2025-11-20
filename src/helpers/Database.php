<?php
/**
 * Clase Helper para Conexión a Base de Datos
 * Utiliza PDO para conexiones MySQL
 */

class Database {
    private $connection;
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->connect();
    }
    
    /**
     * Establece conexión con la base de datos
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['DB_HOST']};port={$this->config['DB_PORT']};dbname={$this->config['DB_NAME']};charset={$this->config['DB_CHARSET']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO(
                $dsn,
                $this->config['DB_USER'],
                $this->config['DB_PASS'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
        }
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Ejecuta una consulta SELECT y retorna todos los resultados
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en query: " . $e->getMessage());
            throw new Exception("Error al ejecutar consulta");
        }
    }
    
    /**
     * Ejecuta una consulta SELECT y retorna un solo resultado
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en queryOne: " . $e->getMessage());
            throw new Exception("Error al ejecutar consulta");
        }
    }
    
    /**
     * Ejecuta una consulta INSERT, UPDATE o DELETE
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            return [
                'success' => $result,
                'lastInsertId' => $this->connection->lastInsertId(),
                'rowCount' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            error_log("Error en execute: " . $e->getMessage());
            throw new Exception("Error al ejecutar operación");
        }
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
}

