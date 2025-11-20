<?php
/**
 * Script para actualizar todas las rutas inline en páginas PHP
 */

$pagesDir = __DIR__ . '/../pages';
$files = glob($pagesDir . '/*.php');

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
    
    // Reemplazar window.location.href = '/index.php'
    $content = str_replace(
        "window.location.href = '/index.php'",
        "window.location.href = appUrl('index.php')",
        $content
    );
    
    // Reemplazar window.location.href = "/index.php"
    $content = str_replace(
        'window.location.href = "/index.php"',
        'window.location.href = appUrl("index.php")',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Actualizado: " . basename($file) . "\n";
    }
}

echo "Proceso completado.\n";

