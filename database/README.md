# Instalación de Base de Datos

Este directorio contiene los archivos necesarios para crear la estructura de la base de datos.

## Archivos

- `schema.sql` - Esquema completo de la base de datos con todas las tablas
- `install.php` - Script PHP para instalar automáticamente las tablas
- `install.sh` - Script shell para ejecutar la instalación (Linux/Mac)

## Métodos de Instalación

### Método 1: Script Automático (Recomendado)

#### Opción A: Usando PHP directamente
```bash
php database/install.php
```

#### Opción B: Usando script shell
```bash
./database/install.sh
```

El script:
- Se conecta a la base de datos usando las credenciales de `config/config.php`
- Crea la base de datos si no existe
- Ejecuta todas las sentencias SQL del `schema.sql`
- Muestra el progreso y resumen de la instalación
- Verifica las tablas creadas

### Método 2: Manual (MySQL CLI)

```bash
mysql -h 173.231.22.109 -u ideamiadev_mkt -p ideamiadev_mkt < database/schema.sql
```

Cuando se solicite, ingresar la contraseña: `oYN&hC8RMH@GzjdB`

### Método 3: Desde phpMyAdmin o cliente MySQL

1. Abrir phpMyAdmin o tu cliente MySQL favorito
2. Conectarse a la base de datos `ideamiadev_mkt`
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido completo de `schema.sql`
5. Ejecutar

## Verificación

Después de la instalación, verificar que las tablas se crearon correctamente:

```sql
SHOW TABLES;
```

Deberías ver las siguientes tablas:
- users
- clients
- social_accounts
- posts
- campaigns
- campaign_metrics
- post_metrics
- media
- notifications
- reports

## Usuario Administrador por Defecto

Después de la instalación, puedes iniciar sesión con:

- **Email:** admin@ideamia.com
- **Contraseña:** admin123

⚠️ **IMPORTANTE:** Cambiar la contraseña inmediatamente después del primer acceso.

## Solución de Problemas

### Error de conexión
- Verificar que las credenciales en `config/config.php` sean correctas
- Verificar que el servidor MySQL esté accesible desde tu ubicación
- Verificar que el firewall permita conexiones al puerto 3306

### Error "Table already exists"
- Esto es normal si ejecutas el script múltiples veces
- El script usa `CREATE TABLE IF NOT EXISTS` para evitar errores
- Si necesitas recrear las tablas, primero elimínalas manualmente

### Error de permisos
- Verificar que el usuario de MySQL tenga permisos para:
  - CREATE DATABASE
  - CREATE TABLE
  - INSERT, UPDATE, DELETE, SELECT

## Notas

- El script `install.php` es seguro de ejecutar múltiples veces
- No eliminará datos existentes, solo creará tablas que no existan
- Los datos de ejemplo (usuario admin) se insertan automáticamente

