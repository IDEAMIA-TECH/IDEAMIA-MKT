<?php
/**
 * Página de Detalle de Campaña
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';

$campaignId = $_GET['id'] ?? null;
if (!$campaignId) {
    header('Location: /pages/campaigns.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Campaña - IDEAMIA Marketing Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .metric-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
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
                    <a class="nav-link" href="/pages/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="/pages/clients.php">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    <a class="nav-link" href="/pages/posts.php">
                        <i class="bi bi-calendar-event"></i> Publicaciones
                    </a>
                    <a class="nav-link active" href="/pages/campaigns.php">
                        <i class="bi bi-megaphone"></i> Campañas
                    </a>
                    <a class="nav-link" href="/pages/reports.php">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                    <a class="nav-link" href="/pages/media-library.php">
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
                    <div>
                        <a href="/pages/campaigns.php" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <h1 id="campaignName">Cargando...</h1>
                    </div>
                    <button class="btn btn-primary" onclick="syncMetrics()">
                        <i class="bi bi-arrow-clockwise"></i> Sincronizar Métricas
                    </button>
                </div>
                
                <!-- Información de la Campaña -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de la Campaña</h5>
                    </div>
                    <div class="card-body" id="campaignInfo">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Métricas Principales -->
                <div class="row mb-4" id="metricsContainer">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <!-- Gráficos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Gasto Diario</h5>
                            <canvas id="spendChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Impresiones vs Alcance</h5>
                            <canvas id="reachChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Clics y CTR</h5>
                            <canvas id="clicksChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>CPC y CPM</h5>
                            <canvas id="costChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const campaignId = <?php echo json_encode($campaignId); ?>;
        let spendChart, reachChart, clicksChart, costChart;
        
        // Cargar información de la campaña
        async function loadCampaignDetail() {
            try {
                const response = await fetch(`/api/campaigns.php?action=summary&id=${campaignId}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    const campaign = data.campaign;
                    const metrics = data.metrics;
                    
                    document.getElementById('campaignName').textContent = campaign.name;
                    
                    // Información de la campaña
                    const statusClass = {
                        'active': 'success',
                        'paused': 'warning',
                        'completed': 'secondary'
                    }[campaign.status] || 'secondary';
                    
                    document.getElementById('campaignInfo').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Cliente:</strong> ${escapeHtml(campaign.client_name || 'N/A')}</p>
                                <p><strong>Objetivo:</strong> ${escapeHtml(campaign.objective || 'N/A')}</p>
                                <p><strong>Estado:</strong> <span class="badge bg-${statusClass}">${campaign.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Presupuesto Diario:</strong> $${parseFloat(campaign.daily_budget || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                                <p><strong>Presupuesto Total:</strong> $${parseFloat(campaign.total_budget || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                                <p><strong>Periodo:</strong> ${campaign.start_date || 'N/A'} - ${campaign.end_date || 'Sin fin'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Métricas principales
                    displayMetrics(metrics);
                    
                    // Cargar métricas diarias para gráficos
                    loadDailyMetrics();
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('campaignInfo').innerHTML = '<div class="alert alert-danger">Error al cargar información de la campaña</div>';
            }
        }
        
        // Mostrar métricas principales
        function displayMetrics(metrics) {
            const container = document.getElementById('metricsContainer');
            
            container.innerHTML = `
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">${parseInt(metrics.total_impressions || 0).toLocaleString('es-MX')}</div>
                        <div class="metric-label">Impresiones</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">${parseInt(metrics.total_reach || 0).toLocaleString('es-MX')}</div>
                        <div class="metric-label">Alcance</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">${parseInt(metrics.total_clicks || 0).toLocaleString('es-MX')}</div>
                        <div class="metric-label">Clics</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">${parseFloat(metrics.avg_ctr || 0).toFixed(2)}%</div>
                        <div class="metric-label">CTR Promedio</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">$${parseFloat(metrics.avg_cpc || 0).toFixed(2)}</div>
                        <div class="metric-label">CPC Promedio</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">$${parseFloat(metrics.avg_cpm || 0).toFixed(2)}</div>
                        <div class="metric-label">CPM Promedio</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">$${parseFloat(metrics.total_spend || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                        <div class="metric-label">Gasto Total</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card text-center">
                        <div class="metric-value">${parseInt(metrics.total_conversions || 0).toLocaleString('es-MX')}</div>
                        <div class="metric-label">Conversiones</div>
                    </div>
                </div>
            `;
        }
        
        // Cargar métricas diarias
        async function loadDailyMetrics() {
            try {
                const dateTo = new Date().toISOString().split('T')[0];
                const dateFrom = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                
                const response = await fetch(`/api/campaigns.php?action=metrics&id=${campaignId}&date_from=${dateFrom}&date_to=${dateTo}`);
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    const data = result.data;
                    const labels = data.map(d => d.period_date);
                    
                    // Gráfico de Gasto
                    const spendCtx = document.getElementById('spendChart').getContext('2d');
                    if (spendChart) spendChart.destroy();
                    spendChart = new Chart(spendCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Gasto',
                                data: data.map(d => parseFloat(d.spend || 0)),
                                borderColor: 'rgb(102, 126, 234)',
                                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                tension: 0.1
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
                    
                    // Gráfico de Impresiones vs Alcance
                    const reachCtx = document.getElementById('reachChart').getContext('2d');
                    if (reachChart) reachChart.destroy();
                    reachChart = new Chart(reachCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Impresiones',
                                    data: data.map(d => parseInt(d.impressions || 0)),
                                    backgroundColor: 'rgba(102, 126, 234, 0.5)'
                                },
                                {
                                    label: 'Alcance',
                                    data: data.map(d => parseInt(d.reach || 0)),
                                    backgroundColor: 'rgba(40, 167, 69, 0.5)'
                                }
                            ]
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
                    
                    // Gráfico de Clics
                    const clicksCtx = document.getElementById('clicksChart').getContext('2d');
                    if (clicksChart) clicksChart.destroy();
                    clicksChart = new Chart(clicksCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Clics',
                                    data: data.map(d => parseInt(d.clicks || 0)),
                                    borderColor: 'rgb(40, 167, 69)',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'CTR (%)',
                                    data: data.map(d => parseFloat(d.ctr || 0)),
                                    borderColor: 'rgb(255, 193, 7)',
                                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            }
                        }
                    });
                    
                    // Gráfico de Costos
                    const costCtx = document.getElementById('costChart').getContext('2d');
                    if (costChart) costChart.destroy();
                    costChart = new Chart(costCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'CPC',
                                    data: data.map(d => parseFloat(d.cpc || 0)),
                                    borderColor: 'rgb(220, 53, 69)',
                                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                    tension: 0.1
                                },
                                {
                                    label: 'CPM',
                                    data: data.map(d => parseFloat(d.cpm || 0)),
                                    borderColor: 'rgb(255, 193, 7)',
                                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                    tension: 0.1
                                }
                            ]
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
            } catch (error) {
                console.error('Error al cargar métricas diarias:', error);
            }
        }
        
        // Sincronizar métricas
        async function syncMetrics() {
            if (!confirm('¿Sincronizar métricas desde Meta?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/campaigns.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'sync',
                        id: campaignId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    loadCampaignDetail();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al sincronizar métricas');
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
                    const response = await fetch('/api/auth.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({action: 'logout'})
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = '/index.php';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.href = '/index.php';
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', loadCampaignDetail);
    </script>
</body>
</html>

