# Guía de Instalación
## IDEAMIA Marketing Platform

### Requisitos Previos

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Apache 2.4+ con mod_rewrite (o Nginx)
- Extensiones PHP:
  - PDO
  - MySQLi
  - cURL
  - JSON
  - OpenSSL
  - GD (para procesamiento de imágenes)
  - mbstring

### Paso 1: Clonar y Configurar

```bash
# Clonar repositorio
git clone <repository-url>
cd IDEAMIA-MKT

# Crear directorios necesarios
mkdir -p uploads/clients logs cache reports
chmod 755 uploads logs cache reports
```

### Paso 2: Base de Datos

```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE ideamia_mkt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar esquema
mysql -u root -p ideamia_mkt < database/schema.sql
```

### Paso 3: Configuración

Editar `config/config.php`:

```php
// Base de Datos
'DB_HOST' => '127.0.0.1',
'DB_NAME' => 'ideamia_mkt',
'DB_USER' => 'tu_usuario',
'DB_PASS' => 'tu_contraseña',

// Meta API (obtener de Facebook Developers)
'META_APP_ID' => 'tu_app_id',
'META_APP_SECRET' => 'tu_app_secret',
'META_REDIRECT_URI' => 'https://tu-dominio.com/api/social-accounts-callback.php',

// Encriptación (generar clave segura de 32 caracteres)
'ENCRYPTION_KEY' => 'clave_segura_de_32_caracteres_aqui',
```

### Paso 4: Configurar Servidor Web

#### Apache (.htaccess ya incluido)

Asegúrate de que `mod_rewrite` esté habilitado:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /ruta/al/proyecto;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Paso 5: Configurar Cron Jobs

Editar crontab:
```bash
crontab -e
```

Agregar las siguientes líneas:

```cron
# Publicar posts programados (cada minuto)
* * * * * php /ruta/completa/al/proyecto/cron/publish-scheduled-posts.php

# Verificar posts próximos (cada 15 minutos)
*/15 * * * * php /ruta/completa/al/proyecto/cron/check-upcoming-posts.php

# Verificar tokens próximos a expirar (diario a las 9 AM)
0 9 * * * php /ruta/completa/al/proyecto/cron/check-expiring-tokens.php
```

**Nota:** Reemplazar `/ruta/completa/al/proyecto` con la ruta absoluta de tu proyecto.

### Paso 6: Configurar Meta App

1. Ir a [Facebook Developers](https://developers.facebook.com/)
2. Crear una nueva App
3. Agregar productos:
   - Facebook Login
   - Instagram Basic Display
   - Marketing API
4. Configurar OAuth Redirect URIs:
   - `https://tu-dominio.com/api/social-accounts-callback.php`
5. Obtener App ID y App Secret
6. Configurar permisos requeridos

### Paso 7: Usuario Inicial

Después de importar el esquema, puedes iniciar sesión con:

- **Email:** admin@ideamia.com
- **Contraseña:** admin123

**⚠️ IMPORTANTE:** Cambiar la contraseña inmediatamente en producción.

### Paso 8: Verificar Instalación

1. Acceder a `http://tu-dominio.com`
2. Iniciar sesión con las credenciales por defecto
3. Verificar que el dashboard cargue correctamente
4. Probar crear un cliente
5. Verificar logs en `logs/` para errores

### Solución de Problemas

#### Error de conexión a BD
- Verificar credenciales en `config/config.php`
- Verificar que MySQL esté corriendo
- Verificar permisos del usuario de BD

#### Error 500
- Verificar permisos de archivos (755 para directorios, 644 para archivos)
- Revisar logs de PHP/Apache
- Verificar que todas las extensiones PHP estén instaladas

#### Error en uploads
- Verificar permisos del directorio `uploads/` (debe ser 755 o 777)
- Verificar `upload_max_filesize` y `post_max_size` en php.ini

#### Cron jobs no funcionan
- Verificar ruta absoluta en crontab
- Verificar permisos de ejecución de los scripts
- Verificar logs en `logs/`

### Seguridad en Producción

1. Cambiar `APP_DEBUG` a `false` en `config/config.php`
2. Cambiar `APP_ENV` a `production`
3. Cambiar contraseña del usuario admin
4. Generar claves seguras para `ENCRYPTION_KEY` y `JWT_SECRET`
5. Habilitar HTTPS (SSL/TLS)
6. Configurar `SESSION_SECURE` a `true` en `config/config.php`
7. Restringir acceso a directorios sensibles (config, logs, etc.)

