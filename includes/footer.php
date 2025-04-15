<?php
/**
 * Footer común para todas las páginas
 * Incluye pie de página, scripts adicionales y cierre de estructura HTML
 */

// Determinar la ruta base para recursos
$basePath = '/restaurante-app';
$currentYear = date('Y');
?>
        </main>
        <!-- Fin del contenido principal -->
        
        <!-- Pie de página -->
        <footer class="footer bg-dark text-white mt-auto py-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; <?= $currentYear ?> Sistema de Gestión de Restaurante</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-0">Desarrollado con <i class="fas fa-heart text-danger"></i></p> 
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts personalizados -->
    <script src="<?= $basePath ?>/assets/js/scripts.js"></script>
    
    <!-- Inicialización de tooltips y popovers -->
    <script>
        // Inicializar todos los tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar todos los popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Automatizar el cierre de alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>