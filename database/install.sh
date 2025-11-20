#!/bin/bash
# Script de Instalación de Base de Datos (Shell)
# IDEAMIA Marketing Platform

echo "========================================"
echo "  IDEAMIA Marketing Platform"
echo "  Instalación de Base de Datos"
echo "========================================"
echo ""

# Verificar que PHP está instalado
if ! command -v php &> /dev/null; then
    echo "✗ Error: PHP no está instalado"
    exit 1
fi

# Verificar que el archivo schema.sql existe
if [ ! -f "database/schema.sql" ]; then
    echo "✗ Error: No se encontró el archivo database/schema.sql"
    exit 1
fi

# Verificar que config.php existe
if [ ! -f "config/config.php" ]; then
    echo "✗ Error: No se encontró el archivo config/config.php"
    echo "  Copia config/config.example.php a config/config.php y completa las credenciales"
    exit 1
fi

# Ejecutar script PHP
echo "→ Ejecutando instalación..."
php database/install.php

exit_code=$?

if [ $exit_code -eq 0 ]; then
    echo ""
    echo "✓ Instalación completada exitosamente"
else
    echo ""
    echo "✗ La instalación falló con código de error: $exit_code"
    exit $exit_code
fi

