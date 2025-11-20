<?php
/**
 * Script de Prueba para UrlHelper
 * Ejecutar desde navegador para verificar rutas
 */

require_once __DIR__ . '/src/helpers/UrlHelper.php';

echo "<h1>Test de UrlHelper</h1>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";

echo "<h2>Rutas Generadas:</h2>";
echo "<ul>";
echo "<li>Base Path: <code>" . UrlHelper::url() . "</code></li>";
echo "<li>Dashboard: <code>" . UrlHelper::url('pages/dashboard.php') . "</code></li>";
echo "<li>Index: <code>" . UrlHelper::url('index.php') . "</code></li>";
echo "<li>Clients: <code>" . UrlHelper::url('pages/clients.php') . "</code></li>";
echo "</ul>";

echo "<h2>URLs Absolutas:</h2>";
echo "<ul>";
echo "<li>Dashboard: <code>" . UrlHelper::absolute('pages/dashboard.php') . "</code></li>";
echo "</ul>";

