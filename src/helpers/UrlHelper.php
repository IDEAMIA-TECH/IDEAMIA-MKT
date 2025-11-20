<?php
/**
 * Helper para Generar URLs
 * Maneja rutas relativas al directorio base de la aplicación
 */

class UrlHelper {
    private static $basePath = null;
    
    /**
     * Obtiene el directorio base de la aplicación
     */
    private static function getBasePath() {
        if (self::$basePath === null) {
            // Obtener el path del script que se está ejecutando
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            
            // Normalizar: convertir a formato Unix
            $scriptPath = str_replace('\\', '/', $scriptPath);
            
            // Obtener el directorio raíz del proyecto
            // Si el script está en: /IDEAMIA-MKT/index.php -> basePath = /IDEAMIA-MKT
            // Si el script está en: /IDEAMIA-MKT/pages/clients.php -> basePath = /IDEAMIA-MKT
            // Si el script está en: /index.php -> basePath = ''
            
            // Remover el nombre del archivo
            $scriptDir = dirname($scriptPath);
            $scriptDir = trim($scriptDir, '/');
            
            // Si estamos en un subdirectorio (pages, api, etc.), removerlo
            // para obtener el directorio raíz del proyecto
            $parts = explode('/', $scriptDir);
            
            // Si hay partes y la última es 'pages', 'api', etc., removerla
            $knownSubdirs = ['pages', 'api', 'assets', 'includes'];
            if (count($parts) > 1 && in_array(end($parts), $knownSubdirs)) {
                array_pop($parts);
            }
            
            // Reconstruir el path
            $projectRoot = implode('/', $parts);
            $projectRoot = trim($projectRoot, '/');
            
            // Si el proyecto está en la raíz, basePath es vacío
            if ($projectRoot === '' || $projectRoot === '.') {
                self::$basePath = '';
            } else {
                // Agregar barra inicial
                self::$basePath = '/' . $projectRoot;
            }
        }
        
        return self::$basePath;
    }
    
    /**
     * Genera una URL relativa al directorio base
     */
    public static function url($path = '') {
        $basePath = self::getBasePath();
        
        // Si el path está vacío, retornar solo el basePath
        if (empty($path)) {
            return $basePath ?: '/';
        }
        
        // Limpiar el path: remover barras iniciales y duplicadas
        $path = ltrim($path, '/');
        
        // Si el basePath ya está en el path, no duplicar
        if ($basePath && strpos($path, $basePath) === 0) {
            return '/' . $path;
        }
        
        // Construir URL: basePath + path
        if ($basePath) {
            return $basePath . '/' . $path;
        } else {
            return '/' . $path;
        }
    }
    
    /**
     * Genera una URL absoluta completa
     */
    public static function absolute($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = self::getBasePath();
        $path = ltrim($path, '/');
        
        $url = $protocol . '://' . $host;
        if ($basePath) {
            $url .= $basePath;
        }
        if ($path) {
            $url .= '/' . $path;
        }
        
        return $url;
    }
    
    /**
     * Redirige a una URL
     */
    public static function redirect($path, $statusCode = 302) {
        $url = self::url($path);
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}

