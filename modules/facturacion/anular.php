<?php
/**
 * Anular una factura
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID de factura&tipo=warning');
    exit;
}

$facturaId = (int)$_GET['id'];

// Obtener datos de la factura
$factura = obtenerFacturaPorId($facturaId);

// Si no se encontró la factura, redirigimos
if (!$factura) {
    header('Location: index.php?mensaje=No se encontró la factura especificada&tipo=danger');
    exit;
}

// Verificar si la factura ya está anulada
if ($factura['FACTURA_ESTADO_FK'] == 3) {
    header('Location: ver.php?id=' . $facturaId . '&mensaje=Esta factura ya está anulada&tipo=warning');
    exit;
}

// Si se confirma la anulación
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'si') {
    // Intentamos anular la factura
    if (anularFactura($facturaId)) {
        header('Location: ver.php?id=' . $facturaId . '&mensaje=Factura anulada correctamente&tipo=success');
        exit;
    } else {
        $error = true;
        $mensaje = 'Error al anular la factura. Intente nuevamente.';
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Anular Factura #<?= $facturaId ?></h1>
        <a href="ver.php?id=<?= $facturaId ?>" class="btn btn-secondary">Volver</a>
    </div>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-<?= isset($error) && $error ? 'danger' : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">Confirmación de Anulación</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <p><strong>¡Atención!</strong> Está a punto de anular la Factura #<?= $facturaId ?>.</p>
                <p>Esta acción no se puede deshacer y la factura quedará registrada como anulada.</p>
                <p>¿Está seguro que desea continuar?</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Información de la Factura:</h6>
                    <ul>
                        <li><strong>Número:</strong> <?= htmlspecialchars($factura['FACTURA_ID_PK']) ?></li>
                        <li><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($factura['FACTURA_FECHA']))) ?></li>
                        <li><strong>Cliente:</strong> <?= htmlspecialchars($factura['FACTURA_CLIENTE_FK']) ?></li>
                        <li><strong>Total:</strong> ₡<?= number_format($factura['FACTURA_TOTAL'], 2) ?></li>
                    </ul>
                </div>
            </div>
            
            <form method="post" class="mt-4">
                <div class="d-flex justify-content-between">
                    <a href="ver.php?id=<?= $facturaId ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="confirmar" value="si" class="btn btn-danger">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>