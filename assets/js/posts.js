/**
 * JavaScript para Calendario de Publicaciones
 * Maneja FullCalendar y operaciones AJAX
 */

let calendar;
let currentFilters = {
    client_id: '',
    status: '',
    platform: ''
};

// Inicializar calendario
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            loadCalendarEvents(fetchInfo.start, fetchInfo.end, successCallback, failureCallback);
        },
        eventClick: function(info) {
            viewPost(info.event.id);
        },
        dateClick: function(info) {
            openPostModal(info.dateStr);
        },
        eventDidMount: function(info) {
            // Tooltip con información
            info.el.setAttribute('title', info.event.extendedProps.content || '');
        }
    });
    
    calendar.render();
    
    // Contador de caracteres
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
    }
});

// Cargar eventos del calendario
async function loadCalendarEvents(start, end, successCallback, failureCallback) {
    try {
        const params = new URLSearchParams({
            action: 'calendar',
            date_from: start.toISOString().split('T')[0],
            date_to: end.toISOString().split('T')[0],
            ...currentFilters
        });
        
        const response = await fetch(`/api/posts.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            successCallback(result.data);
        } else {
            failureCallback(result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        failureCallback(error);
    }
}

// Aplicar filtros
function applyFilters() {
    currentFilters = {
        client_id: document.getElementById('clientFilter').value,
        status: document.getElementById('statusFilter').value,
        platform: document.getElementById('platformFilter').value
    };
    
    calendar.refetchEvents();
}

// Cargar lista de clientes
async function loadClients() {
    try {
        const response = await fetch('/api/clients.php?action=list&per_page=100');
        const result = await response.json();
        
        if (result.success && result.data.data) {
            const clientFilter = document.getElementById('clientFilter');
            const clientSelect = document.getElementById('clientId');
            
            result.data.data.forEach(client => {
                const option1 = document.createElement('option');
                option1.value = client.id;
                option1.textContent = client.business_name;
                clientFilter.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = client.id;
                option2.textContent = client.business_name;
                clientSelect.appendChild(option2);
            });
        }
    } catch (error) {
        console.error('Error al cargar clientes:', error);
    }
}

// Cargar cuentas de redes sociales cuando se selecciona un cliente
async function loadSocialAccounts(clientId) {
    if (!clientId) {
        document.getElementById('socialAccountId').innerHTML = '<option value="">Seleccionar cuenta (opcional)</option>';
        return;
    }
    
    try {
        const response = await fetch(`/api/social-accounts.php?action=list&client_id=${clientId}`);
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('socialAccountId');
            select.innerHTML = '<option value="">Seleccionar cuenta (opcional)</option>';
            
            result.data.forEach(account => {
                const option = document.createElement('option');
                option.value = account.id;
                option.textContent = `${account.account_name} (${account.platform})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar cuentas:', error);
    }
}

// Abrir modal para nueva publicación
function openPostModal(dateStr = null) {
    document.getElementById('modalTitle').textContent = 'Nueva Publicación';
    document.getElementById('postForm').reset();
    document.getElementById('postId').value = '';
    document.getElementById('status').value = 'draft';
    document.getElementById('publishNowBtn').style.display = 'none';
    
    if (dateStr) {
        // Formatear fecha para input datetime-local
        const date = new Date(dateStr);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        document.getElementById('scheduledAt').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    } else {
        // Fecha actual + 1 hora
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduledAt').value = now.toISOString().slice(0, 16);
    }
    
    document.getElementById('charCount').textContent = '0';
    
    // Listener para cargar cuentas cuando se selecciona cliente
    const clientSelect = document.getElementById('clientId');
    clientSelect.onchange = function() {
        loadSocialAccounts(this.value);
    };
}

// Ver publicación
async function viewPost(id) {
    try {
        const response = await fetch(`/api/posts.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const post = result.data;
            
            document.getElementById('modalTitle').textContent = 'Ver Publicación';
            document.getElementById('postId').value = post.id;
            document.getElementById('clientId').value = post.client_id;
            document.getElementById('socialAccountId').value = post.social_account_id || '';
            document.getElementById('platform').value = post.platform;
            document.getElementById('scheduledAt').value = post.scheduled_at ? post.scheduled_at.replace(' ', 'T').slice(0, 16) : '';
            document.getElementById('content').value = post.content || '';
            document.getElementById('linkUrl').value = post.link_url || '';
            document.getElementById('status').value = post.status;
            document.getElementById('tags').value = post.tags ? post.tags.join(', ') : '';
            document.getElementById('charCount').textContent = (post.content || '').length;
            
            // Deshabilitar campos en modo vista
            const form = document.getElementById('postForm');
            Array.from(form.elements).forEach(el => {
                if (el.type !== 'hidden') el.disabled = true;
            });
            
            // Mostrar botón de publicar ahora si está programado
            if (post.status === 'scheduled' || post.status === 'approved') {
                document.getElementById('publishNowBtn').style.display = 'inline-block';
            }
            
            // Cargar cuentas del cliente
            loadSocialAccounts(post.client_id);
            
            const modal = new bootstrap.Modal(document.getElementById('postModal'));
            modal.show();
        } else {
            alert('Error al cargar publicación: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar publicación');
    }
}

// Editar publicación
async function editPost(id) {
    try {
        const response = await fetch(`/api/posts.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const post = result.data;
            
            document.getElementById('modalTitle').textContent = 'Editar Publicación';
            document.getElementById('postId').value = post.id;
            document.getElementById('clientId').value = post.client_id;
            document.getElementById('socialAccountId').value = post.social_account_id || '';
            document.getElementById('platform').value = post.platform;
            document.getElementById('scheduledAt').value = post.scheduled_at ? post.scheduled_at.replace(' ', 'T').slice(0, 16) : '';
            document.getElementById('content').value = post.content || '';
            document.getElementById('linkUrl').value = post.link_url || '';
            document.getElementById('status').value = post.status;
            document.getElementById('tags').value = post.tags ? post.tags.join(', ') : '';
            document.getElementById('charCount').textContent = (post.content || '').length;
            
            // Habilitar campos
            const form = document.getElementById('postForm');
            Array.from(form.elements).forEach(el => {
                el.disabled = false;
            });
            
            // Mostrar botón de publicar ahora si aplica
            if (post.status === 'scheduled' || post.status === 'approved') {
                document.getElementById('publishNowBtn').style.display = 'inline-block';
            }
            
            // Cargar cuentas del cliente
            loadSocialAccounts(post.client_id);
            
            const modal = new bootstrap.Modal(document.getElementById('postModal'));
            modal.show();
        } else {
            alert('Error al cargar publicación: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar publicación');
    }
}

// Guardar publicación
async function savePost() {
    const id = document.getElementById('postId').value;
    const isEdit = id !== '';
    
    const tags = document.getElementById('tags').value
        ? document.getElementById('tags').value.split(',').map(t => t.trim()).filter(t => t)
        : [];
    
    const data = {
        action: isEdit ? 'update' : 'create',
        id: id || undefined,
        client_id: document.getElementById('clientId').value,
        social_account_id: document.getElementById('socialAccountId').value || null,
        platform: document.getElementById('platform').value,
        scheduled_at: document.getElementById('scheduledAt').value,
        content: document.getElementById('content').value,
        link_url: document.getElementById('linkUrl').value || null,
        status: document.getElementById('status').value,
        tags: tags
    };
    
    if (isEdit) {
        data.id = id;
    }
    
    try {
        const response = await fetch('/api/posts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('postModal'));
            modal.hide();
            
            calendar.refetchEvents();
            
            alert(result.message || 'Publicación guardada exitosamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar publicación');
    }
}

// Publicar ahora
async function publishNow() {
    const id = document.getElementById('postId').value;
    
    if (!id) {
        alert('No hay publicación seleccionada');
        return;
    }
    
    if (!confirm('¿Publicar esta publicación ahora?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/posts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'publish_now', id: id})
        });
        
        const result = await response.json();
        
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('postModal'));
            modal.hide();
            
            calendar.refetchEvents();
            alert('Publicación realizada exitosamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al publicar');
    }
}

// Eliminar publicación
async function deletePost(id) {
    if (!confirm('¿Estás seguro de eliminar esta publicación?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/posts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete', id: id})
        });
        
        const result = await response.json();
        
        if (result.success) {
            calendar.refetchEvents();
            alert('Publicación eliminada exitosamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar publicación');
    }
}

