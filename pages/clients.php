<?php
/**
 * Página de Listado de Clientes
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
    <title>Clientes - IDEAMIA Marketing Platform</title>
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
        .client-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .client-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.75rem;
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
                    <a class="nav-link active" href="<?php echo url('pages/clients.php'); ?>">
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
                    <h1><i class="bi bi-people"></i> Clientes</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal" onclick="openClientModal()">
                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="Nombre, email...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">Todos</option>
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                    <option value="suspended">Suspendido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sector</label>
                                <select class="form-select" id="sectorFilter">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-secondary w-100" onclick="applyFilters()">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Clientes -->
                <div id="clientsContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Paginación -->
                <nav id="paginationContainer" class="mt-4">
                    <!-- Se llenará dinámicamente -->
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Modal para Crear/Editar Cliente -->
    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="clientForm">
                        <input type="hidden" id="clientId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Comercial *</label>
                                <input type="text" class="form-control" id="businessName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Razón Social</label>
                                <input type="text" class="form-control" id="legalName">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre de Contacto *</label>
                                <input type="text" class="form-control" id="contactName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email de Contacto *</label>
                                <input type="email" class="form-control" id="contactEmail" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="contactPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" id="contactWhatsapp">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sector</label>
                                <input type="text" class="form-control" id="sector" list="sectorsList">
                                <datalist id="sectorsList"></datalist>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Presupuesto Mensual</label>
                                <input type="number" class="form-control" id="monthlyBudget" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="status">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                                <option value="suspended">Suspendido</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveClient()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/../includes/js-config.php'; ?>
    <script src="<?php echo url('assets/js/clients.js'); ?>"></script>
    <script>
        // Asegurar que las funciones JavaScript estén disponibles
        if (typeof apiUrl === 'undefined') {
            function apiUrl(endpoint) {
                const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
                return (API_BASE_URL || '/api') + '/' + cleanEndpoint;
            }
        }
        if (typeof appUrl === 'undefined') {
            function appUrl(path) {
                return url(path);
            }
        }
    </script>
    <script>
        // Función de logout
        async function logout() {
            if (confirm('¿Estás seguro de cerrar sesión?')) {
                try {
                    const response = await fetch(apiUrl('auth.php?action=logout'), {
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
        
        // Cargar clientes al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadClients();
            loadSectors();
        });
    </script>
</body>
</html>

