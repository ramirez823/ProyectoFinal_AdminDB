<?php
/**
 * Eliminar/Desactivar dirección
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

// Verificar si hay personas asociadas a esta dirección
$personasAsociadas = obtenerPersonasPorDireccion($direccionId);

// Si se envió el formulario de confirmación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'si') {
    // Intentamos desactivar la dirección
    if (desactivarDireccion($direccionId)) {
        // Éxito
        header('Location: index.php?mensaje=Dirección desactivada correctamente&tipo=success');
        exit;
    } else {
        // Error
        $error = true;
        $mensaje = 'Error al desactivar la dirección. Por favor, intente nuevamente.';
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Eliminar Dirección</h1>
        <a href="index.php" class="btn btn-secondary">Volver a la lista</a>
    </div>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-<?= isset($error) && $error ? 'danger' : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">Confirmación de Eliminación</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($personasAsociadas)): ?>
                <div class="alert alert-warning">
                    <strong>¡Advertencia!</strong> Esta dirección tiene <?= count($personasAsociadas) ?> persona(s) asociada(s).
                    <p>Antes de eliminar la dirección, debe actualizar las direcciones de estas personas.</p>
                    <a href="ver.php?id=<?= $direccionId ?>" class="btn btn-info mt-2">Ver detalles de la dirección</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p><strong>¡Atención!</strong> Está a punto de desactivar la dirección seleccionada.</p>
                    <p>Esta acción marcará la dirección como inactiva pero no la eliminará de la base de datos.</p>
                    <p>¿Está seguro que desea continuar?</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información de la Dirección:</h6>
                        <ul>
                            <li><strong>ID:</strong> <?= htmlspecialchars($direccion['ID_DIRECCION_PK']) ?></li>
                            <li><strong>Provincia:</strong> 
                                <?php
                                $provincia = obtenerProvinciaPorId($direccion['ID_PROVINCIA_FK']);
                                echo $provincia ? htmlspecialchars($provincia['PROVINCIA_NOMBRE']) : 'No especificada';
                                ?>
                            </li>
                            <li><strong>Cantón:</strong> 
                                <?php
                                $canton = obtenerCantonPorId($direccion['ID_CANTON_FK']);
                                echo $canton ? htmlspecialchars($canton['CANTON_NOMBRE']) : 'No especificado';
                                ?>
                            </li>
                            <li><strong>Distrito:</strong> 
                                <?php
                                $distrito = obtenerDistritoPorId($direccion['ID_DISTRITO_FK']);
                                echo $distrito ? htmlspecialchars($distrito['DISTRITO_NOMBRE']) : 'No especificado';
                                ?>
                            </li>
                            <li><strong>Señas:</strong> <?= !empty($direccion['SENNAS']) ? htmlspecialchars($direccion['SENNAS']) : 'No especificadas' ?></li>
                        </ul>
                    </div>
                </div>
                
                <form method="post" class="mt-4">
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" name="confirmar" value="si" class="btn btn-danger">
                            Confirmar Desactivación
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>