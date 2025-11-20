<?php
/**
 * Servicio de Media
 * Maneja upload y procesamiento de archivos
 */

require_once __DIR__ . '/../models/Media.php';
require_once __DIR__ . '/../helpers/Validator.php';

class MediaService {
    private $db;
    private $media;
    private $config;
    
    public function __construct($db) {
        $this->db = $db;
        $this->media = new Media($db);
        $this->config = require __DIR__ . '/../../config/config.php';
    }
    
    /**
     * Procesa y guarda un archivo subido
     */
    public function uploadFile($file, $clientId, $uploadedBy, $folder = null, $tags = []) {
        // Validar archivo
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            throw new Exception('Archivo inválido: ' . implode(', ', $validation['errors']));
        }
        
        // Determinar tipo de archivo
        $fileType = $this->getFileType($file['type']);
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Crear directorio si no existe
        $uploadDir = $this->config['UPLOAD_DIR'] . 'clients/' . $clientId . '/';
        if ($folder) {
            $uploadDir .= $folder . '/';
        }
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . $filename;
        $relativePath = 'uploads/clients/' . $clientId . '/' . ($folder ? $folder . '/' : '') . $filename;
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Error al guardar el archivo');
        }
        
        // Procesar imagen si es necesario (redimensionar, optimizar)
        if ($fileType === 'image') {
            $this->processImage($filePath);
        }
        
        // Crear registro en BD
        $mediaData = [
            'client_id' => $clientId,
            'uploaded_by' => $uploadedBy,
            'filename' => $filename,
            'original_filename' => $file['name'],
            'file_path' => $relativePath,
            'file_type' => $fileType,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'folder' => $folder,
            'tags' => $tags
        ];
        
        return $this->media->create($mediaData);
    }
    
    /**
     * Valida un archivo
     */
    private function validateFile($file) {
        $errors = [];
        
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error en la subida del archivo';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validar tamaño
        $maxSize = $this->config['MAX_UPLOAD_SIZE'];
        if ($file['size'] > $maxSize) {
            $errors[] = 'El archivo excede el tamaño máximo permitido (' . ($maxSize / 1024 / 1024) . ' MB)';
        }
        
        // Validar tipo
        $allowedTypes = array_merge(
            $this->config['ALLOWED_IMAGE_TYPES'],
            $this->config['ALLOWED_VIDEO_TYPES']
        );
        
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Tipo de archivo no permitido';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Determina el tipo de archivo
     */
    private function getFileType($mimeType) {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } else {
            return 'document';
        }
    }
    
    /**
     * Procesa una imagen (redimensiona si es muy grande)
     */
    private function processImage($filePath) {
        if (!function_exists('imagecreatefromjpeg')) {
            return; // GD no disponible
        }
        
        $maxWidth = 1920;
        $maxHeight = 1080;
        
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Si la imagen es más pequeña que el máximo, no procesar
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }
        
        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // Crear imagen según tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return; // Tipo no soportado
        }
        
        if (!$source) {
            return;
        }
        
        // Crear nueva imagen redimensionada
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Mantener transparencia para PNG y GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Guardar
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filePath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destination, $filePath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($destination);
    }
    
    /**
     * Formatea tamaño de archivo
     */
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

