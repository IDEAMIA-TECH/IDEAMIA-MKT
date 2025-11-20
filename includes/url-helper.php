<?php
/**
 * Helper de URLs para uso en páginas
 * Incluir este archivo para usar las funciones url(), apiUrl(), appUrl()
 */

require_once __DIR__ . '/../src/helpers/UrlHelper.php';

/**
 * Función helper para generar URLs
 */
function url($path = '') {
    return UrlHelper::url($path);
}

/**
 * Función helper para generar URLs absolutas
 */
function absoluteUrl($path = '') {
    return UrlHelper::absolute($path);
}
