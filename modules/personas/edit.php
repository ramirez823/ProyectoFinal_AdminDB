<?php
/**
 * Formulario para editar una persona existente
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;
$persona = null;

// Verificamos si se proporcionó una cédula
if (empty($_GET['cedula'])) {
    header('Location: index.php?mensaje=Debe especificar una cédula para editar');
    exit;
}

$cedula = trim($_GET['cedula']);

// Obtener datos actuales de la persona
$persona = obtenerPersonaPorCedula($cedula);

// Si no se encontró la persona, redirigimos
if (!$persona) {
    header('Location: index.php?mensaje=No se encontró la persona con la cédula especificada');
    exit;
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificamos si es una acción de desactivar
    if (isset($_POST['accion']) && $_POST['accion'] === 'desactivar') {
        // Intentamos desactivar la persona
        if (desactivarPersona($cedula)) {
            // Redireccionamos a la lista con mensaje de éxito
            header('Location: index.php?mensaje=Persona desactivada correctamente');
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al desactivar la persona. Puede que esté siendo utilizada en otros registros.';
        }
    } else {
        // Es una actualización normal
        // Validamos y sanitizamos los datos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido1 = trim($_POST['apellido1'] ?? '');
        $apellido2 = trim($_POST['apellido2'] ?? '');
        $direccion_id = !empty($_POST['direccion_id']) ? (int)$_POST['direccion_id'] : null;
        $tipo_id = !empty($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : null;
        $estado_id = !empty($_POST['estado_id']) ? (int)$_POST['estado_id'] : 1; // Por defecto activo
        
        // Validación básica
        if (empty($nombre) || empty($apellido1) || $tipo_id === null) {
            $error = true;
            $mensaje = 'Todos los campos marcados con * son obligatorios.';
        } else {
            // Preparamos los datos para actualizar
            $datos = [
                'cedula' => $cedula,
                'nombre' => $nombre,
                'apellido1' => $apellido1,
                'apellido2' => $apellido2,
                'direccion_id' => $direccion_id,
                'tipo_id' => $tipo_id,
                'estado_id' => $estado_id
            ];
            
            // Intentamos actualizar la persona
            if (actualizarPersona($datos)) {
                // Redireccionamos a la lista con mensaje de éxito
                header('Location: index.php?mensaje=Persona actualizada correctamente');
                exit;
            } else {
                $error = true;
                $mensaje = 'Error al actualizar la persona. Verifica los datos e intenta nuevamente.';
            }
        }
    }
}


// Obtener tipos de persona para el selector
$conn = getOracleConnection();
$tipos_persona = executeOracleCursorProcedure($conn, 'FIDE_TIPO_PERSONA_PKG', 'TIPO_PERSONA_SELECCIONAR_ACTIVOS_SP', [1]);

// Obtener direcciones para el selector
$direcciones = executeOracleCursorProcedure($conn, 'FIDE_DIRECCION_PKG', 'DIRECCION_SELECCIONAR_ACTIVOS_SP', [1]);

// Obtener estados para el selector
$estados = executeOracleCursorProcedure($conn, 'FIDE_ESTADO_PKG', 'ESTADO_SELECCIONAR_TODOS_SP', []);
oci_close($conn);

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <h1>Editar Persona</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula/DIMEX</label>
            <input type="text" class="form-control" id="cedula" value="<?= htmlspecialchars($persona['PERSONAS_CEDULA_PERSONA_PK']) ?>" readonly>
            <div class="form-text">La cédula no se puede modificar</div>
        </div>
        
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="50" 
                   value="<?= htmlspecialchars($persona['PERSONAS_NOMBRE']) ?>">
        </div>
        
        <div class="mb-3">
            <label for="apellido1" class="form-label">Primer Apellido *</label>
            <input type="text" class="form-control" id="apellido1" name="apellido1" required maxlength="50" 
                   value="<?= htmlspecialchars($persona['PERSONAS_APELLIDO1']) ?>">
        </div>
        
        <div class="mb-3">
            <label for="apellido2" class="form-label">Segundo Apellido</label>
            <input type="text" class="form-control" id="apellido2" name="apellido2" maxlength="50" 
                   value="<?= htmlspecialchars($persona['PERSONAS_APELLIDO2'] ?? '') ?>">
        </div>
        
        <div class="mb-3">
            <label for="direccion_id" class="form-label">Dirección</label>
            <select class="form-select" id="direccion_id" name="direccion_id">
                <option value="">-- Seleccione una dirección --</option>
                <?php foreach ($direcciones as $direccion): ?>
                    <?php 
                    // Combinar datos para mostrar la dirección completa
                    $direccionCompleta = "ID: {$direccion['ID_DIRECCION_PK']}";
                    if (isset($direccion['SENNAS'])) {
                        $direccionCompleta .= " - {$direccion['SENNAS']}";
                    }
                    
                    $selected = $persona['PERSONAS_ID_DIRECCION_FK'] == $direccion['ID_DIRECCION_PK'] ? 'selected' : '';
                    ?>
                    <option value="<?= $direccion['ID_DIRECCION_PK'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($direccionCompleta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="tipo_id" class="form-label">Tipo de Persona *</label>
            <select class="form-select" id="tipo_id" name="tipo_id" required>
                <option value="">-- Seleccione un tipo --</option>
                <?php foreach ($tipos_persona as $tipo): ?>
                    <?php $selected = $persona['PERSONAS_ID_TIPO_FK'] == $tipo['TIPO_PERSONA_ID_TIPO_PK'] ? 'selected' : ''; ?>
                    <option value="<?= $tipo['TIPO_PERSONA_ID_TIPO_PK'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($tipo['TIPO_PERSONA_NOMBRE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
    <label for="estado_id" class="form-label">Estado *</label>
    <select class="form-select" id="estado_id" name="estado_id" required>
        <?php foreach ($estados as $estado): ?>
            <?php $selected = $persona['ESTADO_ID_FK'] == $estado['ESTADO_ID_PK'] ? 'selected' : ''; ?>
            <option value="<?= $estado['ESTADO_ID_PK'] ?>" <?= $selected ?>>
                <?= htmlspecialchars($estado['ESTADO_NOMBRE']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="form-text">
        Nota: Seleccionar "Inactivo" desactivará esta persona en el sistema.
    </div>
</div>
        
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>
<script>

// Antes de enviar el formulario, confirmamos si el usuario realmente quiere desactivar la persona
document.querySelector('form').addEventListener('submit', function(event) {
    const estadoSelect = document.getElementById('estado_id');
    const estadoId = parseInt(estadoSelect.value);
    
    // Si el estado seleccionado es "Inactivo" (ID 2), pedimos confirmación
    if (estadoId === 2) {
        if (!confirm('¿Está seguro que desea desactivar esta persona? Esta acción no se puede deshacer.')) {
            event.preventDefault(); // Detener el envío del formulario si el usuario cancela
        }
    }
});
</script>


<?php
include_once __DIR__ . '/../../includes/footer.php';
?>