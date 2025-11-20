/**
 * JavaScript para Gestión de Campañas
 */

let currentPage = 1;
let currentFilters = {};

// Cargar lista de campañas
async function loadCampaigns(page = 1) {
    currentPage = page;
    
    const filters = {
        page: page,
        per_page: 20,
        client_id: document.getElementById('clientFilter')?.value || '',
        status: document.getElementById('statusFilter')?.value || ''
    };
    
    currentFilters = filters;
    
    const params = new URLSearchParams(filters);
    const container = document.getElementById('campaignsContainer');
    
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
    
    try {
        const response = await fetch(apiUrl('campaigns.php?action=list&${params}'));
        const result = await response.json();
        
        if (result.success) {
            displayCampaigns(result.data);
        } else {
            container.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error al cargar campañas</div>';
    }
}

// Mostrar campañas
function displayCampaigns(data) {
    const container = document.getElementById('campaignsContainer');
    
    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No se encontraron campañas. Sincroniza desde Meta para importar campañas existentes.</div>';
        document.getElementById('paginationContainer').innerHTML = '';
        return;
    }
    
    let html = '';
    
    data.data.forEach(campaign => {
        const statusClass = {
            'active': 'success',
            'paused': 'warning',
            'completed': 'secondary'
        }[campaign.status] || 'secondary';
        
        html += `
            <div class="campaign-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1">${escapeHtml(campaign.name)}</h5>
                        <small class="text-muted">
                            ${campaign.client_name || 'Sin cliente'}
                            ${campaign.objective ? ' • ' + escapeHtml(campaign.objective) : ''}
                        </small>
                    </div>
                    <span class="badge bg-${statusClass}">${campaign.status}</span>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value">$${parseFloat(campaign.daily_budget || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                            <div class="metric-label">Presupuesto Diario</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value">${campaign.start_date || 'N/A'}</div>
                            <div class="metric-label">Fecha Inicio</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value">${campaign.end_date || 'Sin fin'}</div>
                            <div class="metric-label">Fecha Fin</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewCampaign(${campaign.id})">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="syncCampaignMetrics(${campaign.id})">
                                <i class="bi bi-arrow-clockwise"></i> Sincronizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
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
    
    html += `<li class="page-item ${data.page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadCampaigns(${data.page - 1}); return false;">Anterior</a>
    </li>`;
    
    for (let i = 1; i <= data.total_pages; i++) {
        if (i === 1 || i === data.total_pages || (i >= data.page - 2 && i <= data.page + 2)) {
            html += `<li class="page-item ${i === data.page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadCampaigns(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.page - 3 || i === data.page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    html += `<li class="page-item ${data.page === data.total_pages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadCampaigns(${data.page + 1}); return false;">Siguiente</a>
    </li>`;
    
    html += '</ul>';
    container.innerHTML = html;
}

// Aplicar filtros
function applyFilters() {
    loadCampaigns(1);
}

// Ver detalle de campaña
function viewCampaign(id) {
    window.location.href = appUrl(`pages/campaigns-detail.php?id=${id}`);
}

// Sincronizar campañas desde Meta
async function syncCampaigns() {
    const clientId = prompt('Ingresa el ID del cliente:');
    const adAccountId = prompt('Ingresa el Ad Account ID de Meta:');
    
    if (!clientId || !adAccountId) {
        alert('Se requieren ambos IDs');
        return;
    }
    
    if (!confirm('¿Sincronizar campañas desde Meta para este cliente?')) {
        return;
    }
    
    try {
        const response = await fetch(apiUrl('campaigns.php'), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'sync',
                client_id: clientId,
                ad_account_id: adAccountId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadCampaigns(currentPage);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al sincronizar campañas');
    }
}

// Sincronizar métricas de una campaña
async function syncCampaignMetrics(id) {
    if (!confirm('¿Sincronizar métricas de esta campaña desde Meta?')) {
        return;
    }
    
    try {
        const response = await fetch(apiUrl('campaigns.php'), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'sync',
                id: id
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al sincronizar métricas');
    }
}

// Cargar lista de clientes
async function loadClients() {
    try {
        const response = await fetch(apiUrl('clients.php?action=list&per_page=100'));
        const result = await response.json();
        
        if (result.success && result.data.data) {
            const clientFilter = document.getElementById('clientFilter');
            
            result.data.data.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.business_name;
                clientFilter.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar clientes:', error);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

