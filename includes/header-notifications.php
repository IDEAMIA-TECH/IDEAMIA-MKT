<?php
/**
 * Componente de Notificaciones para Header
 * Incluir en páginas que necesiten mostrar notificaciones
 */

require_once __DIR__ . '/../src/models/Notification.php';

$db = new Database();
$notification = new Notification($db);
$unreadCount = $notification->getUnreadCount($currentUser['id']);
$recentNotifications = $notification->list($currentUser['id'], ['limit' => 5]);
?>
<!-- Notificaciones Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge">
                <?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?>
            </span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="width: 350px; max-height: 400px; overflow-y: auto;" id="notificationsList">
        <li><h6 class="dropdown-header">Notificaciones</h6></li>
        <li><hr class="dropdown-divider"></li>
        <div id="notificationsContent">
            <?php if (empty($recentNotifications)): ?>
                <li><span class="dropdown-item-text text-muted">No hay notificaciones</span></li>
            <?php else: ?>
                <?php foreach ($recentNotifications as $notif): ?>
                    <li>
                        <a class="dropdown-item <?php echo $notif['read_at'] ? '' : 'fw-bold'; ?>" href="#" onclick="markNotificationRead(<?php echo $notif['id']; ?>)">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($notif['title']); ?></div>
                                    <div class="small"><?php echo htmlspecialchars($notif['message']); ?></div>
                                    <div class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></div>
                                </div>
                                <?php if (!$notif['read_at']): ?>
                                    <span class="badge bg-primary">Nuevo</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <li>
            <div class="dropdown-item-text">
                <button class="btn btn-sm btn-outline-primary w-100" onclick="markAllNotificationsRead()">
                    Marcar todas como leídas
                </button>
            </div>
        </li>
        <li>
            <div class="dropdown-item-text">
                <a href="/pages/notifications.php" class="btn btn-sm btn-link w-100">Ver todas</a>
            </div>
        </li>
    </ul>
</li>

<script>
// Actualizar notificaciones cada 30 segundos
setInterval(updateNotifications, 30000);

async function updateNotifications() {
    try {
        // Actualizar contador
        const countResponse = await fetch('/api/notifications.php?action=unread_count');
        const countResult = await countResponse.json();
        
        if (countResult.success) {
            const badge = document.getElementById('notificationBadge');
            const count = countResult.data.count;
            
            if (count > 0) {
                if (!badge) {
                    const icon = document.querySelector('#notificationsDropdown i');
                    const newBadge = document.createElement('span');
                    newBadge.id = 'notificationBadge';
                    newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    newBadge.textContent = count > 99 ? '99+' : count;
                    icon.parentElement.appendChild(newBadge);
                } else {
                    badge.textContent = count > 99 ? '99+' : count;
                }
            } else if (badge) {
                badge.remove();
            }
        }
        
        // Actualizar lista si el dropdown está abierto
        const dropdown = document.getElementById('notificationsDropdown');
        if (dropdown && dropdown.getAttribute('aria-expanded') === 'true') {
            loadNotificationsList();
        }
    } catch (error) {
        console.error('Error al actualizar notificaciones:', error);
    }
}

async function loadNotificationsList() {
    try {
        const response = await fetch('/api/notifications.php?action=list&limit=5');
        const result = await response.json();
        
        if (result.success) {
            const container = document.getElementById('notificationsContent');
            let html = '';
            
            if (result.data.length === 0) {
                html = '<li><span class="dropdown-item-text text-muted">No hay notificaciones</span></li>';
            } else {
                result.data.forEach(notif => {
                    const isRead = notif.read_at !== null;
                    const date = new Date(notif.created_at).toLocaleString('es-MX');
                    
                    html += `
                        <li>
                            <a class="dropdown-item ${isRead ? '' : 'fw-bold'}" href="#" onclick="markNotificationRead(${notif.id})">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small text-muted">${escapeHtml(notif.title)}</div>
                                        <div class="small">${escapeHtml(notif.message)}</div>
                                        <div class="small text-muted">${date}</div>
                                    </div>
                                    ${!isRead ? '<span class="badge bg-primary">Nuevo</span>' : ''}
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    `;
                });
            }
            
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
    }
}

async function markNotificationRead(id) {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'mark_read', id: id})
        });
        
        const result = await response.json();
        if (result.success) {
            updateNotifications();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function markAllNotificationsRead() {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'mark_all_read'})
        });
        
        const result = await response.json();
        if (result.success) {
            updateNotifications();
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
</script>

