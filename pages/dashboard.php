<?php
/**
 * Dashboard Principal
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IDEAMIA Marketing Platform</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
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
                    <a class="nav-link active" href="/pages/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="/pages/clients.php">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    <a class="nav-link" href="/pages/posts.php">
                        <i class="bi bi-calendar-event"></i> Publicaciones
                    </a>
                    <a class="nav-link" href="/pages/campaigns.php">
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
                
                <!-- Notificaciones en Sidebar -->
                <div class="px-3 mt-3">
                    <?php
                    require_once __DIR__ . '/../src/models/Notification.php';
                    $db = new Database();
                    $notification = new Notification($db);
                    $unreadCount = $notification->getUnreadCount($currentUser['id']);
                    ?>
                    <a href="/pages/notifications.php" class="nav-link text-white">
                        <i class="bi bi-bell"></i> Notificaciones
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Dashboard</h1>
                    <span class="badge bg-primary"><?php echo strtoupper($currentUser['role']); ?></span>
                </div>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Clientes</h6>
                                    <h3 class="mb-0" id="stat-clients">-</h3>
                                </div>
                                <div class="icon text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Publicaciones</h6>
                                    <h3 class="mb-0" id="stat-posts">-</h3>
                                </div>
                                <div class="icon text-success">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Campañas Activas</h6>
                                    <h3 class="mb-0" id="stat-campaigns">-</h3>
                                </div>
                                <div class="icon text-warning">
                                    <i class="bi bi-megaphone"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Redes Conectadas</h6>
                                    <h3 class="mb-0" id="stat-social">-</h3>
                                </div>
                                <div class="icon text-info">
                                    <i class="bi bi-link-45deg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Message -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bienvenido, <?php echo htmlspecialchars($currentUser['name']); ?>!</h5>
                        <p class="card-text">
                            Has iniciado sesión correctamente en IDEAMIA Marketing Platform.
                            <br>
                            <strong>Rol:</strong> <?php echo htmlspecialchars($currentUser['role']); ?>
                            <br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función de logout
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
        
        // Cargar estadísticas (se implementará cuando tengamos los módulos)
        // Por ahora solo mostramos placeholders
        document.getElementById('stat-clients').textContent = '0';
        document.getElementById('stat-posts').textContent = '0';
        document.getElementById('stat-campaigns').textContent = '0';
        document.getElementById('stat-social').textContent = '0';
    </script>
</body>
</html>

