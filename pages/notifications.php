<?php
/**
 * Página de Notificaciones
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/url-helper.php';
require_once __DIR__ . '/../src/models/Notification.php';

$db = new Database();
$notification = new Notification($db);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - IDEAMIA Marketing Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem;
            border-radius: 5px;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            padding: 2rem;
        }
        .notification-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
            transition: all 0.2s;
        }
        .notification-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .notification-item.unread {
            border-left-color: #28a745;
            background: #f8fff9;
        }
        .notification-item.read {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <div class="mb-4">
                    <h4><i class="bi bi-graph-up-arrow"></i> IDEAMIA</h4>
                    <small class="text-white-50">Marketing Platform</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?php echo url('pages/dashboard.php'); ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?php echo url('pages/clients.php'); ?>">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    <a class="nav-link" href="<?php echo url('pages/posts.php'); ?>">
                        <i class="bi bi-calendar-event"></i> Publicaciones
                    </a>
                    <a class="nav-link" href="<?php echo url('pages/campaigns.php'); ?>">
                        <i class="bi bi-megaphone"></i> Campañas
                    </a>
                    <a class="nav-link" href="<?php echo url('pages/reports.php'); ?>">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                    <a class="nav-link" href="<?php echo url('pages/media-library.php'); ?>">
                        <i class="bi bi-images"></i> Biblioteca
                    </a>
                    
                    <hr class="text-white-50">
                    
                    <div class="mt-auto">
                        <div class="px-3 py-2">
                            <small class="text-white-50"><?php echo htmlspecialchars($currentUser['name']); ?></small><br>
                            <small class="text-white-50"><?php echo htmlspecialchars($currentUser['email']); ?></small>
                        </div>
                        <a class="nav-link" href="#" onclick="logout()">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </div>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="bi bi-bell"></i> Notificaciones</h1>
                    <button class="btn btn-primary" onclick="markAllRead()">
                        <i class="bi bi-check-all"></i> Marcar todas como leídas
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="filter" id="filterAll" checked onclick="loadNotifications('all')">
                            <label class="btn btn-outline-primary" for="filterAll">Todas</label>
                            
                            <input type="radio" class="btn-check" name="filter" id="filterUnread" onclick="loadNotifications('unread')">
                            <label class="btn btn-outline-primary" for="filterUnread">No leídas</label>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Notificaciones -->
                <div id="notificationsContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../includes/js-config.php'; "?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFilter = 'all';
        
        // Cargar notificaciones
        async function loadNotifications(filter = 'all') {
            currentFilter = filter;
            const unreadOnly = filter === 'unread';
            
            const container = document.getElementById('notificationsContainer');
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
            
            try {
                const response = await fetch(apiUrl('notifications.php?action=list&unread_only=${unreadOnly}&limit=100'));
                const result = await response.json();
                
                if (result.success) {
                    displayNotifications(result.data);
                } else {
                    container.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="alert alert-danger">Error al cargar notificaciones</div>';
            }
        }
        
        // Mostrar notificaciones
        function displayNotifications(notifications) {
            const container = document.getElementById('notificationsContainer');
            
            if (!notifications || notifications.length === 0) {
                container.innerHTML = '<div class="alert alert-info text-center">No hay notificaciones</div>';
                return;
            }
            
            let html = '';
            
            notifications.forEach(notif => {
                const isRead = notif.read_at !== null;
                const date = new Date(notif.created_at).toLocaleString('es-MX');
                
                const typeIcons = {
                    'post_upcoming': 'bi-calendar-event',
                    'post_failed': 'bi-exclamation-triangle',
                    'post_published': 'bi-check-circle',
                    'token_expiring': 'bi-key',
                    'campaign_low_performance': 'bi-graph-down',
                    'report_ready': 'bi-file-earmark-pdf'
                };
                
                const typeColors = {
                    'post_upcoming': 'warning',
                    'post_failed': 'danger',
                    'post_published': 'success',
                    'token_expiring': 'warning',
                    'campaign_low_performance': 'danger',
                    'report_ready': 'info'
                };
                
                const icon = typeIcons[notif.type] || 'bi-bell';
                const color = typeColors[notif.type] || 'primary';
                
                html += `
                    <div class="notification-item ${isRead ? 'read' : 'unread'}" onclick="markAsRead(${notif.id})">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi ${icon} text-${color} me-2"></i>
                                    <h6 class="mb-0 ${isRead ? '' : 'fw-bold'}">${escapeHtml(notif.title)}</h6>
                                    ${!isRead ? '<span class="badge bg-success ms-2">Nuevo</span>' : ''}
                                </div>
                                <p class="mb-2">${escapeHtml(notif.message)}</p>
                                <small class="text-muted">${date}</small>
                            </div>
                            <div class="ms-3">
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(${notif.id}, event)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        // Marcar como leída
        async function markAsRead(id) {
            try {
                const response = await fetch(apiUrl('notifications.php'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'mark_read', id: id})
                });
                
                const result = await response.json();
                if (result.success) {
                    loadNotifications(currentFilter);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        // Marcar todas como leídas
        async function markAllRead() {
            if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
                return;
            }
            
            try {
                const response = await fetch(apiUrl('notifications.php'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'mark_all_read'})
                });
                
                const result = await response.json();
                if (result.success) {
                    loadNotifications(currentFilter);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        // Eliminar notificación
        async function deleteNotification(id, event) {
            event.stopPropagation();
            
            if (!confirm('¿Eliminar esta notificación?')) {
                return;
            }
            
            try {
                const response = await fetch(apiUrl('notifications.php'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'delete', id: id})
                });
                
                const result = await response.json();
                if (result.success) {
                    loadNotifications(currentFilter);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        async function logout() {
            if (confirm('¿Estás seguro de cerrar sesión?')) {
                try {
                    const response = await fetch(apiUrl('auth.php'), {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({action: 'logout'})
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = appUrl('index.php');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.href = appUrl('index.php');
                }
            }
        }
        
        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications('all');
            
            // Actualizar cada 30 segundos
            setInterval(() => loadNotifications(currentFilter), 30000);
        });
    </script>
</body>
</html>

