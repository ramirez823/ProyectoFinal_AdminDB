<?php
/**
 * Lista de facturas
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Obtener todas las facturas activas
$facturas = obtenerFacturasActivas();

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Facturas</h1>
        <a href="crear.php" class="btn btn-primary">Crear Nueva Factura</a>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-<?= isset($_GET['tipo']) ? $_GET['tipo'] : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_GET['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Facturas Emitidas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($facturas)): ?>
                <div class="alert alert-info">
                    No se encontraron facturas en el sistema.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturas as $factura): ?>
                                <tr>
                                    <td><?= htmlspecialchars($factura['FACTURA_ID_PK']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($factura['FACTURA_FECHA']))) ?></td>
                                    <td><?= htmlspecialchars($factura['FACTURA_CLIENTE_FK']) ?></td>
                                    <td>₡<?= number_format($factura['FACTURA_TOTAL'], 2) ?></td>
                                    <td>
                                        <?php
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
                                        ?>
                                        <span class="badge bg-<?= $estadoClass ?>"><?= $estadoTexto ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="ver.php?id=<?= $factura['FACTURA_ID_PK'] ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                            <?php if ($factura['FACTURA_ESTADO_FK'] == 1): // Solo si está emitida (no anulada) ?>
                                                <a href="anular.php?id=<?= $factura['FACTURA_ID_PK'] ?>" class="btn btn-danger" title="Anular factura" 
                                                   onclick="return confirm('¿Está seguro que desea anular esta factura?');">
                                                    <i class="bi bi-x-circle"></i> Anular
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>