/**
 * JavaScript para Gestión de Clientes
 * Maneja todas las operaciones AJAX
 */

let currentPage = 1;
let currentFilters = {};

// Cargar lista de clientes
async function loadClients(page = 1) {
    currentPage = page;
    
    const filters = {
        page: page,
        per_page: 20,
        search: document.getElementById('searchInput')?.value || '',
        status: document.getElementById('statusFilter')?.value || '',
        sector: document.getElementById('sectorFilter')?.value || ''
    };
    
    currentFilters = filters;
    
    const params = new URLSearchParams(filters);
    const container = document.getElementById('clientsContainer');
    
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
    
    try {
        const response = await fetch(`/api/clients.php?action=list&${params}`);
        const result = await response.json();
        
        if (result.success) {
            displayClients(result.data);
        } else {
            container.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error al cargar clientes</div>';
    }
}

// Mostrar clientes en la lista
function displayClients(data) {
    const container = document.getElementById('clientsContainer');
    
    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No se encontraron clientes</div>';
        document.getElementById('paginationContainer').innerHTML = '';
        return;
    }
    
    let html = '<div class="row">';
    
    data.data.forEach(client => {
        const statusClass = {
            'active': 'success',
            'inactive': 'secondary',
            'suspended': 'danger'
        }[client.status] || 'secondary';
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="client-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-1">${escapeHtml(client.business_name)}</h5>
                            ${client.legal_name ? `<small class="text-muted">${escapeHtml(client.legal_name)}</small>` : ''}
                        </div>
                        <span class="badge bg-${statusClass} status-badge">${client.status}</span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-person"></i> ${escapeHtml(client.contact_name)}<br>
                            <i class="bi bi-envelope"></i> ${escapeHtml(client.contact_email)}
                        </small>
                    </div>
                    
                    ${client.sector ? `<div class="mb-2"><span class="badge bg-light text-dark">${escapeHtml(client.sector)}</span></div>` : ''}
                    
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewClient(${client.id})">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editClient(${client.id})">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteClient(${client.id})">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Mostrar paginación
    displayPagination(data);
}

// Mostrar paginación
function displayPagination(data) {
    const container = document.getElementById('paginationContainer');
    
    if (data.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    html += `<li class="page-item ${data.page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadClients(${data.page - 1}); return false;">Anterior</a>
    </li>`;
    
    // Números de página
    for (let i = 1; i <= data.total_pages; i++) {
        if (i === 1 || i === data.total_pages || (i >= data.page - 2 && i <= data.page + 2)) {
            html += `<li class="page-item ${i === data.page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadClients(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.page - 3 || i === data.page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Botón siguiente
    html += `<li class="page-item ${data.page === data.total_pages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadClients(${data.page + 1}); return false;">Siguiente</a>
    </li>`;
    
    html += '</ul>';
    container.innerHTML = html;
}

// Aplicar filtros
function applyFilters() {
    loadClients(1);
}

// Abrir modal para nuevo cliente
function openClientModal() {
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = '';
    document.getElementById('status').value = 'active';
}

// Ver detalle de cliente
function viewClient(id) {
    window.location.href = `/pages/clients-detail.php?id=${id}`;
}

// Editar cliente
async function editClient(id) {
    try {
        const response = await fetch(`/api/clients.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const client = result.data;
            
            document.getElementById('modalTitle').textContent = 'Editar Cliente';
            document.getElementById('clientId').value = client.id;
            document.getElementById('businessName').value = client.business_name || '';
            document.getElementById('legalName').value = client.legal_name || '';
            document.getElementById('contactName').value = client.contact_name || '';
            document.getElementById('contactEmail').value = client.contact_email || '';
            document.getElementById('contactPhone').value = client.contact_phone || '';
            document.getElementById('contactWhatsapp').value = client.contact_whatsapp || '';
            document.getElementById('sector').value = client.sector || '';
            document.getElementById('monthlyBudget').value = client.monthly_budget || '';
            document.getElementById('status').value = client.status || 'active';
            document.getElementById('notes').value = client.notes || '';
            
            const modal = new bootstrap.Modal(document.getElementById('clientModal'));
            modal.show();
        } else {
            alert('Error al cargar cliente: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar cliente');
    }
}

// Guardar cliente (crear o actualizar)
async function saveClient() {
    const id = document.getElementById('clientId').value;
    const isEdit = id !== '';
    
    const data = {
        action: isEdit ? 'update' : 'create',
        id: id || undefined,
        business_name: document.getElementById('businessName').value,
        legal_name: document.getElementById('legalName').value,
        contact_name: document.getElementById('contactName').value,
        contact_email: document.getElementById('contactEmail').value,
        contact_phone: document.getElementById('contactPhone').value,
        contact_whatsapp: document.getElementById('contactWhatsapp').value,
        sector: document.getElementById('sector').value,
        monthly_budget: document.getElementById('monthlyBudget').value || null,
        status: document.getElementById('status').value,
        notes: document.getElementById('notes').value
    };
    
    if (isEdit) {
        data.id = id;
    }
    
    try {
        const response = await fetch('/api/clients.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
            modal.hide();
            
            loadClients(currentPage);
            
            // Mostrar mensaje de éxito
            alert(result.message || 'Cliente guardado exitosamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar cliente');
    }
}

// Eliminar cliente
async function deleteClient(id) {
    if (!confirm('¿Estás seguro de eliminar este cliente?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/clients.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete', id: id})
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadClients(currentPage);
            alert('Cliente eliminado exitosamente');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar cliente');
    }
}

// Cargar sectores para el filtro y datalist
async function loadSectors() {
    try {
        const response = await fetch('/api/clients.php?action=sectors');
        const result = await response.json();
        
        if (result.success && result.data) {
            const sectorFilter = document.getElementById('sectorFilter');
            const sectorsList = document.getElementById('sectorsList');
            
            result.data.forEach(sector => {
                // Agregar al select de filtro
                const option = document.createElement('option');
                option.value = sector;
                sectorFilter.appendChild(option);
                
                // Agregar al datalist
                const datalistOption = document.createElement('option');
                datalistOption.value = sector;
                sectorsList.appendChild(datalistOption);
            });
        }
    } catch (error) {
        console.error('Error al cargar sectores:', error);
    }
}

// Función helper para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Búsqueda en tiempo real (con debounce)
let searchTimeout;
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadClients(1);
            }, 500);
        });
    }
});

