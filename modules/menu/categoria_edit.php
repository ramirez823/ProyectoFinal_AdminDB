<?php
/**
 * Formulario para editar una categoría de menú existente
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;
$categoria = null;

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    $_SESSION['mensaje'] = 'Debe especificar un ID para editar.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: categorias.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos actuales de la categoría
$categoria = obtenerCategoriaMenuPorId($id);

// Si no se encontró la categoría, redirigimos
if (!$categoria) {
    $_SESSION['mensaje'] = 'No se encontró la categoría de menú con el ID especificado.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: categorias.php');
    exit;
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado_id = (int)$_POST['estado_id'];
    
    // Validación básica
    if (empty($nombre)) {
        $error = true;
        $mensaje = 'El nombre de la categoría es obligatorio.';
    } else {
        // Preparamos los datos para actualizar
        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'estado_id' => $estado_id
        ];
        
        // Intentamos actualizar la categoría
        if (actualizarCategoriaMenu($datos)) {
            $_SESSION['mensaje'] = 'Categoría actualizada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: categorias.php');
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al actualizar la categoría. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Obtener estados para el selector
$conn = getOracleConnection();
$estados = executeOracleCursorProcedure($conn, 'FIDE_ESTADO_PKG', 'ESTADO_SELECCIONAR_TODOS_SP', []);
oci_close($conn);

// Incluir el encabezado
$pageTitle = 'Editar Categoría de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Categoría de Menú</h1>
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
                           value="<?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>" required>
                    <div class="invalid-feedback">
                        Por favor ingrese un nombre para la categoría.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($categoria['CATEGORIA_MENU_DESCRIPCION'] ?? '') ?></textarea>
                    <div class="form-text">
                        Describa brevemente el propósito de esta categoría de menú.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="estado_id" class="form-label">Estado</label>
                    <select class="form-select" id="estado_id" name="estado_id">
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado['ESTADO_ID_PK'] ?>" <?= $categoria['ESTADO_ID_FK'] == $estado['ESTADO_ID_PK'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($estado['ESTADO_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="categorias.php" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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