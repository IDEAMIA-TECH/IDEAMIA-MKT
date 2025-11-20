# IDEAMIA Marketing Platform

Plataforma web para gestión de pautas y contenidos para agencia de marketing.

## Stack Tecnológico

- **Backend:** PHP 8.0+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), AJAX
- **Base de Datos:** MySQL 8.0+
- **Framework CSS:** Bootstrap 5

## Requisitos

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Apache con mod_rewrite (o Nginx)
- Extensiones PHP: PDO, cURL, JSON, OpenSSL, GD, mbstring

## Instalación

### 1. Clonar repositorio

```bash
git clone <repository-url>
cd IDEAMIA-MKT
```

### 2. Configurar base de datos

**Opción A: Script automático (Recomendado)**
```bash
php database/install.php
```

**Opción B: Manual**
```bash
mysql -h 173.231.22.109 -u ideamiadev_mkt -p ideamiadev_mkt < database/schema.sql
```

Ver `database/README.md` para más detalles sobre la instalación.

### 3. Configurar aplicación

Editar `config/config.php` con tus credenciales:

```php
'DB_HOST' => '127.0.0.1',
'DB_NAME' => 'ideamia_mkt',
'DB_USER' => 'tu_usuario',
'DB_PASS' => 'tu_contraseña',
```

### 4. Configurar servidor web

**Apache:**
- Asegúrate de que mod_rewrite esté habilitado
- El archivo `.htaccess` ya está incluido

**Nginx:**
- Configurar rewrite rules según tu setup

### 5. Crear directorios necesarios

```bash
mkdir -p uploads logs cache
chmod 755 uploads logs cache
```

## Usuario por Defecto

Después de importar el esquema, puedes iniciar sesión con:

- **Email:** admin@ideamia.com
- **Contraseña:** admin123

**⚠️ IMPORTANTE:** Cambiar la contraseña en producción.

## Estructura del Proyecto

```
/project-root
  /api              - Endpoints PHP para AJAX
  /assets           - CSS, JS, imágenes
  /config           - Archivos de configuración
  /database         - Scripts SQL
  /includes         - Headers, footers, helpers
  /pages            - Páginas principales
  /src              - Clases PHP (models, services, helpers)
  /uploads          - Archivos subidos
  /logs             - Logs del sistema
```

## Desarrollo

### Módulos Implementados

- ✅ **Módulo 1: Autenticación**
  - Login/Logout
  - Sistema de sesiones
  - Protección de rutas
  - Dashboard básico

- ✅ **Módulo 2: Gestión de Clientes**
  - CRUD completo de clientes
  - Listado con búsqueda y filtros (estado, sector)
  - Paginación
  - Vista detalle de cliente con resumen
  - Formularios de crear/editar cliente
  - Operaciones AJAX

- ✅ **Módulo 3: Integración con Redes Sociales**
  - Conexión OAuth con Meta (Facebook/Instagram)
  - Almacenamiento seguro de tokens (encriptados)
  - Listado de cuentas conectadas por cliente
  - Verificación de estado de conexión
  - Refresh de tokens
  - Desconexión de cuentas
  - Detección automática de Instagram Business asociado

- ✅ **Módulo 4: Calendario de Contenidos**
  - CRUD completo de publicaciones
  - Vista de calendario (mensual, semanal, diaria) con FullCalendar
  - Programación de publicaciones con fecha/hora
  - Estados de publicación (borrador, programado, publicado, fallido)
  - Publicación inmediata
  - Validación de límites de caracteres por plataforma
  - Sistema de cron job para publicación automática
  - Integración con Meta API para publicar en Facebook/Instagram
  - Filtros por cliente, estado, plataforma
  - Duplicar publicaciones

- ✅ **Módulo 5: Campañas de Anuncios**
  - Sincronización de campañas desde Meta Marketing API
  - Listado de campañas con filtros
  - Vista detalle de campaña con métricas
  - Sincronización de métricas diarias desde Meta
  - Dashboard de rendimiento con gráficos (Chart.js)
  - Métricas: Impresiones, Alcance, Clics, CTR, CPC, CPM, Gasto, Conversiones
  - Gráficos de evolución temporal
  - Resumen agregado de métricas

- ✅ **Módulo 6: Reportes y Métricas**
  - Generación de reportes consolidados por cliente
  - Métricas orgánicas (publicaciones, engagement, alcance)
  - Métricas de anuncios (campañas, gasto, clics, conversiones)
  - Resumen ejecutivo combinado
  - Mejores publicaciones y campañas del periodo
  - Gráficos comparativos (orgánico vs ads)
  - Exportación de reportes (JSON, preparado para PDF)
  - Filtros por cliente y periodo
  - Historial de reportes generados

- ✅ **Módulo 7: Biblioteca de Recursos**
  - Upload de archivos (imágenes, videos, documentos)
  - Vista de galería con grid responsive
  - Organización por carpetas
  - Sistema de etiquetas
  - Búsqueda por nombre y etiquetas
  - Filtros por cliente, tipo de archivo, carpeta
  - Procesamiento automático de imágenes (redimensionamiento)
  - Validación de tipos y tamaños de archivo
  - Paginación
  - Eliminación de archivos

- ✅ **Módulo 8: Notificaciones**
  - Sistema completo de notificaciones
  - Notificaciones en tiempo real (polling cada 30 segundos)
  - Contador de notificaciones no leídas
  - Página de notificaciones con filtros
  - Marcar como leída / Marcar todas como leídas
  - Eliminar notificaciones
  - Tipos de notificaciones:
    - Publicación próxima (15 min antes)
    - Publicación exitosa
    - Error en publicación
    - Token próximo a expirar
    - Campaña con bajo rendimiento
    - Reporte listo
  - Cron jobs para notificaciones automáticas
  - Integración con eventos del sistema

## Estado del Proyecto

✅ **PROYECTO COMPLETO** - Todos los módulos principales implementados

## Instalación

Ver `INSTALL.md` para guía completa de instalación y configuración.

**⚠️ IMPORTANTE:** El archivo `config/config.php` contiene credenciales sensibles y está en `.gitignore`. 
Para nuevos desarrolladores, copiar `config/config.example.php` a `config/config.php` y completar con las credenciales correspondientes.

## Configuración de Cron Jobs

Para que las funcionalidades automáticas funcionen, configurar los siguientes cron jobs:

```bash
# Publicar posts programados (cada minuto)
* * * * * php /ruta/al/proyecto/cron/publish-scheduled-posts.php

# Verificar posts próximos (cada 15 minutos)
*/15 * * * * php /ruta/al/proyecto/cron/check-upcoming-posts.php

# Verificar tokens próximos a expirar (diario a las 9 AM)
0 9 * * * php /ruta/al/proyecto/cron/check-expiring-tokens.php
```

## Documentación

- **Documentación técnica:** Ver `DOCS/context.md` para documentación completa del proyecto
- **Guía de instalación:** Ver `INSTALL.md` para instrucciones detalladas de instalación

## Licencia

Propietario - IDEAMIA

