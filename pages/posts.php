<?php
/**
 * Página de Calendario de Publicaciones
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
    <title>Calendario de Publicaciones - IDEAMIA Marketing Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
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
        #calendar {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
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
                    <a class="nav-link active" href="<?php echo url('pages/posts.php'); ?>">
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
                    <h1><i class="bi bi-calendar-event"></i> Calendario de Publicaciones</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal" onclick="openPostModal()">
                        <i class="bi bi-plus-circle"></i> Nueva Publicación
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" id="clientFilter">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">Todos</option>
                                    <option value="draft">Borrador</option>
                                    <option value="scheduled">Programado</option>
                                    <option value="published">Publicado</option>
                                    <option value="failed">Fallido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Plataforma</label>
                                <select class="form-select" id="platformFilter">
                                    <option value="">Todas</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="both">Ambas</option>
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
                
                <!-- Leyenda de Estados -->
                <div class="status-legend">
                    <div class="status-item">
                        <div class="status-color" style="background-color: #6c757d;"></div>
                        <small>Borrador</small>
                    </div>
                    <div class="status-item">
                        <div class="status-color" style="background-color: #ffc107;"></div>
                        <small>Pendiente Aprobación</small>
                    </div>
                    <div class="status-item">
                        <div class="status-color" style="background-color: #17a2b8;"></div>
                        <small>Aprobado</small>
                    </div>
                    <div class="status-item">
                        <div class="status-color" style="background-color: #007bff;"></div>
                        <small>Programado</small>
                    </div>
                    <div class="status-item">
                        <div class="status-color" style="background-color: #28a745;"></div>
                        <small>Publicado</small>
                    </div>
                    <div class="status-item">
                        <div class="status-color" style="background-color: #dc3545;"></div>
                        <small>Rechazado/Fallido</small>
                    </div>
                </div>
                
                <!-- Calendario -->
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Crear/Editar Publicación -->
    <div class="modal fade" id="postModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm">
                        <input type="hidden" id="postId">
                        
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select class="form-select" id="clientId" required>
                                <option value="">Seleccionar cliente</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Red Social</label>
                            <select class="form-select" id="socialAccountId">
                                <option value="">Seleccionar cuenta (opcional)</option>
                            </select>
                            <small class="text-muted">Si no seleccionas, se podrá elegir al programar</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Plataforma *</label>
                            <select class="form-select" id="platform" required>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="both">Ambas</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fecha y Hora Programada *</label>
                            <input type="datetime-local" class="form-control" id="scheduledAt" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contenido *</label>
                            <textarea class="form-control" id="content" rows="5" required maxlength="5000"></textarea>
                            <small class="text-muted">
                                <span id="charCount">0</span> / 5000 caracteres
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">URL de Referencia</label>
                            <input type="url" class="form-control" id="linkUrl" placeholder="https://...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="status">
                                <option value="draft">Borrador</option>
                                <option value="scheduled">Programado</option>
                                <option value="approved">Aprobado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Etiquetas (separadas por comas)</label>
                            <input type="text" class="form-control" id="tags" placeholder="campaña, producto, promoción">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="savePost()">Guardar</button>
                    <button type="button" class="btn btn-success" id="publishNowBtn" onclick="publishNow()" style="display:none;">
                        <i class="bi bi-send"></i> Publicar Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/../includes/js-config.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js"></script>
    <script src="<?php echo url('assets/js/posts.js'); ?>"></script>
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
        
        // Cargar clientes al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadClients();
        });
    </script>
</body>
</html>

