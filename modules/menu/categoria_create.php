<?php
/**
 * Formulario para crear una nueva categoría de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;
$nombre = '';
$descripcion = '';

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    // Validación básica
    if (empty($nombre)) {
        $error = true;
        $mensaje = 'El nombre de la categoría es obligatorio.';
    } else {
        // Preparamos los datos para insertar
        $datos = [
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ];
        
        // Intentamos insertar la categoría
        $categoriaId = insertarCategoriaMenu($datos);
        
        if ($categoriaId) {
            // Redireccionamos a la vista de categorías
            $_SESSION['mensaje'] = 'Categoría creada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: categorias.php");
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al crear la categoría. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Incluir el encabezado
$pageTitle = 'Crear Categoría de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Crear Nueva Categoría de Menú</h1>
        <div>
            <a href="categorias.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($nombre) ?>" required>
                    <div class="invalid-feedback">
                        Por favor ingrese un nombre para la categoría.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>
                    <div class="form-text">
                        Describa brevemente el propósito de esta categoría de menú.
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="categorias.php" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>