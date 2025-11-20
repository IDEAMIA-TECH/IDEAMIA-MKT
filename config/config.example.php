<?php
// config/config.example.php
// Copiar este archivo a config.php y completar con tus credenciales

return [
    // Aplicación
    'APP_NAME' => 'IDEAMIA Marketing Platform',
    'APP_ENV' => 'development', // development, staging, production
    'APP_DEBUG' => true,
    'APP_URL' => 'http://localhost:8000', // Cambiar a tu URL de producción
    'APP_TIMEZONE' => 'America/Mexico_City',

    // Base de Datos
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => 3306,
    'DB_NAME' => 'ideamia_mkt',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_CHARSET' => 'utf8mb4',

    // Sesiones
    'SESSION_NAME' => 'IDEAMIA_SESSION',
    'SESSION_LIFETIME' => 7200, // 2 horas
    'SESSION_SECURE' => false, // Cambiar a true en producción con HTTPS
    'SESSION_HTTPONLY' => true,

    // Storage
    'UPLOAD_DIR' => __DIR__ . '/../uploads/',
    'MAX_UPLOAD_SIZE' => 10485760, // 10 MB
    'MAX_VIDEO_SIZE' => 104857600, // 100 MB
    'ALLOWED_IMAGE_TYPES' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'ALLOWED_VIDEO_TYPES' => ['video/mp4', 'video/mov', 'video/avi'],

    // AWS S3 (Opcional)
    'USE_S3' => false,
    'AWS_ACCESS_KEY_ID' => '',
    'AWS_SECRET_ACCESS_KEY' => '',
    'AWS_REGION' => 'us-east-1',
    'AWS_BUCKET' => 'ideamia-media',

    // Meta API
    'META_APP_ID' => 'YOUR_META_APP_ID',
    'META_APP_SECRET' => 'YOUR_META_APP_SECRET',
    'META_REDIRECT_URI' => 'http://localhost:8000/api/social-accounts-callback.php', // Cambiar a tu URL de producción
    'META_API_VERSION' => 'v18.0',

    // Email
    'MAIL_HOST' => 'smtp.mailtrap.io',
    'MAIL_PORT' => 2525,
    'MAIL_USERNAME' => 'your_mailtrap_username',
    'MAIL_PASSWORD' => 'your_mailtrap_password',
    'MAIL_FROM_EMAIL' => 'noreply@ideamia.com',
    'MAIL_FROM_NAME' => 'IDEAMIA Platform',
    'MAIL_ENCRYPTION' => 'tls',

    // Seguridad
    'ENCRYPTION_KEY' => 'YOUR_32_CHARACTER_ENCRYPTION_KEY_HERE', // Generar con bin2hex(random_bytes(16))
    'JWT_SECRET' => 'YOUR_JWT_SECRET_KEY', // Usar si se implementa JWT
    'CSRF_TOKEN_NAME' => 'csrf_token',

    // Límites
    'POSTS_PER_DAY_LIMIT' => 50,
    'RATE_LIMIT_REQUESTS' => 100,
    'RATE_LIMIT_WINDOW' => 60, // segundos

    // Paths
    'BASE_PATH' => __DIR__ . '/../',
    'LOG_PATH' => __DIR__ . '/../logs/',
    'CACHE_PATH' => __DIR__ . '/../cache/',
];

