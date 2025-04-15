<?php
/**
 * Crear nueva dirección
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializar variables
$mensaje = '';
$error = false;

// Obtener todas las provincias para el formulario
$provincias = obtenerProvinciasActivas();

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $provinciaId = isset($_POST['provincia_id']) ? (int)$_POST['provincia_id'] : 0;
    $cantonId = isset($_POST['canton_id']) ? (int)$_POST['canton_id'] : 0;
    $distritoId = isset($_POST['distrito_id']) ? (int)$_POST['distrito_id'] : 0;
    $sennas = isset($_POST['sennas']) ? trim($_POST['sennas']) : '';
    
    // Validación básica
    if ($provinciaId <= 0 || $cantonId <= 0 || $distritoId <= 0) {
        $error = true;
        $mensaje = 'Debe seleccionar provincia, cantón y distrito.';
    } else {
        // Datos para insertar
        $datos = [
            'provincia_id' => $provinciaId,
            'canton_id' => $cantonId,
            'distrito_id' => $distritoId,
            'sennas' => $sennas,
            'estado_id' => 1 // Activo por defecto
        ];
        
        // Insertar dirección
        $direccionId = insertarDireccion($datos);
        
        if ($direccionId) {
            // Éxito, redirigir a la página de detalles
            header('Location: ver.php?id=' . $direccionId . '&mensaje=Dirección creada correctamente&tipo=success');
            exit;
        } else {
            // Error
            $error = true;
            $mensaje = 'Error al crear la dirección. Por favor, intente nuevamente.';
        }
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Crear Nueva Dirección</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Formulario de Dirección</h5>
        </div>
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="provincia_id" class="form-label">Provincia *</label>
                    <select class="form-select" id="provincia_id" name="provincia_id" required>
                        <option value="">-- Seleccione una provincia --</option>
                        <?php foreach ($provincias as $provincia): ?>
                            <option value="<?= $provincia['PROVINCIA_ID_PK'] ?>">
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
                    <select class="form-select" id="canton_id" name="canton_id" required disabled>
                        <option value="">-- Primero seleccione una provincia --</option>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un cantón.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="distrito_id" class="form-label">Distrito *</label>
                    <select class="form-select" id="distrito_id" name="distrito_id" required disabled>
                        <option value="">-- Primero seleccione un cantón --</option>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un distrito.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="sennas" class="form-label">Señas</label>
                    <textarea class="form-control" id="sennas" name="sennas" rows="3" 
                              placeholder="Ingrese otras señas o referencias adicionales"><?= htmlspecialchars($sennas ?? '') ?></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Guardar Dirección</button>
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
        cantonSelect.disabled = true;
        
        distritoSelect.innerHTML = '<option value="">-- Primero seleccione un cantón --</option>';
        distritoSelect.disabled = true;
        
        if (provinciaId) {
            // Cargar cantones mediante AJAX
            fetch(`obtener_cantones.php?provincia_id=${provinciaId}`)
                .then(response => response.json())
                .then(cantones => {
                    // Habilitar el select de cantones
                    cantonSelect.disabled = false;
                    
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
        distritoSelect.disabled = true;
        
        if (cantonId) {
            // Cargar distritos mediante AJAX
            fetch(`obtener_distritos.php?canton_id=${cantonId}`)
                .then(response => response.json())
                .then(distritos => {
                    // Habilitar el select de distritos
                    distritoSelect.disabled = false;
                    
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