<?php
/**
 * Ver detalles de una factura
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID de factura&tipo=warning');
    exit;
}

$facturaId = (int)$_GET['id'];

// Obtener datos de la factura con sus detalles
$factura = obtenerFacturaPorId($facturaId);

// Si no se encontró la factura, redirigimos
if (!$factura) {
    header('Location: index.php?mensaje=No se encontró la factura especificada&tipo=danger');
    exit;
}

// Determinar estado de la factura
$estadoClass = 'secondary';
$estadoTexto = 'Desconocido';

switch ($factura['FACTURA_ESTADO_FK']) {
    case 1:
        $estadoClass = 'success';
        $estadoTexto = 'Emitida';
        break;
    case 2:
        $estadoClass = 'info';
        $estadoTexto = 'Pagada';
        break;
    case 3:
        $estadoClass = 'danger';
        $estadoTexto = 'Anulada';
        break;
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4" id="factura-contenido">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalle de Factura #<?= $facturaId ?></h1>
        <div class="btn-group">
            <a href="index.php" class="btn btn-secondary">Volver</a>
            <button class="btn btn-primary" onclick="imprimirFactura()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <?php if ($factura['FACTURA_ESTADO_FK'] == 1): // Solo si está activa ?>
                <a href="anular.php?id=<?= $facturaId ?>" class="btn btn-danger" 
                   onclick="return confirm('¿Está seguro que desea anular esta factura?');">
                    <i class="bi bi-x-circle"></i> Anular
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-<?= isset($_GET['tipo']) ? $_GET['tipo'] : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_GET['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between">
                <h5 class="card-title mb-0">Información de Factura</h5>
                <span class="badge bg-<?= $estadoClass ?>"><?= $estadoTexto ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número de Factura:</strong> <?= htmlspecialchars($factura['FACTURA_ID_PK']) ?></p>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($factura['FACTURA_FECHA']))) ?></p>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($factura['FACTURA_CLIENTE_FK']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Subtotal:</strong> ₡<?= number_format($factura['FACTURA_TOTAL'] - $factura['FACTURA_IVA'], 2) ?></p>
                    <p><strong>IVA (13%):</strong> ₡<?= number_format($factura['FACTURA_IVA'], 2) ?></p>
                    <p><strong>Total:</strong> ₡<?= number_format($factura['FACTURA_TOTAL'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Detalle de Productos</h5>
        </div>
        <div class="card-body">
            <?php if (empty($factura['DETALLES'])): ?>
                <div class="alert alert-warning">
                    No se encontraron detalles para esta factura.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($factura['DETALLES'] as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['ARTICULO_FK']) ?></td>
                                    <td>
                                        <?php
                                        // Aquí deberías obtener el nombre del artículo
                                        // En una aplicación real, esto podría requerir una consulta adicional
                                        echo 'Artículo #' . htmlspecialchars($detalle['ARTICULO_FK']);
                                        ?>
                                    </td>
                                    <td class="text-center"><?= htmlspecialchars($detalle['CANTIDAD']) ?></td>
                                    <td class="text-end">₡<?= number_format($detalle['PRECIO_UNITARIO'], 2) ?></td>
                                    <td class="text-end">₡<?= number_format($detalle['CANTIDAD'] * $detalle['PRECIO_UNITARIO'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">₡<?= number_format($factura['FACTURA_TOTAL'] - $factura['FACTURA_IVA'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>IVA (13%):</strong></td>
                                <td class="text-end">₡<?= number_format($factura['FACTURA_IVA'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>₡<?= number_format($factura['FACTURA_TOTAL'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-4 mb-4 text-center">
        <p class="small text-muted">
            Esta factura fue generada por el sistema de restaurante. 
            <?php if ($factura['FACTURA_ESTADO_FK'] == 3): ?>
                <span class="text-danger"><strong>FACTURA ANULADA</strong></span>
            <?php endif; ?>
        </p>
    </div>
</div>

<script>
function imprimirFactura() {
    // Guardar el contenido original
    const contenidoOriginal = document.body.innerHTML;
    
    // Obtener solo el contenido de la factura
    const contenidoFactura = document.getElementById('factura-contenido').innerHTML;
    
    // Crear un estilo específico para impresión
    const estiloImpresion = `
        <style>
            @media print {
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }
                .btn, .alert, .btn-group {
                    display: none !important;
                }
                .card {
                    border: 1px solid #ddd;
                    margin-bottom: 20px;
                }
                .card-header {
                    background-color: #f8f9fa !important;
                    border-bottom: 1px solid #ddd;
                    padding: 10px;
                }
                .card-body {
                    padding: 15px;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .table th, .table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                .text-end {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
            }
        </style>
    `;
    
    // Establecer el contenido para impresión
    document.body.innerHTML = estiloImpresion + contenidoFactura;
    
    // Imprimir
    window.print();
    
    // Restaurar el contenido original
    document.body.innerHTML = contenidoOriginal;
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>