<?php
/**
 * Página de Reportes y Métricas
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/url-helper.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - IDEAMIA Marketing Platform</title>
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
            text-align: center;
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
                    <a class="nav-link active" href="<?php echo url('pages/reports.php'); ?>">
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
                    <h1><i class="bi bi-graph-up"></i> Reportes y Métricas</h1>
                    <button class="btn btn-primary" onclick="generateReport()">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" id="clientFilter">
                                    <option value="">Seleccionar cliente</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="dateFrom" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="dateTo" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-secondary w-100" onclick="loadMetrics()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Métricas Principales -->
                <div id="metricsContainer">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Selecciona un cliente y periodo para ver las métricas
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row" id="chartsContainer" style="display: none;">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Alcance: Orgánico vs Ads</h5>
                            <canvas id="reachChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Engagement Orgánico</h5>
                            <canvas id="engagementChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Reportes Generados -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Reportes Generados</h5>
                    </div>
                    <div class="card-body">
                        <div id="reportsList">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Generar Reporte -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generar Reporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select class="form-select" id="reportClientId" required>
                                <option value="">Seleccionar cliente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Reporte *</label>
                            <select class="form-select" id="reportType" required>
                                <option value="executive">Resumen Ejecutivo</option>
                                <option value="organic">Solo Orgánico</option>
                                <option value="ads">Solo Anuncios</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Desde *</label>
                            <input type="date" class="form-control" id="reportDateFrom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Hasta *</label>
                            <input type="date" class="form-control" id="reportDateTo" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveReport()">Generar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/../includes/js-config.php'; ?>
    <script src="<?php echo url('assets/js/reports.js'); ?>"></script>
    <script>
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
        
        document.addEventListener('DOMContentLoaded', function() {
            loadClients();
            loadReports();
        });
    </script>
</body>
</html>

