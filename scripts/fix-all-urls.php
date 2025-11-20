<?php
/**
 * Script para actualizar todas las rutas en las páginas HTML
 * Reemplaza rutas absolutas por función url()
 */

$pagesDir = __DIR__ . '/../pages';
$files = glob($pagesDir . '/*.php');

$replacements = [
    // Enlaces de navegación
    'href="/pages/' => 'href="<?php echo url(\'pages/',
    'href="/index.php"' => 'href="<?php echo url(\'index.php\'); ?>"',
    
    // Scripts
    'src="/assets/' => 'src="<?php echo url(\'assets/',
    
    // API calls en JavaScript (necesitan ser manejados diferente)
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Agregar require de url-helper si no existe
    if (strpos($content, 'url-helper.php') === false && strpos($content, 'auth-check.php') !== false) {
        $content = str_replace(
            "require_once __DIR__ . '/../includes/auth-check.php';",
            "require_once __DIR__ . '/../includes/auth-check.php';\nrequire_once __DIR__ . '/../includes/url-helper.php';",
            $content
        );
    }
    
    // Reemplazar enlaces de páginas
    $content = preg_replace(
        '/href="\/pages\/([^"]+)"/',
        'href="<?php echo url(\'pages/$1\'); ?>"',
        $content
    );
    
    // Reemplazar scripts
    $content = preg_replace(
        '/src="\/assets\/([^"]+)"/',
        'src="<?php echo url(\'assets/$1\'); ?>"',
        $content
    );
    
    // Reemplazar index.php
    $content = str_replace(
        'href="/index.php"',
        'href="<?php echo url(\'index.php\'); ?>"',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Actualizado: " . basename($file) . "\n";
    }
}

echo "Proceso completado.\n";

