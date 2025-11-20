/**
 * JavaScript para Biblioteca de Recursos
 */

let currentPage = 1;
let currentFilters = {};

// Cargar media
async function loadMedia(page = 1) {
    currentPage = page;
    
    const filters = {
        page: page,
        per_page: 24,
        client_id: document.getElementById('clientFilter')?.value || '',
        file_type: document.getElementById('typeFilter')?.value || '',
        folder: document.getElementById('folderFilter')?.value || '',
        search: document.getElementById('searchInput')?.value || ''
    };
    
    currentFilters = filters;
    
    const params = new URLSearchParams(filters);
    const container = document.getElementById('mediaContainer');
    
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
    
    try {
        const response = await fetch(apiUrl('media.php?action=list&${params}'));
        const result = await response.json();
        
        if (result.success) {
            displayMedia(result.data);
            
            // Cargar carpetas si hay cliente seleccionado
            if (filters.client_id) {
                loadFolders(filters.client_id);
            }
        } else {
            container.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error al cargar archivos</div>';
    }
}

// Mostrar media en grid
function displayMedia(data) {
    const container = document.getElementById('mediaContainer');
    
    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<div class="alert alert-info text-center">No se encontraron archivos</div>';
        document.getElementById('paginationContainer').innerHTML = '';
        return;
    }
    
    let html = '<div class="media-grid">';
    
    data.data.forEach(item => {
        const fileUrl = '/' + item.file_path;
        const fileSize = formatFileSize(item.file_size);
        
        html += `
            <div class="media-item" onclick="viewMedia(${item.id})">
                ${item.file_type === 'image' ? 
                    `<img src="${fileUrl}" alt="${escapeHtml(item.original_filename)}" loading="lazy">` :
                    `<div style="height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-file-earmark" style="font-size: 3rem; color: #667eea;"></i>
                    </div>`
                }
                <div class="media-info">
                    <div class="media-name" title="${escapeHtml(item.original_filename)}">
                        ${escapeHtml(item.original_filename)}
                    </div>
                    <div class="media-meta">
                        ${fileSize} • ${item.file_type}
                        ${item.folder ? '<br><span class="badge bg-light text-dark">' + escapeHtml(item.folder) + '</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
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
        <a class="page-link" href="#" onclick="loadMedia(${data.page - 1}); return false;">Anterior</a>
    </li>`;
    
    for (let i = 1; i <= data.total_pages; i++) {
        if (i === 1 || i === data.total_pages || (i >= data.page - 2 && i <= data.page + 2)) {
            html += `<li class="page-item ${i === data.page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadMedia(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.page - 3 || i === data.page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    html += `<li class="page-item ${data.page === data.total_pages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadMedia(${data.page + 1}); return false;">Siguiente</a>
    </li>`;
    
    html += '</ul>';
    container.innerHTML = html;
}

// Aplicar filtros
function applyFilters() {
    loadMedia(1);
}

// Ver media
function viewMedia(id) {
    // Abrir en nueva pestaña o mostrar modal con detalles
    window.open(apiUrl('media.php?action=get&id=${id}'), '_blank');
}

// Subir archivo
async function uploadFile() {
    const clientId = document.getElementById('uploadClientId').value;
    const fileInput = document.getElementById('uploadFile');
    const folder = document.getElementById('uploadFolder').value;
    const tags = document.getElementById('uploadTags').value;
    
    if (!clientId || !fileInput.files.length) {
        alert('Completa todos los campos requeridos');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('client_id', clientId);
    if (folder) formData.append('folder', folder);
    if (tags) formData.append('tags', tags);
    
    try {
        const response = await fetch(apiUrl('media.php?action=upload'), {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            modal.hide();
            
            // Limpiar formulario
            document.getElementById('uploadForm').reset();
            
            alert('Archivo subido exitosamente');
            loadMedia(currentPage);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al subir archivo');
    }
}

// Cargar carpetas
async function loadFolders(clientId) {
    if (!clientId) {
        document.getElementById('folderFilter').innerHTML = '<option value="">Todas</option>';
        return;
    }
    
    try {
        const response = await fetch(apiUrl('media.php?action=folders&client_id=${clientId}'));
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('folderFilter');
            select.innerHTML = '<option value="">Todas</option>';
            
            result.data.forEach(folder => {
                const option = document.createElement('option');
                option.value = folder;
                option.textContent = folder;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar carpetas:', error);
    }
}

// Cargar clientes
async function loadClients() {
    try {
        const response = await fetch(apiUrl('clients.php?action=list&per_page=100'));
        const result = await response.json();
        
        if (result.success && result.data.data) {
            const clientFilter = document.getElementById('clientFilter');
            const uploadClientId = document.getElementById('uploadClientId');
            
            result.data.data.forEach(client => {
                const option1 = document.createElement('option');
                option1.value = client.id;
                option1.textContent = client.business_name;
                clientFilter.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = client.id;
                option2.textContent = client.business_name;
                uploadClientId.appendChild(option2);
            });
            
            // Listener para cargar carpetas cuando se selecciona cliente
            clientFilter.onchange = function() {
                loadFolders(this.value);
                applyFilters();
            };
        }
    } catch (error) {
        console.error('Error al cargar clientes:', error);
    }
}

// Formatear tamaño de archivo
function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Búsqueda en tiempo real
let searchTimeout;
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadMedia(1);
            }, 500);
        });
    }
});

