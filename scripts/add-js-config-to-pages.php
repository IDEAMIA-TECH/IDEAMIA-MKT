<?php
/**
 * Script para agregar js-config.php a todas las páginas
 */

$pagesDir = __DIR__ . '/../pages';
$files = glob($pagesDir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Buscar donde están los scripts
    if (strpos($content, 'bootstrap.bundle.min.js') !== false && strpos($content, 'js-config.php') === false) {
        // Agregar js-config.php antes del primer script local
        $content = preg_replace(
            '/(<script src="https:\/\/cdn\.jsdelivr\.net[^<]+<\/script>\s*)(<script src=")/',
            '$1<?php require_once __DIR__ . \'/../includes/js-config.php\'; ?>\n    $2',
            $content
        );
        
        // Si no hay script de CDN, agregar antes del primer script
        if (strpos($content, 'js-config.php') === false) {
            $content = preg_replace(
                '/(<script[^>]*src="[^"]*\.js"[^>]*>)/',
                '<?php require_once __DIR__ . \'/../includes/js-config.php\'; ?>\n    $1',
                $content,
                1
            );
        }
        
        file_put_contents($file, $content);
        echo "Actualizado: " . basename($file) . "\n";
    }
}

echo "Proceso completado.\n";

