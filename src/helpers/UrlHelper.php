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
            // Ejemplo: /IDEAMIA-MKT/index.php -> /IDEAMIA-MKT
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            $scriptDir = dirname($scriptPath);
            
            // Normalizar: convertir a formato Unix y limpiar
            $scriptDir = str_replace('\\', '/', $scriptDir);
            $scriptDir = trim($scriptDir, '/');
            
            // Si el script está en la raíz (dirname = / o .), basePath es vacío
            if ($scriptDir === '.' || $scriptDir === '' || $scriptDir === '/') {
                self::$basePath = '';
            } else {
                // Agregar barra inicial
                self::$basePath = '/' . $scriptDir;
            }
        }
        
        return self::$basePath;
    }
    
    /**
     * Genera una URL relativa al directorio base
     */
    public static function url($path = '') {
        $basePath = self::getBasePath();
        $path = ltrim($path, '/');
        
        if (empty($path)) {
            return $basePath ?: '/';
        }
        
        return $basePath . '/' . $path;
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

