<?php
/**
 * Página de Gestión de Redes Sociales
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/url-helper.php';

$clientId = $_GET['client_id'] ?? null;
if (!$clientId) {
    require_once __DIR__ . '/../src/helpers/UrlHelper.php';
    UrlHelper::redirect('pages/clients.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redes Sociales - IDEAMIA Marketing Platform</title>
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
        .account-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .platform-icon {
            font-size: 2rem;
            margin-right: 1rem;
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
                    <div>
                        <a href="<?php echo url('pages/clients-detail.php?id=<?php echo htmlspecialchars($clientId); ?>'); ?>" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <h1><i class="bi bi-link-45deg"></i> Redes Sociales</h1>
                    </div>
                    <button class="btn btn-primary" onclick="connectAccount()">
                        <i class="bi bi-plus-circle"></i> Conectar Red Social
                    </button>
                </div>
                
                <!-- Mensaje de éxito si viene del callback -->
                <?php if (isset($_GET['connected'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Redes sociales conectadas exitosamente
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Lista de Cuentas Conectadas -->
                <div id="accountsContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../includes/js-config.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const clientId = <?php echo json_encode($clientId); ?>;
        
        // Cargar cuentas conectadas
        async function loadAccounts() {
            try {
                const response = await fetch(apiUrl(`social-accounts.php?action=list&client_id=${clientId}`));
                const result = await response.json();
                
                if (result.success) {
                    displayAccounts(result.data);
                } else {
                    document.getElementById('accountsContainer').innerHTML = 
                        `<div class="alert alert-danger">${result.error}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('accountsContainer').innerHTML = 
                    '<div class="alert alert-danger">Error al cargar cuentas</div>';
            }
        }
        
        // Mostrar cuentas
        function displayAccounts(accounts) {
            const container = document.getElementById('accountsContainer');
            
            if (!accounts || accounts.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No hay redes sociales conectadas.
                        <br>
                        <button class="btn btn-primary mt-3" onclick="connectAccount()">
                            Conectar Primera Red Social
                        </button>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            accounts.forEach(account => {
                const platformIcon = account.platform === 'facebook' ? 
                    '<i class="bi bi-facebook text-primary"></i>' : 
                    '<i class="bi bi-instagram text-danger"></i>';
                
                const statusClass = {
                    'connected': 'success',
                    'expired': 'warning',
                    'error': 'danger'
                }[account.status] || 'secondary';
                
                const expiresAt = account.token_expires_at ? 
                    new Date(account.token_expires_at).toLocaleDateString('es-MX') : 
                    'N/A';
                
                html += `
                    <div class="account-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center">
                                <div class="platform-icon">
                                    ${platformIcon}
                                </div>
                                <div>
                                    <h5 class="mb-1">${escapeHtml(account.account_name)}</h5>
                                    <small class="text-muted">
                                        ${account.platform === 'facebook' ? 'Facebook Page' : 'Instagram Business'}
                                        <br>
                                        ID: ${account.account_id}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${statusClass} status-badge mb-2">${account.status}</span>
                                <br>
                                <small class="text-muted">Expira: ${expiresAt}</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="checkStatus(${account.id})">
                                <i class="bi bi-check-circle"></i> Verificar Estado
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="refreshToken(${account.id})">
                                <i class="bi bi-arrow-clockwise"></i> Refrescar Token
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAccount(${account.id})">
                                <i class="bi bi-trash"></i> Desconectar
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        // Conectar cuenta
        async function connectAccount() {
            try {
                const response = await fetch(apiUrl(`social-accounts.php?action=connect&client_id=${clientId}&platform=facebook`), {
                    method: 'GET'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirigir a la URL de autorización
                    window.location.href = result.auth_url;
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al iniciar conexión');
            }
        }
        
        // Verificar estado
        async function checkStatus(id) {
            try {
                const response = await fetch(apiUrl(`social-accounts.php?action=status&id=${id}`));
                const result = await response.json();
                
                if (result.success) {
                    const status = result.data;
                    let message = `Estado: ${status.status}\n`;
                    
                    if (status.is_expired) {
                        message += '⚠️ El token ha expirado. Debes refrescarlo.';
                    } else if (status.days_until_expiry !== null) {
                        message += `El token expira en ${status.days_until_expiry} días.`;
                    }
                    
                    alert(message);
                    loadAccounts(); // Recargar para actualizar estado
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al verificar estado');
            }
        }
        
        // Refrescar token
        async function refreshToken(id) {
            if (!confirm('¿Refrescar el token de esta cuenta?')) {
                return;
            }
            
            try {
                const response = await fetch(apiUrl('social-accounts.php'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'refresh_token', id: id})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Token refrescado exitosamente');
                    loadAccounts();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al refrescar token');
            }
        }
        
        // Eliminar cuenta
        async function deleteAccount(id) {
            if (!confirm('¿Estás seguro de desconectar esta cuenta?')) {
                return;
            }
            
            try {
                const response = await fetch(apiUrl('social-accounts.php'), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'delete', id: id})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Cuenta desconectada exitosamente');
                    loadAccounts();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al desconectar cuenta');
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
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
        
        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', loadAccounts);
    </script>
</body>
</html>

