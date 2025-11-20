<?php
/**
 * Script de Instalación de Base de Datos (Versión Remota)
 * IDEAMIA Marketing Platform
 * 
 * Esta versión permite pasar credenciales como parámetros o variables de entorno
 * Útil cuando config.php no está disponible
 * 
 * Uso: 
 *   php database/install-remote.php
 *   php database/install-remote.php --host=173.231.22.109 --user=ideamiadev_mkt --pass=password --db=ideamiadev_mkt
 */

// Colores para terminal
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$blue = "\033[34m";
$reset = "\033[0m";

echo "\n{$blue}========================================{$reset}\n";
echo "{$blue}  IDEAMIA Marketing Platform{$reset}\n";
echo "{$blue}  Instalación de Base de Datos (Remota){$reset}\n";
echo "{$blue}========================================{$reset}\n\n";

// Obtener credenciales de parámetros de línea de comandos o variables de entorno
$config = [
    'DB_HOST' => getenv('DB_HOST') ?: '173.231.22.109',
    'DB_PORT' => getenv('DB_PORT') ?: 3306,
    'DB_NAME' => getenv('DB_NAME') ?: 'ideamiadev_mkt',
    'DB_USER' => getenv('DB_USER') ?: 'ideamiadev_mkt',
    'DB_PASS' => getenv('DB_PASS') ?: 'oYN&hC8RMH@GzjdB',
    'DB_CHARSET' => getenv('DB_CHARSET') ?: 'utf8mb4',
];

// Parsear argumentos de línea de comandos
if ($argc > 1) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--host=') === 0) {
            $config['DB_HOST'] = substr($arg, 7);
        } elseif (strpos($arg, '--port=') === 0) {
            $config['DB_PORT'] = (int)substr($arg, 7);
        } elseif (strpos($arg, '--user=') === 0) {
            $config['DB_USER'] = substr($arg, 7);
        } elseif (strpos($arg, '--pass=') === 0) {
            $config['DB_PASS'] = substr($arg, 7);
        } elseif (strpos($arg, '--db=') === 0) {
            $config['DB_NAME'] = substr($arg, 5);
        }
    }
}

// Verificar que el archivo schema.sql existe
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    echo "{$red}✗ Error: No se encontró el archivo schema.sql{$reset}\n";
    echo "   Ruta esperada: {$schemaFile}\n";
    exit(1);
}

// Conectar a la base de datos
try {
    echo "{$yellow}→ Conectando a la base de datos...{$reset}\n";
    echo "   Host: {$config['DB_HOST']}\n";
    echo "   Base de datos: {$config['DB_NAME']}\n";
    echo "   Usuario: {$config['DB_USER']}\n\n";
    
    $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};charset={$config['DB_CHARSET']}";
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "{$green}✓ Conexión exitosa{$reset}\n\n";
    
    // Crear base de datos si no existe
    echo "{$yellow}→ Verificando/Creando base de datos '{$config['DB_NAME']}'...{$reset}\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['DB_NAME']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$config['DB_NAME']}`");
    echo "{$green}✓ Base de datos lista{$reset}\n\n";
    
    // Leer archivo schema.sql
    echo "{$yellow}→ Leyendo archivo schema.sql...{$reset}\n";
    $sql = file_get_contents($schemaFile);
    
    if (empty($sql)) {
        throw new Exception("El archivo schema.sql está vacío");
    }
    
    echo "{$green}✓ Archivo leído correctamente{$reset}\n\n";
    
    // Remover la línea CREATE DATABASE y USE si existen (ya las manejamos arriba)
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
    
    // Dividir en sentencias individuales
    // Remover comentarios de una línea que empiezan con --
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Dividir por punto y coma, pero respetando los que están dentro de strings
    $statements = [];
    $currentStatement = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        $currentStatement .= $char;
        
        if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar) {
                $inString = false;
            }
        }
        
        if (!$inString && $char === ';') {
            $statement = trim($currentStatement);
            if (!empty($statement)) {
                $statements[] = $statement;
            }
            $currentStatement = '';
        }
    }
    
    // Si queda algo sin punto y coma, agregarlo
    $remaining = trim($currentStatement);
    if (!empty($remaining)) {
        $statements[] = $remaining;
    }
    
    echo "{$yellow}→ Ejecutando sentencias SQL...{$reset}\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        // Saltar sentencias vacías
        if (empty($statement) || strlen($statement) < 10) {
            continue;
        }
        
        // Detectar tipo de sentencia
        $statementType = '';
        if (preg_match('/CREATE TABLE/i', $statement)) {
            preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches);
            $statementType = $matches[1] ?? 'tabla';
            echo "  {$yellow}→ Creando tabla: {$statementType}...{$reset}";
        } elseif (preg_match('/INSERT INTO/i', $statement)) {
            preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
            $statementType = "Insertando en: " . ($matches[1] ?? 'tabla');
            echo "  {$yellow}→ {$statementType}...{$reset}";
        } elseif (preg_match('/CREATE INDEX/i', $statement)) {
            $statementType = "Creando índice";
            echo "  {$yellow}→ Creando índice...{$reset}";
        } else {
            $statementType = "Ejecutando sentencia";
            echo "  {$yellow}→ Ejecutando sentencia...{$reset}";
        }
        
        try {
            $pdo->exec($statement);
            echo " {$green}✓{$reset}\n";
            $successCount++;
        } catch (PDOException $e) {
            // Si es un error de "tabla ya existe", lo consideramos éxito
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo " {$yellow}⚠ (ya existe){$reset}\n";
                $successCount++;
            } else {
                echo " {$red}✗{$reset}\n";
                $errorCount++;
                $errors[] = [
                    'statement' => substr($statement, 0, 100) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }
    }
    
    echo "\n{$blue}========================================{$reset}\n";
    echo "{$blue}  Resumen de Instalación{$reset}\n";
    echo "{$blue}========================================{$reset}\n";
    echo "{$green}✓ Sentencias exitosas: {$successCount}{$reset}\n";
    
    if ($errorCount > 0) {
        echo "{$red}✗ Errores: {$errorCount}{$reset}\n\n";
        echo "{$yellow}Detalles de errores:{$reset}\n";
        foreach ($errors as $error) {
            echo "  {$red}✗{$reset} {$error['error']}\n";
            echo "     Sentencia: {$error['statement']}\n\n";
        }
    } else {
        echo "{$green}✓ Errores: 0{$reset}\n";
    }
    
    // Verificar tablas creadas
    echo "\n{$yellow}→ Verificando tablas creadas...{$reset}\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "{$green}✓ Tablas en la base de datos:{$reset}\n";
        foreach ($tables as $table) {
            echo "   - {$green}{$table}{$reset}\n";
        }
    } else {
        echo "{$yellow}⚠ No se encontraron tablas{$reset}\n";
    }
    
    echo "\n{$green}========================================{$reset}\n";
    echo "{$green}  ¡Instalación completada!{$reset}\n";
    echo "{$green}========================================{$reset}\n\n";
    
    // Mostrar credenciales de usuario admin
    echo "{$blue}Credenciales de acceso por defecto:{$reset}\n";
    echo "  Email: {$yellow}admin@ideamia.com{$reset}\n";
    echo "  Contraseña: {$yellow}admin123{$reset}\n";
    echo "  {$red}⚠ IMPORTANTE: Cambiar la contraseña después del primer acceso{$reset}\n\n";
    
} catch (PDOException $e) {
    echo "\n{$red}✗ Error de conexión a la base de datos:{$reset}\n";
    echo "  {$red}{$e->getMessage()}{$reset}\n\n";
    echo "Verifica las credenciales:\n";
    echo "  Host: {$config['DB_HOST']}\n";
    echo "  Usuario: {$config['DB_USER']}\n";
    echo "  Base de datos: {$config['DB_NAME']}\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n{$red}✗ Error: {$e->getMessage()}{$reset}\n";
    exit(1);
}

