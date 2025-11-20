# Documento de Inicio de Desarrollo
## Plataforma de Gestión de Pautas y Contenidos para Agencia de Marketing

---

## Tabla de Contenidos

1. [Objetivo General](#1-objetivo-general)
2. [Perfiles de Usuario](#2-perfiles-de-usuario)
3. [Módulos Principales](#3-módulos-principales)
4. [Arquitectura y Modelos de Datos](#4-arquitectura-y-modelos-de-datos)
5. [Requerimientos Técnicos](#5-requerimientos-técnicos)
6. [APIs y Endpoints](#6-apis-y-endpoints)
7. [Roadmap y Fases](#7-roadmap-y-fases)
8. [Métricas Clave](#8-métricas-clave)
9. [Casos de Uso Principales](#9-casos-de-uso-principales)
10. [Configuración y Variables de Entorno](#10-configuración-y-variables-de-entorno)
11. [Testing y Calidad](#11-testing-y-calidad)
12. [Próximos Pasos](#12-próximos-pasos)
13. [Estructura del Proyecto PHP](#13-estructura-del-proyecto-php)

---

## 1. Objetivo General

Desarrollar una plataforma web (tipo dashboard) para una agencia de marketing que permita:

- ✅ Administrar múltiples **clientes** y sus **cuentas de redes sociales**
- ✅ **Planear y agendar** publicaciones en Facebook, Instagram (y otras redes en fases posteriores)
- ✅ Crear, administrar y monitorear **campañas de anuncios pagados** (Facebook Ads / Instagram Ads)
- ✅ Generar **reportes claros y métricas clave** para cada cliente
- ✅ Centralizar la operación diaria de la agencia en un solo sistema

### Alcance del Proyecto

- **Tipo:** SaaS interno para agencia de marketing
- **Usuarios objetivo:** Administradores, Community Managers, Clientes
- **Plataformas:** Web App (responsive)
- **Integraciones:** Meta (Facebook/Instagram), futuras: TikTok, LinkedIn, YouTube

---

## 2. Perfiles de Usuario

### 2.1. Administrador de la Agencia

**Permisos:**
- ✅ Alta, edición y baja de clientes
- ✅ Alta de usuarios internos (community managers, analistas, etc.)
- ✅ Configuración general del sistema (branding, logo, colores, etc.)
- ✅ Acceso a todos los reportes y métricas
- ✅ Gestión de permisos y roles
- ✅ Configuración de integraciones con APIs externas

**Casos de uso principales:**
- Crear nuevo cliente y asignar usuarios
- Configurar integraciones de redes sociales
- Ver dashboard general de todos los clientes
- Exportar reportes consolidados

---

### 2.2. Community Manager / Operador

**Permisos:**
- ✅ Gestionar calendario de contenidos por cliente asignado
- ✅ Proponer, crear y agendar publicaciones
- ✅ Ver desempeño de campañas y contenidos (lectura de métricas)
- ✅ Cargar recursos (imágenes, videos, copys)
- ✅ Editar publicaciones en estado borrador/pendiente
- ❌ No puede eliminar clientes ni modificar configuraciones del sistema

**Casos de uso principales:**
- Crear y programar publicación en calendario
- Subir imágenes/videos a biblioteca de recursos
- Ver métricas de publicaciones publicadas
- Duplicar publicaciones exitosas

---

### 2.3. Cliente (Vista Externa / Portal Cliente)

**Permisos:**
- ✅ Ver su propio **panel de métricas**
- ✅ Ver el **calendario de publicaciones** aprobadas y publicadas
- ✅ Descargar reportes en PDF/Excel
- ✅ Aprobar / rechazar propuestas de contenido (Fase 2)
- ❌ No puede crear ni editar publicaciones directamente
- ❌ No puede ver información de otros clientes

**Casos de uso principales:**
- Revisar reporte mensual de métricas
- Ver calendario de publicaciones programadas
- Descargar reporte en PDF para presentación interna
- Aprobar contenido propuesto por el CM (Fase 2)

---

## 3. Módulos Principales

### 3.1. Módulo de Gestión de Clientes

**Objetivo:** Centralizar los datos y configuración de cada cliente.

#### Datos Básicos por Cliente

| Campo | Tipo | Descripción | Requerido |
|-------|------|-------------|-----------|
| Nombre comercial | String | Nombre público del cliente | ✅ |
| Razón social | String | Nombre legal/empresa | ✅ |
| Contacto principal | String | Nombre de la persona de contacto | ✅ |
| Email contacto | Email | Correo del contacto | ✅ |
| Teléfono | String | Teléfono de contacto | ⚠️ |
| WhatsApp | String | Número de WhatsApp | ⚠️ |
| Sector/Industria | String | Categoría del negocio | ⚠️ |
| Presupuesto mensual | Decimal | Presupuesto estimado en anuncios | ⚠️ |
| Observaciones | Text | Notas internas | ❌ |
| Estado | Enum | Activo, Inactivo, Suspendido | ✅ |
| Fecha creación | DateTime | Fecha de alta | ✅ |

#### Funcionalidades v1

- ✅ CRUD completo de clientes
- ✅ Listado con buscador y filtros (nombre, sector, estado)
- ✅ Vista detalle de cliente con resumen:
  - Redes conectadas y estado
  - Campañas activas
  - Próximas publicaciones (próximos 7 días)
  - Presupuesto consumido / disponible
  - Métricas resumidas del mes actual

#### Funcionalidades v2

- Exportar listado de clientes a Excel
- Historial de cambios en datos del cliente
- Asignación de múltiples contactos por cliente
- Segmentación de clientes por tags/categorías

---

### 3.2. Integración con Redes Sociales

**Objetivo:** Conectar cuentas de clientes para publicaciones y métricas.

#### Redes en Fase 1

- ✅ **Facebook Pages** (via Meta Graph API)
- ✅ **Instagram Business** (via Meta Graph API)

#### Funcionalidades v1

- ✅ Conexión mediante OAuth 2.0 a Meta (Facebook/Instagram)
- ✅ Almacenamiento seguro de tokens de acceso (encriptados)
- ✅ Listado de páginas/cuentas conectadas por cliente
- ✅ Verificación de estado de conexión (activo / expirado / error)
- ✅ Refresh automático de tokens cuando sea posible
- ✅ Notificación cuando token esté próximo a expirar

#### Flujo de Conexión

1. Usuario inicia conexión desde panel del cliente
2. Redirección a OAuth de Meta
3. Usuario autoriza permisos necesarios
4. Callback con código de autorización
5. Intercambio por access token y refresh token
6. Almacenamiento seguro en base de datos
7. Verificación de permisos obtenidos

#### Permisos Requeridos (Meta API)

- `pages_read_engagement` - Leer métricas de páginas
- `pages_manage_posts` - Publicar en páginas
- `pages_read_user_content` - Leer contenido
- `instagram_basic` - Acceso básico a Instagram
- `instagram_content_publish` - Publicar en Instagram
- `ads_read` - Leer datos de anuncios (Fase 2)

#### Funcionalidades v2

- Integración con TikTok Business API
- Integración con LinkedIn Company Pages
- Integración con YouTube Channel
- Re-autenticación automática o recordatorios de expiración
- Dashboard de salud de conexiones

---

### 3.3. Calendario de Contenidos (Content Planner)

**Objetivo:** Planear, organizar y visualizar las publicaciones de cada cliente.

#### Vistas de Calendario

| Vista | Descripción | Filtros Disponibles |
|-------|-------------|---------------------|
| Mensual | Vista de mes completo | Cliente, Red social, Estado |
| Semanal | Vista de semana (7 días) | Cliente, Red social, Estado |
| Diaria | Vista de día específico | Cliente, Red social, Estado |
| Lista | Vista de lista con detalles | Cliente, Red social, Estado, Rango fechas |

#### Estados de Publicación

| Estado | Descripción | Acciones Permitidas |
|--------|-------------|---------------------|
| `draft` | Borrador | Editar, Eliminar, Programar |
| `pending_approval` | Pendiente de aprobación | Aprobar, Rechazar, Editar (Fase 2) |
| `approved` | Aprobado | Programar, Editar, Eliminar |
| `scheduled` | Programado | Ver, Cancelar, Editar (con restricciones) |
| `published` | Publicado | Ver, Ver métricas, Duplicar |
| `rejected` | Rechazado | Ver, Editar, Eliminar |
| `failed` | Error en publicación | Ver error, Reintentar, Editar |

#### Información de Cada Post

| Campo | Tipo | Descripción | Requerido |
|-------|------|-------------|-----------|
| Cliente | Relation | Cliente asociado | ✅ |
| Red social | Enum | Facebook, Instagram, Ambas | ✅ |
| Fecha programada | DateTime | Fecha y hora de publicación | ✅ |
| Copy/Texto | Text | Contenido del post | ✅ |
| Imágenes | Media[] | Array de imágenes | ⚠️ |
| Video | Media | Video (si aplica) | ❌ |
| URL referencia | URL | Link a landing/tienda | ❌ |
| Responsable | Relation | Usuario que creó el post | ✅ |
| Etiquetas | String[] | Tags internos | ❌ |
| Campaña asociada | Relation | Campaña de ads (opcional) | ❌ |
| Estado | Enum | Ver estados arriba | ✅ |

#### Funcionalidades v1

- ✅ CRUD completo de publicaciones
- ✅ Agendar publicaciones (fecha/hora exacta con timezone)
- ✅ Sincronización automática con API de Meta para publicación
- ✅ Duplicar / clonar post a otra fecha
- ✅ Etiquetas internas (campaña, objetivo, tipo de contenido)
- ✅ Vista previa del post antes de publicar
- ✅ Validación de límites de caracteres por red social
- ✅ Sistema de colas para publicaciones programadas

#### Funcionalidades v2

- Flujo de aprobación con el cliente (Aceptar/Rechazar con comentarios)
- Plantillas de contenido reutilizables
- Comentarios internos en cada post
- Programación masiva de posts
- Análisis de mejor hora para publicar (basado en histórico)
- Sugerencias de contenido basadas en posts exitosos

---

### 3.4. Módulo de Anuncios Pagados (Facebook e Instagram Ads)

**Objetivo:** Configurar, monitorear y reportar campañas de anuncios.

#### Configuración Básica de Campaña

| Campo | Tipo | Descripción |
|-------|------|-------------|
| Cliente | Relation | Cliente propietario |
| Cuenta publicitaria | Relation | Ad Account de Meta |
| Nombre campaña | String | Nombre descriptivo |
| Objetivo | Enum | Reach, Traffic, Leads, Conversion, etc. |
| Presupuesto diario | Decimal | Presupuesto por día |
| Presupuesto total | Decimal | Presupuesto total de campaña |
| Fecha inicio | Date | Inicio de la campaña |
| Fecha fin | Date | Fin de la campaña |
| Estado | Enum | Activa, Pausada, Finalizada |

#### Funcionalidades v1

- ✅ Lectura de campañas existentes desde Meta Ads API
- ✅ Sincronización periódica de métricas (cada hora)
- ✅ Mostrar métricas principales por campaña:
  - Impresiones
  - Alcance (Reach)
  - Clics
  - CTR (Click Through Rate)
  - CPC (Costo por Clic)
  - CPM (Costo por Mil Impresiones)
  - Conversiones (si la API lo permite)
  - Gasto total y gasto diario
- ✅ Filtros por periodo (hoy, 7 días, 30 días, rango personalizado)
- ✅ Dashboard de rendimiento de campañas
- ✅ Alertas de campañas con bajo rendimiento

#### Funcionalidades v2

- Crear y editar campañas directamente desde el sistema
- Pausar/activar campañas desde la plataforma
- Presupuestos recomendados según resultados históricos
- A/B testing de creativos
- Optimización automática de presupuestos

---

### 3.5. Reportes y Métricas

**Objetivo:** Entregar valor al cliente mediante métricas claras y entendibles.

#### Tipos de Reportes

##### 1. Reporte de Redes Sociales (Orgánico)

**Métricas incluidas:**
- Posts publicados en el periodo
- Engagement total:
  - Likes / Reacciones
  - Comentarios
  - Compartidos
  - Alcance (Reach)
  - Guardados (Instagram)
- Crecimiento de seguidores:
  - Seguidores iniciales vs finales
  - Tasa de crecimiento
- Mejores publicaciones (Top 3–5 por engagement)
- Gráficos de tendencia de engagement
- Horarios de mayor engagement

##### 2. Reporte de Anuncios Pagados

**Métricas incluidas:**
- Gasto total del periodo
- Impresiones y alcance
- Clics y CTR
- Costo por clic (CPC)
- Conversiones (si aplica)
- Costo por resultado (CPA, CPL, etc.)
- ROAS (Return on Ad Spend) si hay datos de conversión
- Comparativa de campañas
- Gráficos de evolución de gasto y resultados

##### 3. Resumen Ejecutivo por Cliente

**Tarjeta resumen con:**
- Gasto total en anuncios del mes
- Crecimiento de seguidores (número y porcentaje)
- Número de publicaciones orgánicas
- Mejor post del mes (por engagement)
- Mejor campaña del mes (por ROI/resultados)
- Comparativa mes anterior (MoM)

#### Funcionalidades v1

- ✅ Panel por cliente con gráficos y tarjetas de métricas clave
- ✅ Filtros por rango de fechas
- ✅ Exportar reportes a PDF
- ✅ Enviar reporte por correo al cliente (manual)
- ✅ Dashboard en tiempo real (últimas 24-48 horas)
- ✅ Gráficos interactivos (Chart.js, Recharts, o similar)

#### Funcionalidades v2

- Programar envío automático de reportes mensuales
- Exportar reportes a Excel / CSV
- Comparativas mes vs mes (MoM) y año vs año (YoY)
- Reportes personalizados por cliente
- White-label de reportes (logo del cliente)

---

### 3.6. Módulo de Notificaciones

**Objetivo:** Mantener al equipo informado sobre eventos importantes.

#### Eventos a Notificar

| Evento | Prioridad | Canal | Destinatario |
|--------|-----------|-------|--------------|
| Post próximo a publicarse (15 min antes) | Media | In-app, Email | CM responsable |
| Fallo en publicación programada | Alta | In-app, Email | CM responsable, Admin |
| Token de red social próximo a expirar (7 días) | Media | In-app, Email | Admin, CM |
| Campaña con bajo rendimiento | Media | In-app | CM, Admin |
| Nuevo reporte listo | Baja | In-app | Cliente, Admin |
| Publicación exitosa | Baja | In-app | CM responsable |
| Error en sincronización de métricas | Alta | In-app, Email | Admin |

#### Canales de Notificación

- ✅ **In-app:** Notificaciones dentro del sistema (centro de notificaciones)
- ⚠️ **Email:** Correo electrónico (Fase 2)
- ❌ **WhatsApp / Telegram:** Futuro, opcional

#### Funcionalidades v1

- Sistema de notificaciones en tiempo real
- Centro de notificaciones con historial
- Marcar como leída/no leída
- Filtros por tipo de notificación

---

### 3.7. Biblioteca de Recursos (Media Library)

**Objetivo:** Organizar imágenes, videos y assets por cliente.

#### Tipos de Archivos Soportados

| Tipo | Formatos | Tamaño Máximo | Descripción |
|------|----------|---------------|-------------|
| Imágenes | JPG, PNG, GIF, WebP | 10 MB | Fotos, gráficos, memes |
| Videos | MP4, MOV, AVI | 100 MB | Videos cortos para posts |
| Documentos | PDF | 5 MB | Guías, documentos de referencia |

#### Funcionalidades v1

- ✅ Cargar archivos por cliente
- ✅ Clasificar por carpetas / etiquetas (campaña, temporada, producto)
- ✅ Reutilizar imágenes en distintos posts
- ✅ Búsqueda por nombre/etiquetas
- ✅ Vista previa de imágenes
- ✅ Compresión automática de imágenes grandes
- ✅ Almacenamiento en cloud (S3, Cloudinary, o similar)

#### Funcionalidades v2

- Editor básico de imágenes (crop, resize, filtros)
- Biblioteca compartida entre clientes (opcional)
- Integración con stock photos (Unsplash, Pexels)
- Análisis de uso de recursos (qué imágenes tienen mejor rendimiento)

---

### 3.8. Configuración y Seguridad

#### Configuraciones Generales

| Configuración | Descripción | Acceso |
|---------------|-------------|--------|
| Branding | Logo de la agencia, colores, nombre | Admin |
| Zona horaria | Zona horaria por defecto | Admin |
| Idioma | ES (con posibilidad de EN futuro) | Admin |
| Permisos | Manejo de permisos por rol | Admin |
| Integraciones | Configuración de APIs externas | Admin |
| Límites | Límites de uso (posts/día, storage) | Admin |

#### Seguridad

**Autenticación:**
- ✅ Autenticación con usuario y contraseña (v1)
- ✅ Recuperación de contraseña por correo
- ✅ Contraseñas encriptadas (bcrypt/argon2)
- ✅ Sesiones seguras con tokens
- ⚠️ Autenticación de dos factores (2FA) - Fase 2

**Autorización:**
- ✅ Sistema de roles y permisos (RBAC)
- ✅ Middleware de autenticación en todas las rutas protegidas
- ✅ Validación de permisos por recurso

**Auditoría:**
- ⚠️ Registro de actividad (log básico): quién creó/edita/elimina posts y campañas (Fase 2)
- ⚠️ Historial de cambios en datos sensibles (Fase 2)

**Protección de Datos:**
- ✅ Tokens de API encriptados en base de datos
- ✅ HTTPS obligatorio
- ✅ Validación y sanitización de inputs
- ✅ Protección CSRF
- ✅ Rate limiting en APIs

---

## 4. Arquitectura y Modelos de Datos

### 4.1. Arquitectura General

```
┌─────────────────────────────────────┐
│         Frontend (Browser)          │
│  HTML + CSS + JavaScript (Vanilla)  │
│         AJAX (Fetch/XMLHttpRequest) │
│      Bootstrap/Tailwind CSS         │
│         Chart.js / ApexCharts        │
└──────────────┬──────────────────────┘
               │ HTTP Requests (AJAX)
               │ Form Submissions
┌──────────────▼──────────────────────┐
│         Backend PHP                 │
│    PHP 8.0+ (Vanilla o Framework)   │
│    Sessions / JWT Authentication    │
│    RESTful Endpoints (PHP)          │
└──────────────┬──────────────────────┘
               │
    ┌──────────┴──────────┬──────────────┬──────────────┐
    │                     │              │              │
┌───▼────┐         ┌──────▼─────┐  ┌─────▼─────┐  ┌────▼────┐
│ MySQL  │         │   Cron     │  │  File     │  │ Storage │
│  8.0+  │         │   Jobs     │  │  System   │  │  (S3)   │
│        │         │  (PHP CLI) │  │  (Local)  │  │         │
└────────┘         └────────────┘  └───────────┘  └─────────┘
    │
    └──────────┬──────────────────────────────────┐
                │                                  │
         ┌──────▼──────┐                    ┌─────▼────┐
         │   Meta      │                    │  Email  │
         │   API       │                    │  SMTP   │
         │ (cURL/Guzzle)│                   │ (PHPMailer)│
         └─────────────┘                    └─────────┘
```

**Flujo de Comunicación:**
- **Frontend:** HTML estático con JavaScript que hace peticiones AJAX a endpoints PHP
- **Backend:** PHP procesa requests, consulta MySQL, y retorna JSON/HTML
- **Background Jobs:** Scripts PHP ejecutados vía cron para tareas programadas

### 4.2. Modelos de Datos Principales

#### Entidades Core

```
User (Usuarios)
├── id
├── name
├── email
├── password (hashed)
├── role (admin, cm, client)
├── client_id (nullable, si es cliente)
├── created_at
└── updated_at

Client (Clientes)
├── id
├── business_name
├── legal_name
├── contact_name
├── contact_email
├── contact_phone
├── contact_whatsapp
├── sector
├── monthly_budget
├── notes
├── status (active, inactive, suspended)
├── created_at
└── updated_at

SocialAccount (Cuentas de Redes Sociales)
├── id
├── client_id
├── platform (facebook, instagram)
├── account_id (ID en la plataforma)
├── account_name
├── access_token (encrypted)
├── refresh_token (encrypted)
├── token_expires_at
├── status (connected, expired, error)
├── permissions (JSON)
├── created_at
└── updated_at

Post (Publicaciones)
├── id
├── client_id
├── social_account_id
├── created_by (user_id)
├── platform (facebook, instagram, both)
├── scheduled_at
├── published_at (nullable)
├── content (text)
├── media_urls (JSON array)
├── link_url (nullable)
├── status (draft, pending, approved, scheduled, published, rejected, failed)
├── error_message (nullable)
├── tags (JSON array)
├── campaign_id (nullable)
├── created_at
└── updated_at

Campaign (Campañas de Ads)
├── id
├── client_id
├── social_account_id
├── ad_account_id (Meta Ad Account ID)
├── name
├── objective
├── daily_budget
├── total_budget
├── start_date
├── end_date
├── status (active, paused, completed)
├── meta_campaign_id (ID en Meta)
├── created_at
└── updated_at

CampaignMetrics (Métricas de Campañas)
├── id
├── campaign_id
├── date
├── impressions
├── reach
├── clicks
├── ctr
├── cpc
├── cpm
├── spend
├── conversions (nullable)
├── created_at
└── updated_at

PostMetrics (Métricas de Posts)
├── id
├── post_id
├── date
├── likes
├── comments
├── shares
├── reach
├── saves (Instagram)
├── created_at
└── updated_at

Media (Biblioteca de Recursos)
├── id
├── client_id
├── uploaded_by (user_id)
├── filename
├── original_filename
├── file_path
├── file_type (image, video, document)
├── file_size
├── mime_type
├── folder (nullable)
├── tags (JSON array)
├── created_at
└── updated_at

Notification (Notificaciones)
├── id
├── user_id
├── type
├── title
├── message
├── data (JSON)
├── read_at (nullable)
├── created_at
└── updated_at

Report (Reportes)
├── id
├── client_id
├── generated_by (user_id)
├── type (organic, ads, executive)
├── period_start
├── period_end
├── file_path (PDF/Excel)
├── sent_at (nullable)
├── created_at
└── updated_at
```

#### Relaciones Principales

- `User` → `Client` (many-to-one, opcional)
- `Client` → `SocialAccount` (one-to-many)
- `Client` → `Post` (one-to-many)
- `Client` → `Campaign` (one-to-many)
- `Client` → `Media` (one-to-many)
- `SocialAccount` → `Post` (one-to-many)
- `Post` → `PostMetrics` (one-to-many)
- `Campaign` → `CampaignMetrics` (one-to-many)
- `User` → `Notification` (one-to-many)

---

## 5. Requerimientos Técnicos

### 5.1. Stack Tecnológico

#### Backend
- **Lenguaje:** PHP 8.0+ (recomendado PHP 8.1 o superior)
- **Arquitectura:** PHP Vanilla (orientado a objetos) o Framework ligero (opcional)
- **Base de Datos:** MySQL 8.0+ / MariaDB 10.6+
- **Conexión BD:** PDO (PHP Data Objects) o MySQLi
- **HTTP Client:** cURL nativo o Guzzle HTTP (para APIs externas)
- **Autenticación:** Sesiones PHP o JWT (JSON Web Tokens)
- **Storage:** Sistema de archivos local o AWS S3 / DigitalOcean Spaces
- **Email:** PHPMailer o función mail() nativa de PHP

#### Frontend
- **HTML:** HTML5 semántico
- **CSS:** CSS3 + Framework CSS (Bootstrap 5+ o Tailwind CSS 3+)
- **JavaScript:** JavaScript ES6+ (Vanilla, sin frameworks)
- **AJAX:** Fetch API o XMLHttpRequest
- **Librerías JavaScript:**
  - **Charts:** Chart.js o ApexCharts
  - **Calendar:** FullCalendar.js
  - **Date Picker:** Flatpickr o similar
  - **Icons:** Font Awesome o Bootstrap Icons
  - **Notifications:** Toastr.js o SweetAlert2
- **Build Tools:** (Opcional) Webpack o Vite para minificación

#### DevOps e Infraestructura
- **Servidor:** Ubuntu 22.04 LTS o similar
- **Web Server:** Apache 2.4+ con mod_rewrite o Nginx
- **PHP-FPM:** PHP 8.0+ con extensiones:
  - PDO / MySQLi
  - cURL
  - JSON
  - OpenSSL
  - GD (para procesamiento de imágenes)
  - mbstring
- **Cron Jobs:** Para tareas programadas (publicaciones, sincronización)
- **SSL:** Let's Encrypt / Certbot (HTTPS obligatorio)
- **Versionado:** Git (GitHub, GitLab, Bitbucket)
- **Monitoring:** Logs de PHP y Apache/Nginx

### 5.2. Integraciones Externas

#### Meta (Facebook/Instagram)
- **API:** Meta Graph API v18+
- **Autenticación:** OAuth 2.0
- **Endpoints principales:**
  - Publicar en páginas: `POST /{page-id}/feed`
  - Publicar en Instagram: `POST /{ig-user-id}/media`
  - Obtener métricas: `GET /{page-id}/insights`
  - Obtener posts: `GET /{page-id}/posts`

#### Meta Marketing API (Ads)
- **API:** Meta Marketing API v18+
- **Endpoints principales:**
  - Listar campañas: `GET /{ad-account-id}/campaigns`
  - Obtener métricas: `GET /{campaign-id}/insights`

#### Email
- **Servicio:** SendGrid / Mailgun / AWS SES
- **Uso:** Notificaciones, reportes, recuperación de contraseña

### 5.3. Requisitos de Rendimiento

- **Tiempo de carga inicial:** < 3 segundos
- **Tiempo de respuesta API:** < 500ms (p95)
- **Sincronización de métricas:** Cada hora (background job)
- **Publicaciones programadas:** Ejecución en tiempo real (± 1 minuto)
- **Concurrencia:** Soporte para 50+ usuarios simultáneos

### 5.4. Requisitos de Seguridad

- ✅ HTTPS obligatorio (TLS 1.2+)
- ✅ Contraseñas encriptadas (bcrypt/argon2)
- ✅ Tokens de API encriptados en BD
- ✅ Validación y sanitización de inputs
- ✅ Protección CSRF
- ✅ Rate limiting (100 req/min por usuario)
- ✅ Headers de seguridad (CSP, X-Frame-Options, etc.)

---

## 6. APIs y Endpoints

### 6.1. Estructura de URLs

**Patrón de URLs:**
- **Páginas:** `/dashboard.php`, `/clients.php`, `/posts.php`
- **Endpoints AJAX:** `/api/auth.php`, `/api/clients.php`, `/api/posts.php`
- **Acciones:** Los endpoints PHP procesan diferentes acciones mediante parámetros `action` o rutas

### 6.2. Endpoints de Autenticación

**Archivo:** `api/auth.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| POST | `login` | `email`, `password` | JSON: `{success, user, token}` |
| POST | `logout` | `token` (session) | JSON: `{success}` |
| POST | `check_session` | `token` (session) | JSON: `{valid, user}` |
| POST | `forgot_password` | `email` | JSON: `{success, message}` |
| POST | `reset_password` | `token`, `password` | JSON: `{success}` |

**Ejemplo de uso AJAX:**
```javascript
fetch('/api/auth.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'login', email: '...', password: '...'})
})
```

### 6.3. Endpoints de Clientes

**Archivo:** `api/clients.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `page`, `search`, `filter` | JSON: `{clients[], total, page}` |
| POST | `create` | `business_name`, `legal_name`, ... | JSON: `{success, client}` |
| GET | `get` | `id` | JSON: `{client}` |
| POST | `update` | `id`, `data...` | JSON: `{success, client}` |
| POST | `delete` | `id` | JSON: `{success}` |
| GET | `summary` | `id` | JSON: `{summary}` |
| GET | `social_accounts` | `id` | JSON: `{accounts[]}` |

### 6.4. Endpoints de Redes Sociales

**Archivo:** `api/social-accounts.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `client_id` | JSON: `{accounts[]}` |
| GET | `connect` | `client_id`, `platform` | Redirect a OAuth |
| GET | `callback` | `code`, `state` | Procesa OAuth y redirige |
| GET | `get` | `id` | JSON: `{account}` |
| POST | `delete` | `id` | JSON: `{success}` |
| POST | `refresh_token` | `id` | JSON: `{success, account}` |
| GET | `status` | `id` | JSON: `{status, expires_at}` |

### 6.5. Endpoints de Publicaciones

**Archivo:** `api/posts.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `client_id`, `date_from`, `date_to`, `status` | JSON: `{posts[]}` |
| POST | `create` | `client_id`, `content`, `scheduled_at`, ... | JSON: `{success, post}` |
| GET | `get` | `id` | JSON: `{post}` |
| POST | `update` | `id`, `data...` | JSON: `{success, post}` |
| POST | `delete` | `id` | JSON: `{success}` |
| POST | `duplicate` | `id`, `new_date` | JSON: `{success, post}` |
| POST | `publish_now` | `id` | JSON: `{success}` |
| GET | `calendar` | `client_id`, `view`, `date` | JSON: `{events[]}` |
| GET | `metrics` | `id` | JSON: `{metrics[]}` |

### 6.6. Endpoints de Campañas

**Archivo:** `api/campaigns.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `client_id`, `status` | JSON: `{campaigns[]}` |
| POST | `create` | `client_id`, `name`, `objective`, ... | JSON: `{success, campaign}` |
| GET | `get` | `id` | JSON: `{campaign}` |
| POST | `update` | `id`, `data...` | JSON: `{success, campaign}` |
| POST | `delete` | `id` | JSON: `{success}` |
| GET | `metrics` | `id`, `date_from`, `date_to` | JSON: `{metrics[]}` |
| POST | `sync` | `id` | JSON: `{success}` |
| POST | `pause` | `id` | JSON: `{success}` |
| POST | `resume` | `id` | JSON: `{success}` |

### 6.7. Endpoints de Reportes

**Archivo:** `api/reports.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `client_id` | JSON: `{reports[]}` |
| POST | `generate` | `client_id`, `type`, `period_start`, `period_end` | JSON: `{success, report_id}` |
| GET | `get` | `id` | JSON: `{report}` |
| GET | `download` | `id` | PDF/Excel file |
| POST | `send_email` | `id`, `email` | JSON: `{success}` |
| GET | `metrics` | `client_id`, `date_from`, `date_to` | JSON: `{metrics}` |

### 6.8. Endpoints de Media

**Archivo:** `api/media.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `client_id`, `folder`, `search` | JSON: `{media[]}` |
| POST | `upload` | `client_id`, `file` (multipart/form-data) | JSON: `{success, media}` |
| GET | `get` | `id` | JSON: `{media}` |
| POST | `delete` | `id` | JSON: `{success}` |
| GET | `search` | `client_id`, `query`, `tags` | JSON: `{media[]}` |

### 6.9. Endpoints de Notificaciones

**Archivo:** `api/notifications.php`

| Método | Acción | Parámetros | Respuesta |
|--------|--------|------------|-----------|
| GET | `list` | `unread_only` | JSON: `{notifications[]}` |
| GET | `unread_count` | - | JSON: `{count}` |
| POST | `mark_read` | `id` | JSON: `{success}` |
| POST | `mark_all_read` | - | JSON: `{success}` |

### 6.10. Formato de Respuestas

**Respuesta exitosa:**
```json
{
    "success": true,
    "data": {...},
    "message": "Operación exitosa"
}
```

**Respuesta de error:**
```json
{
    "success": false,
    "error": "Mensaje de error",
    "code": "ERROR_CODE"
}
```

---

## 7. Roadmap y Fases

### Fase 1 – MVP (8-12 semanas)

**Objetivo:** Plataforma funcional básica para gestión de contenidos orgánicos.

#### Sprint 1-2: Setup y Autenticación (2 semanas)
- [ ] Setup del proyecto (backend + frontend)
- [ ] Configuración de base de datos
- [ ] Sistema de autenticación y autorización
- [ ] CRUD de usuarios y roles
- [ ] Dashboard básico

#### Sprint 3-4: Gestión de Clientes (2 semanas)
- [ ] CRUD completo de clientes
- [ ] Listado con búsqueda y filtros
- [ ] Vista detalle de cliente
- [ ] Integración con redes sociales (OAuth Meta)
- [ ] Gestión de tokens y refresh

#### Sprint 5-6: Calendario de Contenidos (3 semanas)
- [ ] CRUD de publicaciones
- [ ] Vista de calendario (mensual, semanal, diaria)
- [ ] Programación de publicaciones
- [ ] Sistema de colas para publicaciones
- [ ] Integración con Meta API para publicar
- [ ] Estados de publicación

#### Sprint 7-8: Métricas Básicas (2 semanas)
- [ ] Sincronización de métricas desde Meta API
- [ ] Dashboard de métricas por cliente
- [ ] Gráficos básicos de engagement
- [ ] Vista de métricas por post

#### Sprint 9: Biblioteca de Recursos (1 semana)
- [ ] Upload de archivos
- [ ] Almacenamiento en cloud
- [ ] Gestión de carpetas y etiquetas
- [ ] Integración con posts

#### Sprint 10: Reportes Básicos (1 semana)
- [ ] Generación de reportes en PDF
- [ ] Panel de métricas consolidadas
- [ ] Exportación básica

**Entregables Fase 1:**
- ✅ Sistema de autenticación funcional
- ✅ Gestión completa de clientes
- ✅ Calendario de contenidos con publicación automática
- ✅ Métricas básicas de posts orgánicos
- ✅ Reportes en PDF

---

### Fase 2 (6-8 semanas)

**Objetivo:** Integración con Ads y portal de cliente.

#### Sprint 11-12: Integración con Ads (3 semanas)
- [ ] Conexión con Meta Marketing API
- [ ] Sincronización de campañas existentes
- [ ] Dashboard de métricas de campañas
- [ ] Alertas de rendimiento

#### Sprint 13: Portal de Cliente (2 semanas)
- [ ] Autenticación para clientes
- [ ] Dashboard de métricas para cliente
- [ ] Vista de calendario (solo lectura)
- [ ] Descarga de reportes

#### Sprint 14: Mejoras de Reportes (2 semanas)
- [ ] Exportación a Excel/CSV
- [ ] Envío de reportes por email
- [ ] Reportes más detallados
- [ ] Comparativas MoM

#### Sprint 15: Biblioteca y Notificaciones (1 semana)
- [ ] Mejoras en biblioteca de recursos
- [ ] Sistema de notificaciones in-app
- [ ] Notificaciones por email

**Entregables Fase 2:**
- ✅ Integración completa con Meta Ads
- ✅ Portal de cliente funcional
- ✅ Reportes mejorados con exportación
- ✅ Sistema de notificaciones

---

### Fase 3 (8-10 semanas)

**Objetivo:** Funcionalidades avanzadas y nuevas integraciones.

#### Sprint 16-17: Gestión de Campañas (3 semanas)
- [ ] Crear campañas desde la plataforma
- [ ] Editar campañas
- [ ] Pausar/activar campañas
- [ ] Optimización de presupuestos

#### Sprint 18: Flujo de Aprobación (2 semanas)
- [ ] Sistema de aprobación de contenidos
- [ ] Notificaciones a clientes
- [ ] Comentarios en posts
- [ ] Historial de aprobaciones

#### Sprint 19: Automatizaciones (2 semanas)
- [ ] Envío automático de reportes mensuales
- [ ] Programación de reportes
- [ ] Alertas automáticas personalizadas

#### Sprint 20: Nuevas Integraciones (3 semanas)
- [ ] Integración con TikTok
- [ ] Integración con LinkedIn
- [ ] Integración con YouTube (opcional)

**Entregables Fase 3:**
- ✅ Gestión completa de campañas desde la plataforma
- ✅ Flujo de aprobación con clientes
- ✅ Automatizaciones
- ✅ Integraciones con nuevas redes

---

## 8. Métricas Clave

### 8.1. Métricas por Cliente (MVP)

| Métrica | Descripción | Fuente |
|---------|-------------|--------|
| Publicaciones totales | Número de posts publicados en el periodo | Post.published_at |
| Alcance total | Suma de alcance de todos los posts | PostMetrics.reach |
| Engagement total | Suma de likes + comentarios + compartidos | PostMetrics |
| Tasa de engagement | (Engagement / Alcance) × 100 | Calculado |
| Crecimiento de seguidores | Seguidores finales - seguidores iniciales | Meta API |
| Mejor post | Post con mayor engagement | PostMetrics |
| Gasto en anuncios | Suma de gasto de campañas activas | CampaignMetrics.spend |
| Alcance de ads | Alcance total de campañas | CampaignMetrics.reach |
| Clics en ads | Total de clics en campañas | CampaignMetrics.clicks |
| CTR promedio | (Clics / Impresiones) × 100 | Calculado |

### 8.2. KPIs del Sistema

- **Uptime:** > 99.5%
- **Tiempo de respuesta API:** < 500ms (p95)
- **Tasa de éxito de publicaciones:** > 98%
- **Sincronización de métricas:** Cada hora sin fallos
- **Satisfacción del usuario:** Encuesta post-MVP

---

## 9. Casos de Uso Principales

### UC-1: Crear y Programar Publicación

**Actor:** Community Manager

**Flujo:**
1. CM accede al calendario de contenidos
2. Selecciona cliente y fecha/hora
3. Crea nueva publicación:
   - Escribe copy
   - Sube imágenes/videos
   - Selecciona redes sociales
   - Agrega URL si aplica
4. Guarda como borrador o programa directamente
5. Sistema valida contenido (límites de caracteres, formato)
6. Si programa: publicación se agrega a cola
7. En fecha/hora programada: sistema publica automáticamente

**Casos alternativos:**
- Error en publicación: sistema notifica y permite reintentar
- Token expirado: sistema solicita reconexión

---

### UC-2: Conectar Red Social de Cliente

**Actor:** Administrador

**Flujo:**
1. Admin accede a perfil del cliente
2. Hace clic en "Conectar Red Social"
3. Selecciona plataforma (Facebook/Instagram)
4. Sistema redirige a OAuth de Meta
5. Usuario autoriza permisos en Meta
6. Meta redirige de vuelta con código
7. Sistema intercambia código por tokens
8. Sistema almacena tokens encriptados
9. Sistema verifica conexión y muestra estado

**Casos alternativos:**
- Usuario cancela autorización: vuelve sin conectar
- Error en OAuth: sistema muestra mensaje de error

---

### UC-3: Ver Reporte de Métricas

**Actor:** Cliente

**Flujo:**
1. Cliente accede a su portal
2. Selecciona rango de fechas
3. Sistema muestra dashboard con:
   - Métricas resumidas
   - Gráficos de engagement
   - Mejores publicaciones
   - Crecimiento de seguidores
4. Cliente puede descargar reporte en PDF
5. Sistema genera PDF y permite descarga

---

### UC-4: Sincronizar Métricas de Campaña

**Actor:** Sistema (Background Job)

**Flujo:**
1. Job se ejecuta cada hora (cron)
2. Para cada campaña activa:
   - Consulta Meta Marketing API
   - Obtiene métricas del día anterior
   - Almacena en CampaignMetrics
3. Si hay error: registra en log y notifica a admin
4. Si token expirado: marca campaña como "error" y notifica

---

### UC-5: Aprobar Contenido (Fase 2)

**Actor:** Cliente

**Flujo:**
1. CM crea publicación y marca como "pendiente de aprobación"
2. Sistema notifica al cliente
3. Cliente accede a su portal
4. Ve publicación pendiente con preview
5. Cliente aprueba o rechaza
6. Si aprueba: publicación pasa a "aprobado" y puede programarse
7. Si rechaza: publicación pasa a "rechazado" y CM puede editarla
8. Sistema notifica al CM del resultado

---

## 10. Configuración y Variables de Entorno

### 10.1. Archivo de Configuración PHP

**Archivo:** `config/config.php` o `.env` (parseado con PHP)

```php
<?php
// config/config.php

return [
    // Aplicación
    'APP_NAME' => 'IDEAMIA Marketing Platform',
    'APP_ENV' => 'production', // development, staging, production
    'APP_DEBUG' => false,
    'APP_URL' => 'https://platform.ideamia.com',
    'APP_TIMEZONE' => 'America/Mexico_City',
    
    // Base de Datos
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => 3306,
    'DB_NAME' => 'ideamia_mkt',
    'DB_USER' => 'ideamia_user',
    'DB_PASS' => 'secure_password',
    'DB_CHARSET' => 'utf8mb4',
    
    // Sesiones
    'SESSION_NAME' => 'IDEAMIA_SESSION',
    'SESSION_LIFETIME' => 7200, // 2 horas
    'SESSION_SECURE' => true, // Solo HTTPS
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
    'META_APP_ID' => 'your_app_id',
    'META_APP_SECRET' => 'your_app_secret',
    'META_REDIRECT_URI' => 'https://platform.ideamia.com/api/social-accounts/callback.php',
    'META_API_VERSION' => 'v18.0',
    
    // Email
    'MAIL_HOST' => 'smtp.sendgrid.net',
    'MAIL_PORT' => 587,
    'MAIL_USERNAME' => 'apikey',
    'MAIL_PASSWORD' => 'your_sendgrid_api_key',
    'MAIL_FROM_EMAIL' => 'noreply@ideamia.com',
    'MAIL_FROM_NAME' => 'IDEAMIA Platform',
    'MAIL_ENCRYPTION' => 'tls',
    
    // Seguridad
    'ENCRYPTION_KEY' => 'your_32_character_encryption_key_here',
    'JWT_SECRET' => 'your_jwt_secret_key',
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
```

**Uso en código PHP:**
```php
<?php
require_once __DIR__ . '/config/config.php';
$config = require __DIR__ . '/config/config.php';

// Acceso a configuración
$dbHost = $config['DB_HOST'];
$dbName = $config['DB_NAME'];
```

### 10.2. Configuración de Meta App

**Permisos requeridos en Meta App:**
- `pages_read_engagement`
- `pages_manage_posts`
- `pages_read_user_content`
- `instagram_basic`
- `instagram_content_publish`
- `ads_read` (Fase 2)
- `ads_management` (Fase 3)

**Configuración de OAuth:**
- Valid OAuth Redirect URIs: `https://platform.ideamia.com/api/social-accounts/callback`
- App Domains: `ideamia.com`

---

## 11. Testing y Calidad

### 11.1. Estrategia de Testing

#### Unit Tests (PHP)
- **Cobertura objetivo:** > 70%
- **Áreas a cubrir:**
  - Clases de modelo (User, Client, Post, etc.)
  - Clases de servicio (AuthService, PostService, etc.)
  - Helpers y utilidades (validación, encriptación, etc.)
  - Funciones de negocio críticas

#### Integration Tests
- **Endpoints PHP:** Todos los endpoints críticos (`api/*.php`)
- **Flujos completos:**
  - Autenticación y sesiones
  - CRUD de clientes
  - Creación de publicación y publicación automática
  - Sincronización de métricas desde Meta API
  - Generación de reportes PDF
  - Upload de archivos

#### E2E Tests (Fase 2)
- **Flujos principales:**
  - Login y navegación
  - Crear y programar publicación desde UI
  - Conectar red social (flujo OAuth)
  - Ver reporte y descargar PDF
  - Subir archivo a biblioteca

### 11.2. Herramientas de Testing

#### Backend (PHP)
- **PHPUnit:** Framework de testing para PHP
  - Instalación: `composer require --dev phpunit/phpunit`
  - Configuración: `phpunit.xml`
- **Mockery:** Para mocks y stubs (opcional)
- **PHPStan / Psalm:** Análisis estático de código

#### Frontend (JavaScript)
- **Jest:** Framework de testing para JavaScript
  - Testing de funciones JavaScript
  - Testing de módulos AJAX
- **Manual Testing:** Pruebas manuales en navegadores (Chrome, Firefox, Safari)
- **Browser DevTools:** Para debugging

#### API Testing
- **Postman / Insomnia:** Colección de tests para endpoints
- **cURL:** Scripts de prueba desde terminal
- **PHP cURL:** Tests automatizados de integración

#### E2E Testing (Opcional)
- **Selenium WebDriver:** Automatización de navegador
- **Playwright:** Alternativa moderna a Selenium
- **Manual Testing:** Pruebas manuales exhaustivas

### 11.3. Code Quality

#### PHP
- **PHP CS Fixer:** Formateo y estándares de código
  - PSR-12 coding standard
- **PHPStan:** Análisis estático (nivel 5+ recomendado)
- **PHPMD:** Detección de problemas en código
- **Code Review:** Obligatorio antes de merge

#### JavaScript
- **ESLint:** Linting de código JavaScript
  - Configuración estándar o personalizada
- **Prettier:** Formateo automático (opcional)
- **JSHint:** Alternativa a ESLint

#### CSS
- **Stylelint:** Linting de CSS
- **Validación W3C:** Validación de HTML/CSS

### 11.4. Estructura de Tests

```
/tests
  /unit
    UserTest.php
    ClientTest.php
    PostServiceTest.php
  /integration
    AuthEndpointTest.php
    ClientsEndpointTest.php
    PostsEndpointTest.php
  /fixtures
    users.json
    clients.json
```

### 11.5. Ejemplo de Test PHPUnit

```php
<?php
// tests/unit/UserTest.php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../src/models/User.php';

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@example.com');
        
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
    }
    
    public function testPasswordHashing()
    {
        $user = new User();
        $password = 'password123';
        $hashed = $user->hashPassword($password);
        
        $this->assertNotEquals($password, $hashed);
        $this->assertTrue(password_verify($password, $hashed));
    }
}
```

---

## 12. Próximos Pasos

### Inmediatos (Pre-desarrollo)

1. ✅ **Validar documento** con stakeholders (alcance y prioridades)
2. ✅ **Stack tecnológico definido:** PHP, CSS, JavaScript, AJAX, MySQL
3. ⚠️ **Diseñar wireframes** del dashboard principal, calendario y reportes
4. ⚠️ **Configurar repositorio Git** y estructura del proyecto
5. ⚠️ **Setup de entorno de desarrollo:**
   - Servidor local (XAMPP, WAMP, MAMP o servidor Linux)
   - PHP 8.0+ con extensiones necesarias
   - MySQL 8.0+
   - Editor/IDE (VS Code, PHPStorm, etc.)

### Corto Plazo (Sprint 0)

1. ⚠️ **Crear Meta App** en Facebook Developers y obtener credenciales
2. ⚠️ **Configurar base de datos MySQL:**
   - Crear base de datos `ideamia_mkt`
   - Crear script de migración inicial (SQL)
   - Definir estructura de tablas
3. ⚠️ **Crear estructura de carpetas del proyecto:**
   ```
   /project-root
     /api          - Endpoints PHP
     /assets       - CSS, JS, imágenes
     /config       - Archivos de configuración
     /includes     - Headers, footers, helpers
     /src          - Clases PHP (models, services)
     /uploads      - Archivos subidos
     /logs         - Logs del sistema
     /tests        - Tests PHPUnit
   ```
4. ⚠️ **Configurar archivo de configuración** (`config/config.php`)
5. ⚠️ **Setup de librerías JavaScript:**
   - Chart.js o ApexCharts
   - FullCalendar.js
   - Bootstrap 5 o Tailwind CSS
   - Font Awesome
6. ⚠️ **Configurar servicios externos** (SendGrid para email, S3 opcional)

### Mediano Plazo

1. ⚠️ **Estimar tiempos** de desarrollo por fase (MVP primero)
2. ⚠️ **Preparar propuesta económica** y plan de implementación
3. ⚠️ **Definir proceso de deployment** y ambientes (dev, staging, prod)
4. ⚠️ **Establecer metodología** de trabajo (Scrum, Kanban, etc.)

---

## 13. Estructura del Proyecto PHP

### 13.1. Estructura de Carpetas Recomendada

```
/project-root
  /api                    # Endpoints PHP para AJAX
    auth.php
    clients.php
    posts.php
    campaigns.php
    reports.php
    media.php
    notifications.php
    social-accounts.php
  
  /assets                 # Recursos estáticos
    /css
      main.css
      dashboard.css
      calendar.css
    /js
      main.js
      ajax.js
      calendar.js
      charts.js
    /images
      logo.png
      icons/
    /libs                 # Librerías JavaScript
      bootstrap.min.js
      chart.min.js
      fullcalendar.min.js
      jquery.min.js (si se usa)
  
  /config                 # Configuración
    config.php
    database.php
    .env (opcional)
  
  /includes               # Archivos reutilizables
    header.php
    footer.php
    sidebar.php
    functions.php
    auth-check.php
  
  /src                    # Clases PHP (POO)
    /models
      User.php
      Client.php
      Post.php
      Campaign.php
      SocialAccount.php
      Media.php
    /services
      AuthService.php
      PostService.php
      CampaignService.php
      MetaAPIService.php
      ReportService.php
    /helpers
      Database.php
      Validator.php
      Encryptor.php
      Logger.php
      Mailer.php
  
  /pages                  # Páginas principales
    index.php (login)
    dashboard.php
    clients.php
    clients-detail.php
    posts.php
    posts-calendar.php
    campaigns.php
    reports.php
    media-library.php
    settings.php
  
  /uploads                 # Archivos subidos
    /clients
      /{client_id}
        /images
        /videos
        /documents
  
  /logs                    # Logs del sistema
    error.log
    access.log
    api.log
  
  /cache                   # Cache (opcional)
  
  /tests                   # Tests PHPUnit
    /unit
    /integration
    /fixtures
    phpunit.xml
  
  /vendor                  # Dependencias (si se usa Composer)
  
  .htaccess                # Configuración Apache
  index.php                # Punto de entrada principal
  composer.json            # Dependencias PHP (opcional)
  README.md
```

### 13.2. Ejemplo de Estructura de Clase PHP

```php
<?php
// src/models/Client.php

class Client {
    private $db;
    private $id;
    private $businessName;
    private $legalName;
    // ... otros atributos
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($data) {
        // Validar datos
        // Insertar en BD
        // Retornar resultado
    }
    
    public function findById($id) {
        // Consultar BD
        // Retornar objeto Client
    }
    
    public function update($id, $data) {
        // Validar y actualizar
    }
    
    public function delete($id) {
        // Eliminar (soft delete recomendado)
    }
}
```

### 13.3. Ejemplo de Endpoint PHP

```php
<?php
// api/clients.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../src/models/Client.php';
require_once __DIR__ . '/../src/helpers/Database.php';

header('Content-Type: application/json');

$db = new Database();
$client = new Client($db);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $clients = $client->list($_GET);
            echo json_encode(['success' => true, 'data' => $clients]);
            break;
            
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $client->create($data);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            $clientData = $client->findById($id);
            echo json_encode(['success' => true, 'data' => $clientData]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

### 13.4. Ejemplo de JavaScript AJAX

```javascript
// assets/js/clients.js

class ClientsAPI {
    static async list(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`/api/clients.php?action=list&${params}`);
        return await response.json();
    }
    
    static async create(data) {
        const response = await fetch('/api/clients.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'create', ...data})
        });
        return await response.json();
    }
    
    static async get(id) {
        const response = await fetch(`/api/clients.php?action=get&id=${id}`);
        return await response.json();
    }
}

// Uso
const clients = await ClientsAPI.list({page: 1, search: 'test'});
```

### 13.5. Configuración Apache (.htaccess)

```apache
# .htaccess

# Habilitar rewrite engine
RewriteEngine On

# Redirigir a HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos sensibles
<FilesMatch "^(config|\.env)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Configuración PHP
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value memory_limit 256M

# Headers de seguridad
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

## Anexos

### A. Glosario de Términos

- **CM:** Community Manager
- **OAuth:** Protocolo de autorización estándar
- **API:** Application Programming Interface
- **CRUD:** Create, Read, Update, Delete
- **MVP:** Minimum Viable Product
- **MoM:** Month over Month (comparativa mes a mes)
- **YoY:** Year over Year (comparativa año a año)
- **ROAS:** Return on Ad Spend
- **CPC:** Cost Per Click
- **CPM:** Cost Per Mille (mil impresiones)
- **CTR:** Click Through Rate
- **CPA:** Cost Per Acquisition
- **CPL:** Cost Per Lead

### B. Referencias de APIs

- [Meta Graph API Documentation](https://developers.facebook.com/docs/graph-api)
- [Meta Marketing API Documentation](https://developers.facebook.com/docs/marketing-apis)
- [Instagram Graph API](https://developers.facebook.com/docs/instagram-api)

### C. Notas de Desarrollo

- **Prioridad de desarrollo:** Seguir orden de fases (MVP primero)
- **Comunicación:** Documentar decisiones técnicas importantes
- **Versionado:** Usar Semantic Versioning (v1.0.0)
- **Documentación:** Mantener README actualizado con instrucciones de setup

---

**Última actualización:** 2024
**Versión del documento:** 2.0
**Estado:** Listo para inicio de desarrollo
