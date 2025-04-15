<?php
/**
 * Editar dirección existente
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID de dirección&tipo=warning');
    exit;
}

$direccionId = (int)$_GET['id'];

// Obtener datos de la dirección
$direccion = obtenerDireccionPorId($direccionId);

// Si no se encontró la dirección, redirigimos
if (!$direccion) {
    header('Location: index.php?mensaje=No se encontró la dirección especificada&tipo=danger');
    exit;
}

// Obtener todas las provincias
$provincias = obtenerProvinciasActivas();

// Obtener cantones de la provincia actual
$cantones = [];
if (!empty($direccion['ID_PROVINCIA_FK'])) {
    $cantones = obtenerCantonesPorProvincia($direccion['ID_PROVINCIA_FK']);
}

// Obtener distritos del cantón actual
$distritos = [];
if (!empty($direccion['ID_CANTON_FK'])) {
    $distritos = obtenerDistritosPorCanton($direccion['ID_CANTON_FK']);
}

// Inicializar variables
$mensaje = '';
$error = false;

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $provinciaId = isset($_POST['provincia_id']) ? (int)$_POST['provincia_id'] : 0;
    $cantonId = isset($_POST['canton_id']) ? (int)$_POST['canton_id'] : 0;
    $distritoId = isset($_POST['distrito_id']) ? (int)$_POST['distrito_id'] : 0;
    $sennas = isset($_POST['sennas']) ? trim($_POST['sennas']) : '';
    $estadoId = isset($_POST['estado_id']) ? (int)$_POST['estado_id'] : 1;
    
    // Validación básica
    if ($provinciaId <= 0 || $cantonId <= 0 || $distritoId <= 0) {
        $error = true;
        $mensaje = 'Debe seleccionar provincia, cantón y distrito.';
    } else {
        // Datos para actualizar
        $datos = [
            'id_direccion' => $direccionId,
            'provincia_id' => $provinciaId,
            'canton_id' => $cantonId,
            'distrito_id' => $distritoId,
            'sennas' => $sennas,
            'estado_id' => $estadoId
        ];
        
        // Actualizar dirección
        if (actualizarDireccion($datos)) {
            // Éxito, redirigir a la página de detalles
            header('Location: ver.php?id=' . $direccionId . '&mensaje=Dirección actualizada correctamente&tipo=success');
            exit;
        } else {
            // Error
            $error = true;
            $mensaje = 'Error al actualizar la dirección. Por favor, intente nuevamente.';
        }
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Dirección</h1>
        <div class="btn-group">
            <a href="index.php" class="btn btn-secondary">Volver a la lista</a>
            <a href="ver.php?id=<?= $direccionId ?>" class="btn btn-info">Ver detalles</a>
        </div>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0">Formulario de Edición</h5>
        </div>
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="provincia_id" class="form-label">Provincia *</label>
                    <select class="form-select" id="provincia_id" name="provincia_id" required>
                        <option value="">-- Seleccione una provincia --</option>
                        <?php foreach ($provincias as $provincia): ?>
                            <option value="<?= $provincia['PROVINCIA_ID_PK'] ?>" <?= $direccion['ID_PROVINCIA_FK'] == $provincia['PROVINCIA_ID_PK'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($provincia['PROVINCIA_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione una provincia.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="canton_id" class="form-label">Cantón *</label>
                    <select class="form-select" id="canton_id" name="canton_id" required>
                        <option value="">-- Seleccione un cantón --</option>
                        <?php foreach ($cantones as $canton): ?>
                            <option value="<?= $canton['CANTON_ID_PK'] ?>" <?= $direccion['ID_CANTON_FK'] == $canton['CANTON_ID_PK'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($canton['CANTON_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un cantón.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="distrito_id" class="form-label">Distrito *</label>
                    <select class="form-select" id="distrito_id" name="distrito_id" required>
                        <option value="">-- Seleccione un distrito --</option>
                        <?php foreach ($distritos as $distrito): ?>
                            <option value="<?= $distrito['DISTRITO_ID_PK'] ?>" <?= $direccion['ID_DISTRITO_FK'] == $distrito['DISTRITO_ID_PK'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($distrito['DISTRITO_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un distrito.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="sennas" class="form-label">Señas</label>
                    <textarea class="form-control" id="sennas" name="sennas" rows="3" 
                              placeholder="Ingrese otras señas o referencias adicionales"><?= htmlspecialchars($direccion['SENNAS'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="estado_id" class="form-label">Estado *</label>
                    <select class="form-select" id="estado_id" name="estado_id" required>
                        <option value="1" <?= $direccion['ESTADO_ID_FK'] == 1 ? 'selected' : '' ?>>Activo</option>
                        <option value="2" <?= $direccion['ESTADO_ID_FK'] == 2 ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                    <div class="form-text">
                        Nota: Cambiar a "Inactivo" puede afectar a las personas asociadas a esta dirección.
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos select
    const provinciaSelect = document.getElementById('provincia_id');
    const cantonSelect = document.getElementById('canton_id');
    const distritoSelect = document.getElementById('distrito_id');
    
    // Cuando cambia la provincia, cargar los cantones
    provinciaSelect.addEventListener('change', function() {
        const provinciaId = this.value;
        
        // Resetear cantones y distritos
        cantonSelect.innerHTML = '<option value="">-- Seleccione un cantón --</option>';
        distritoSelect.innerHTML = '<option value="">-- Primero seleccione un cantón --</option>';
        
        if (provinciaId) {
            // Cargar cantones mediante AJAX
            fetch(`obtener_cantones.php?provincia_id=${provinciaId}`)
                .then(response => response.json())
                .then(cantones => {
                    // Añadir las opciones de cantones
                    cantones.forEach(canton => {
                        const option = document.createElement('option');
                        option.value = canton.CANTON_ID_PK;
                        option.textContent = canton.CANTON_NOMBRE;
                        cantonSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    });
    
    // Cuando cambia el cantón, cargar los distritos
    cantonSelect.addEventListener('change', function() {
        const cantonId = this.value;
        
        // Resetear distritos
        distritoSelect.innerHTML = '<option value="">-- Seleccione un distrito --</option>';
        
        if (cantonId) {
            // Cargar distritos mediante AJAX
            fetch(`obtener_distritos.php?canton_id=${cantonId}`)
                .then(response => response.json())
                .then(distritos => {
                    // Añadir las opciones de distritos
                    distritos.forEach(distrito => {
                        const option = document.createElement('option');
                        option.value = distrito.DISTRITO_ID_PK;
                        option.textContent = distrito.DISTRITO_NOMBRE;
                        distritoSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    });
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>