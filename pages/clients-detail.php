<?php
/**
 * Página de Detalle de Cliente
 * IDEAMIA Marketing Platform
 */

require_once __DIR__ . '/../includes/auth-check.php';

$clientId = $_GET['id'] ?? null;
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
    <title>Detalle de Cliente - IDEAMIA Marketing Platform</title>
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
                    <a class="nav-link active" href="/pages/clients.php">
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
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="/pages/clients.php" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <h1 id="clientName">Cargando...</h1>
                    </div>
                    <button class="btn btn-primary" onclick="editClient()">
                        <i class="bi bi-pencil"></i> Editar Cliente
                    </button>
                </div>
                
                <!-- Información del Cliente -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body" id="clientInfo">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones Rápidas -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Acciones Rápidas</h5>
                        <a href="/pages/social-accounts.php?client_id=<?php echo htmlspecialchars($clientId); ?>" class="btn btn-primary me-2">
                            <i class="bi bi-link-45deg"></i> Gestionar Redes Sociales
                        </a>
                        <button class="btn btn-secondary" onclick="editClient()">
                            <i class="bi bi-pencil"></i> Editar Cliente
                        </button>
                    </div>
                </div>
                
                <!-- Resumen -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="icon text-primary mb-2">
                                <i class="bi bi-link-45deg" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 id="statSocial">-</h3>
                            <p class="text-muted mb-0">Redes Conectadas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="icon text-warning mb-2">
                                <i class="bi bi-megaphone" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 id="statCampaigns">-</h3>
                            <p class="text-muted mb-0">Campañas Activas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="icon text-success mb-2">
                                <i class="bi bi-calendar-event" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 id="statPosts">-</h3>
                            <p class="text-muted mb-0">Próximas Publicaciones</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="icon text-info mb-2">
                                <i class="bi bi-cash-coin" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 id="statBudget">-</h3>
                            <p class="text-muted mb-0">Presupuesto</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const clientId = <?php echo json_encode($clientId); ?>;
        
        // Cargar información del cliente
        async function loadClientDetail() {
            try {
                // Cargar información básica
                const response = await fetch(`/api/clients.php?action=get&id=${clientId}`);
                const result = await response.json();
                
                if (result.success) {
                    const client = result.data;
                    document.getElementById('clientName').textContent = client.business_name;
                    
                    const statusClass = {
                        'active': 'success',
                        'inactive': 'secondary',
                        'suspended': 'danger'
                    }[client.status] || 'secondary';
                    
                    document.getElementById('clientInfo').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre Comercial:</strong> ${escapeHtml(client.business_name)}</p>
                                ${client.legal_name ? `<p><strong>Razón Social:</strong> ${escapeHtml(client.legal_name)}</p>` : ''}
                                <p><strong>Estado:</strong> <span class="badge bg-${statusClass}">${client.status}</span></p>
                                ${client.sector ? `<p><strong>Sector:</strong> ${escapeHtml(client.sector)}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Contacto:</strong> ${escapeHtml(client.contact_name)}</p>
                                <p><strong>Email:</strong> <a href="mailto:${escapeHtml(client.contact_email)}">${escapeHtml(client.contact_email)}</a></p>
                                ${client.contact_phone ? `<p><strong>Teléfono:</strong> ${escapeHtml(client.contact_phone)}</p>` : ''}
                                ${client.contact_whatsapp ? `<p><strong>WhatsApp:</strong> ${escapeHtml(client.contact_whatsapp)}</p>` : ''}
                            </div>
                        </div>
                        ${client.monthly_budget ? `<p><strong>Presupuesto Mensual:</strong> $${parseFloat(client.monthly_budget).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>` : ''}
                        ${client.notes ? `<hr><p><strong>Observaciones:</strong><br>${escapeHtml(client.notes)}</p>` : ''}
                    `;
                }
                
                // Cargar resumen
                const summaryResponse = await fetch(`/api/clients.php?action=summary&id=${clientId}`);
                const summaryResult = await summaryResponse.json();
                
                if (summaryResult.success) {
                    const summary = summaryResult.data;
                    document.getElementById('statSocial').textContent = summary.social_accounts || 0;
                    document.getElementById('statCampaigns').textContent = summary.active_campaigns || 0;
                    document.getElementById('statPosts').textContent = summary.upcoming_posts || 0;
                    
                    if (summary.budget_available !== null) {
                        document.getElementById('statBudget').innerHTML = `
                            <small>$${summary.budget_spent.toLocaleString('es-MX')} / $${summary.client.monthly_budget ? parseFloat(summary.client.monthly_budget).toLocaleString('es-MX') : '0'}</small>
                        `;
                    } else {
                        document.getElementById('statBudget').textContent = 'N/A';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('clientInfo').innerHTML = '<div class="alert alert-danger">Error al cargar información del cliente</div>';
            }
        }
        
        function editClient() {
            window.location.href = `/pages/clients.php?edit=${clientId}`;
        }
        
        function escapeHtml(text) {
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
        
        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', loadClientDetail);
    </script>
</body>
</html>

