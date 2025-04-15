/**
 * Scripts generales para la aplicación
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Cerrar alertas automáticamente después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Toggle sidebar (para el dashboard)
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-toggled');
            
            // En móviles, alternar la clase para mostrar/ocultar
            if (window.innerWidth < 992) {
                document.body.classList.toggle('sidebar-shown');
            }
        });
    }
    
    // Mantener el sidebar colapsado en móviles por defecto
    if (window.innerWidth < 992) {
        document.body.classList.add('sidebar-toggled');
    }
    
    // Cerrar el sidebar al hacer clic fuera en móviles
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992 && document.body.classList.contains('sidebar-shown')) {
            var sidebar = document.querySelector('.sidebar');
            var sidebarToggle = document.getElementById('sidebarToggle');
            
            if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
                document.body.classList.remove('sidebar-shown');
            }
        }
    });
    
    // Listener para el evento de cierre de sesión
    var logoutLinks = document.querySelectorAll('.logout-link');
    logoutLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
    
    // Funcionalidad de búsqueda en tablas
    var tableSearchInputs = document.querySelectorAll('.table-search-input');
    tableSearchInputs.forEach(function(input) {
        input.addEventListener('keyup', function() {
            var tableId = this.getAttribute('data-table');
            var table = document.getElementById(tableId);
            
            if (table) {
                var searchText = this.value.toLowerCase();
                var rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    var match = text.indexOf(searchText) > -1;
                    row.style.display = match ? '' : 'none';
                });
            }
        });
    });
});

/**
 * Formatea una fecha en formato legible
 * @param {string|Date} date Fecha a formatear
 * @param {boolean} includeTime Incluir hora en el formato
 * @return {string} Fecha formateada
 */
function formatDate(date, includeTime = false) {
    if (!date) return '';
    
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    return date.toLocaleDateString('es-CR', options);
}

/**
 * Formatea un número como moneda
 * @param {number} amount Monto a formatear
 * @param {string} currency Moneda (default: CRC)
 * @return {string} Monto formateado
 */
function formatCurrency(amount, currency = 'CRC') {
    if (isNaN(amount)) return '';
    
    const formatter = new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    });
    
    return formatter.format(amount);
}

/**
 * Genera un componente de confirmación antes de realizar una acción
 * @param {string} message Mensaje de confirmación
 * @param {function} callback Función a ejecutar si se confirma
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Muestra una notificación de toastify
 * @param {string} message Mensaje a mostrar
 * @param {string} type Tipo de notificación (success, error, warning, info)
 * @param {number} duration Duración en milisegundos
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Si existe la librería Toastify
    if (typeof Toastify === 'function') {
        const backgroundColor = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };
        
        Toastify({
            text: message,
            duration: duration,
            gravity: 'top',
            position: 'right',
            backgroundColor: backgroundColor[type],
            stopOnFocus: true
        }).showToast();
    } else {
        // Fallback a alert
        alert(message);
    }
}

/**
 * Función para cambiar entre páginas con fade
 * @param {string} url URL a cargar
 */
function navigateWithFade(url) {
    document.body.classList.add('fade-out');
    
    setTimeout(function() {
        window.location.href = url;
    }, 300);
}

/**
 * Inicializa un dropdown dependiente
 * @param {string} parentId ID del select padre
 * @param {string} childId ID del select hijo
 * @param {object} data Datos para rellenar el dropdown hijo
 */
function initDependentDropdown(parentId, childId, data) {
    const parentSelect = document.getElementById(parentId);
    const childSelect = document.getElementById(childId);
    
    if (!parentSelect || !childSelect) return;
    
    parentSelect.addEventListener('change', function() {
        const parentValue = this.value;
        
        // Limpiar select hijo
        childSelect.innerHTML = '<option value="">-- Seleccione --</option>';
        
        // Si hay valor en el padre y hay datos para ese valor
        if (parentValue && data[parentValue]) {
            // Agregar las opciones al select hijo
            data[parentValue].forEach(function(item) {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                childSelect.appendChild(option);
            });
            
            // Habilitar el select hijo
            childSelect.disabled = false;
        } else {
            // Deshabilitar el select hijo si no hay valor padre
            childSelect.disabled = true;
        }
    });
}