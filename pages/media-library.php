<?php
/**
 * Página de Biblioteca de Recursos
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
    <title>Biblioteca de Recursos - IDEAMIA Marketing Platform</title>
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
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .media-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            cursor: pointer;
        }
        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .media-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .media-item .media-info {
            padding: 0.75rem;
        }
        .media-item .media-name {
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .media-item .media-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e8ebff;
            border-color: #764ba2;
        }
        .upload-area.dragover {
            background: #e8ebff;
            border-color: #28a745;
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
                    <a class="nav-link active" href="<?php echo url('pages/media-library.php'); ?>">
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
                    <h1><i class="bi bi-images"></i> Biblioteca de Recursos</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i> Subir Archivo
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" id="clientFilter">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="typeFilter">
                                    <option value="">Todos</option>
                                    <option value="image">Imágenes</option>
                                    <option value="video">Videos</option>
                                    <option value="document">Documentos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Carpeta</label>
                                <select class="form-select" id="folderFilter">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="Nombre de archivo...">
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
                
                <!-- Grid de Media -->
                <div id="mediaContainer">
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
    
    <!-- Modal para Subir Archivo -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select class="form-select" id="uploadClientId" required>
                                <option value="">Seleccionar cliente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Archivo *</label>
                            <input type="file" class="form-control" id="uploadFile" required accept="image/*,video/*">
                            <small class="text-muted">Máximo 10 MB para imágenes, 100 MB para videos</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Carpeta (opcional)</label>
                            <input type="text" class="form-control" id="uploadFolder" placeholder="Ej: campaña-verano">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Etiquetas (separadas por comas)</label>
                            <input type="text" class="form-control" id="uploadTags" placeholder="Ej: producto, promoción, verano">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="uploadFile()">
                        <i class="bi bi-upload"></i> Subir
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/../includes/js-config.php'; ?>
    <script src="<?php echo url('assets/js/media.js'); ?>"></script>
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
            loadMedia();
            loadClients();
        });
    </script>
</body>
</html>

