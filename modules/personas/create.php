<?php
/**
 * Formulario para crear una nueva persona
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializamos variables para el formulario
$mensaje = '';
$error = false;

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido1 = trim($_POST['apellido1'] ?? '');
    $apellido2 = trim($_POST['apellido2'] ?? '');
    $direccion_id = !empty($_POST['direccion_id']) ? (int)$_POST['direccion_id'] : null;
    $tipo_id = !empty($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : null;
    
    // Validación básica
    if (empty($cedula) || empty($nombre) || empty($apellido1) || $tipo_id === null) {
        $error = true;
        $mensaje = 'Todos los campos marcados con * son obligatorios.';
    } else {
        // Preparamos los datos para insertar
        $datos = [
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido1' => $apellido1,
            'apellido2' => $apellido2,
            'direccion_id' => $direccion_id,
            'tipo_id' => $tipo_id
        ];
        
        // Intentamos insertar la persona
        if (insertarPersona($datos)) {
            // Redireccionamos a la lista con mensaje de éxito
            header('Location: index.php?mensaje=Persona creada correctamente');
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al crear la persona. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Obtener tipos de persona para el selector
$conn = getOracleConnection();
$tipos_persona = executeOracleCursorProcedure($conn, 'FIDE_TIPO_PERSONA_PKG', 'TIPO_PERSONA_SELECCIONAR_ACTIVOS_SP', [1]);

// Obtener direcciones para el selector
$direcciones = executeOracleCursorProcedure($conn, 'FIDE_DIRECCION_PKG', 'DIRECCION_SELECCIONAR_ACTIVOS_SP', [1]);
oci_close($conn);

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <h1>Crear Nueva Persona</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula/DIMEX *</label>
            <input type="text" class="form-control" id="cedula" name="cedula" required 
                   pattern="^[0-9]{9}$|^[0-9]{12}$" 
                   title="Debe ser un número de 9 dígitos (nacional) o 12 dígitos (DIMEX)">
            <div class="form-text">Formato: 9 dígitos para nacional, 12 dígitos para DIMEX</div>
        </div>
        
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="50">
        </div>
        
        <div class="mb-3">
            <label for="apellido1" class="form-label">Primer Apellido *</label>
            <input type="text" class="form-control" id="apellido1" name="apellido1" required maxlength="50">
        </div>
        
        <div class="mb-3">
            <label for="apellido2" class="form-label">Segundo Apellido</label>
            <input type="text" class="form-control" id="apellido2" name="apellido2" maxlength="50">
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
                    ?>
                    <option value="<?= $direccion['ID_DIRECCION_PK'] ?>">
                        <?= htmlspecialchars($direccionCompleta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Si no encuentra la dirección, primero debe crearla en el módulo de direcciones</div>
        </div>
        
        <div class="mb-3">
            <label for="tipo_id" class="form-label">Tipo de Persona *</label>
            <select class="form-select" id="tipo_id" name="tipo_id" required>
                <option value="">-- Seleccione un tipo --</option>
                <?php foreach ($tipos_persona as $tipo): ?>
                    <option value="<?= $tipo['TIPO_PERSONA_ID_TIPO_PK'] ?>">
                        <?= htmlspecialchars($tipo['TIPO_PERSONA_NOMBRE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>