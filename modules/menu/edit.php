<?php
/**
 * Formulario para editar un ítem existente del menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;
$menu = null;

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID para editar&error=1');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos actuales del ítem de menú
$menu = obtenerMenuPorId($id);

// Si no se encontró el ítem, redirigimos
if (!$menu) {
    header('Location: index.php?mensaje=No se encontró el ítem de menú con el ID especificado&error=1');
    exit;
}

// Obtener categorías asignadas al ítem de menú
$categoriasMenuRaw = obtenerCategoriasDeMenu($id);
$categoriasAsignadas = [];
foreach ($categoriasMenuRaw as $cat) {
    $categoriasAsignadas[] = $cat['CATEGORIA_MENU_FK'];
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval(str_replace(['₡', ','], '', $_POST['precio'] ?? 0));
    $disponibilidad = $_POST['disponibilidad'] ?? 'DISPONIBLE';
    $estado_id = (int)$_POST['estado_id'];
    $categorias = $_POST['categorias'] ?? [];
    
    // Validación básica
    if (empty($nombre) || $precio <= 0) {
        $error = true;
        $mensaje = 'El nombre y el precio son obligatorios y el precio debe ser mayor a cero.';
    } else {
        // Preparamos los datos para actualizar
        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'precio' => $precio,
            'disponibilidad' => $disponibilidad,
            'estado_id' => $estado_id
        ];
        
        // Intentamos actualizar el ítem de menú
        if (actualizarMenu($datos)) {
            // Si se actualizó correctamente, actualizar las categorías
            
            // Primero, obtener las categorías actuales
            $categoriasActualesRaw = obtenerCategoriasDeMenu($id);
            $categoriasActuales = [];
            foreach ($categoriasActualesRaw as $cat) {
                $categoriasActuales[] = $cat['CATEGORIA_MENU_FK'];
            }
            
            // Desasignar categorías que ya no están seleccionadas
            foreach ($categoriasActuales as $catId) {
                if (!in_array($catId, $categorias)) {
                    desasignarCategoriaDeMenu($id, $catId);
                }
            }
            
            // Asignar nuevas categorías
            foreach ($categorias as $catId) {
                if (!in_array($catId, $categoriasActuales)) {
                    asignarCategoriaAMenu($id, $catId);
                }
            }
            
            // Redireccionamos a la vista de detalles
            header("Location: view.php?id={$id}&mensaje=Ítem actualizado correctamente");
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al actualizar el ítem. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Obtener todas las categorías de menú disponibles
$todasCategorias = obtenerCategoriasMenuActivas();

// Obtener estados para el selector
$conn = getOracleConnection();
$estados = executeOracleCursorProcedure($conn, 'FIDE_ESTADO_PKG', 'ESTADO_SELECCIONAR_TODOS_SP', []);
oci_close($conn);

// Incluir el encabezado
$pageTitle = 'Editar Ítem de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Ítem de Menú</h1>
        <div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="view.php?id=<?= $id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver Detalles
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
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($menu['MENU_NOMBRE']) ?>" required>
                            <div class="invalid-feedback">
                                Por favor ingrese un nombre para el ítem.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">₡</span>
                                <input type="text" class="form-control" id="precio" name="precio" 
                                       value="<?= number_format($menu['MENU_PRECIO'], 0, '', ',') ?>" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="disponibilidad" class="form-label">Disponibilidad</label>
                            <select class="form-select" id="disponibilidad" name="disponibilidad">
                                <option value="DISPONIBLE" <?= $menu['MENU_DISPONIBILIDAD'] == 'DISPONIBLE' ? 'selected' : '' ?>>Disponible</option>
                                <option value="AGOTADO" <?= $menu['MENU_DISPONIBILIDAD'] == 'AGOTADO' ? 'selected' : '' ?>>No Disponible</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado_id" class="form-label">Estado</label>
                            <select class="form-select" id="estado_id" name="estado_id">
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['ESTADO_ID_PK'] ?>" <?= $menu['ESTADO_ID_FK'] == $estado['ESTADO_ID_PK'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['ESTADO_NOMBRE']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Categorías</label>
                            <div class="card">
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <?php if (empty($todasCategorias)): ?>
                                        <p class="text-muted">No hay categorías disponibles. <a href="categorias.php">Crear categorías</a></p>
                                    <?php else: ?>
                                        <?php foreach ($todasCategorias as $categoria): 
                                            $checked = in_array($categoria['CATEGORIA_MENU_ID_PK'], $categoriasAsignadas) ? 'checked' : '';
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="categorias[]" value="<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>" 
                                                       id="categoria_<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>" <?= $checked ?>>
                                                <label class="form-check-label" for="categoria_<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>">
                                                    <?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Para gestionar los ingredientes, vaya a la vista de detalles después de guardar los cambios.
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="view.php?id=<?= $id ?>" class="btn btn-secondary me-md-2">Cancelar</a>
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

// Formato de moneda para el campo de precio
document.getElementById('precio').addEventListener('input', function(e) {
    let value = this.value.replace(/[^\d]/g, '');
    if (value !== '') {
        value = parseInt(value, 10);
        this.value = new Intl.NumberFormat('es-CR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>