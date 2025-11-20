/**
 * JavaScript para Reportes y Métricas
 */

let reachChart, engagementChart;

// Cargar métricas
async function loadMetrics() {
    const clientId = document.getElementById('clientFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (!clientId) {
        alert('Selecciona un cliente');
        return;
    }
    
    try {
        const response = await fetch(`/api/reports.php?action=metrics&client_id=${clientId}&date_from=${dateFrom}&date_to=${dateTo}`);
        const result = await response.json();
        
        if (result.success) {
            displayMetrics(result.data);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar métricas');
    }
}

// Mostrar métricas
function displayMetrics(data) {
    const container = document.getElementById('metricsContainer');
    const chartsContainer = document.getElementById('chartsContainer');
    
    const organic = data.organic;
    const ads = data.ads;
    const summary = data.summary;
    
    container.innerHTML = `
        <h3 class="mb-4">Resumen del Periodo</h3>
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${organic.total_posts}</div>
                    <div class="metric-label">Publicaciones Orgánicas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${organic.total_engagement.toLocaleString('es-MX')}</div>
                    <div class="metric-label">Engagement Total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${organic.total_reach.toLocaleString('es-MX')}</div>
                    <div class="metric-label">Alcance Orgánico</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${organic.engagement_rate}%</div>
                    <div class="metric-label">Tasa de Engagement</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${ads.total_campaigns}</div>
                    <div class="metric-label">Campañas Activas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">$${ads.total_spend.toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                    <div class="metric-label">Gasto en Ads</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${ads.total_clicks.toLocaleString('es-MX')}</div>
                    <div class="metric-label">Clics en Ads</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value">${summary.total_reach_combined.toLocaleString('es-MX')}</div>
                    <div class="metric-label">Alcance Total</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Mejores Publicaciones</h6>
                    </div>
                    <div class="card-body">
                        ${data.top_posts && data.top_posts.length > 0 ? 
                            data.top_posts.map((post, index) => `
                                <div class="mb-2">
                                    <strong>#${index + 1}</strong> - ${escapeHtml(post.content.substring(0, 50))}...
                                    <br>
                                    <small class="text-muted">
                                        Engagement: ${post.total_engagement.toLocaleString('es-MX')} | 
                                        Alcance: ${post.total_reach.toLocaleString('es-MX')}
                                    </small>
                                </div>
                            `).join('') : 
                            '<p class="text-muted">No hay publicaciones en este periodo</p>'
                        }
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Mejores Campañas</h6>
                    </div>
                    <div class="card-body">
                        ${data.top_campaigns && data.top_campaigns.length > 0 ? 
                            data.top_campaigns.map((campaign, index) => `
                                <div class="mb-2">
                                    <strong>#${index + 1}</strong> - ${escapeHtml(campaign.name)}
                                    <br>
                                    <small class="text-muted">
                                        Clics: ${campaign.total_clicks.toLocaleString('es-MX')} | 
                                        Conversiones: ${campaign.total_conversions.toLocaleString('es-MX')} | 
                                        Gasto: $${parseFloat(campaign.total_spend).toLocaleString('es-MX', {minimumFractionDigits: 2})}
                                    </small>
                                </div>
                            `).join('') : 
                            '<p class="text-muted">No hay campañas en este periodo</p>'
                        }
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Mostrar gráficos
    chartsContainer.style.display = 'block';
    
    // Gráfico de Alcance
    const reachCtx = document.getElementById('reachChart').getContext('2d');
    if (reachChart) reachChart.destroy();
    reachChart = new Chart(reachCtx, {
        type: 'doughnut',
        data: {
            labels: ['Orgánico', 'Ads'],
            datasets: [{
                data: [organic.total_reach, ads.total_reach],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
    
    // Gráfico de Engagement
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    if (engagementChart) engagementChart.destroy();
    engagementChart = new Chart(engagementCtx, {
        type: 'bar',
        data: {
            labels: ['Likes', 'Comentarios', 'Compartidos', 'Guardados'],
            datasets: [{
                label: 'Engagement',
                data: [
                    organic.total_likes,
                    organic.total_comments,
                    organic.total_shares,
                    organic.total_saves
                ],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Generar reporte
function generateReport() {
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    
    // Cargar clientes en el modal
    loadClientsForReport();
    
    // Fechas por defecto
    document.getElementById('reportDateFrom').value = document.getElementById('dateFrom').value;
    document.getElementById('reportDateTo').value = document.getElementById('dateTo').value;
    
    modal.show();
}

// Guardar reporte
async function saveReport() {
    const clientId = document.getElementById('reportClientId').value;
    const type = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('reportDateFrom').value;
    const dateTo = document.getElementById('reportDateTo').value;
    
    if (!clientId || !dateFrom || !dateTo) {
        alert('Completa todos los campos');
        return;
    }
    
    try {
        const response = await fetch('/api/reports.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'generate',
                client_id: clientId,
                type: type,
                period_start: dateFrom,
                period_end: dateTo
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
            modal.hide();
            
            alert('Reporte generado exitosamente');
            loadReports();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al generar reporte');
    }
}

// Cargar reportes
async function loadReports() {
    try {
        const response = await fetch('/api/reports.php?action=list&per_page=10');
        const result = await response.json();
        
        if (result.success) {
            displayReports(result.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mostrar reportes
function displayReports(data) {
    const container = document.getElementById('reportsList');
    
    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No hay reportes generados</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>Cliente</th><th>Tipo</th><th>Periodo</th><th>Generado</th><th>Acciones</th>';
    html += '</tr></thead><tbody>';
    
    data.data.forEach(report => {
        html += `<tr>
            <td>${escapeHtml(report.client_name || 'N/A')}</td>
            <td><span class="badge bg-primary">${report.type}</span></td>
            <td>${report.period_start} - ${report.period_end}</td>
            <td>${new Date(report.created_at).toLocaleDateString('es-MX')}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="downloadReport(${report.id})">
                    <i class="bi bi-download"></i> Descargar
                </button>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Descargar reporte
function downloadReport(id) {
    window.location.href = `/api/reports.php?action=download&id=${id}`;
}

// Cargar clientes
async function loadClients() {
    try {
        const response = await fetch('/api/clients.php?action=list&per_page=100');
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

// Cargar clientes para el modal
async function loadClientsForReport() {
    try {
        const response = await fetch('/api/clients.php?action=list&per_page=100');
        const result = await response.json();
        
        if (result.success && result.data.data) {
            const select = document.getElementById('reportClientId');
            select.innerHTML = '<option value="">Seleccionar cliente</option>';
            
            result.data.data.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.business_name;
                select.appendChild(option);
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

