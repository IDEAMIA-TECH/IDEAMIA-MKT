<?php
/**
 * Script para actualizar todas las rutas API en archivos JavaScript
 */

$jsDir = __DIR__ . '/../assets/js';
$files = glob($jsDir . '/*.js');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Reemplazar fetch('/api/...') con fetch(apiUrl('...'))
    $content = preg_replace(
        "/fetch\(['\"]\/api\/([^'\"]+)['\"]/",
        "fetch(apiUrl('$1')",
        $content
    );
    
    // Reemplazar fetch(`/api/...`) con fetch(apiUrl('...'))
    $content = preg_replace(
        '/fetch\(`\/api\/([^`]+)`\)/',
        "fetch(apiUrl('$1'))",
        $content
    );
    
    // Reemplazar window.location.href = `/api/...`
    $content = preg_replace(
        '/window\.location\.href = `\/api\/([^`]+)`/',
        "window.location.href = apiUrl('$1')",
        $content
    );
    
    // Reemplazar window.open(`/api/...`)
    $content = preg_replace(
        '/window\.open\(`\/api\/([^`]+)`/',
        "window.open(apiUrl('$1')",
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Actualizado: " . basename($file) . "\n";
    }
}

echo "Proceso completado.\n";

