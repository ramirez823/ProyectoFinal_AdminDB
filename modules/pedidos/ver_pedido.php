<?php
/**
 * Vista detallada de un pedido
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../menu/functions.php'; // Para obtener información de los ítems de menú

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$pedidoId = intval($_GET['id']);

// Obtener datos del pedido
$pedido = obtenerPedidoPorId($pedidoId);
if (!$pedido) {
    header('Location: index.php');
    exit;
}

// Obtener detalles del pedido
$detallesPedido = obtenerDetallesPedido($pedidoId);

// Obtener historial de seguimiento
$seguimiento = obtenerSeguimientoPedido($pedidoId);

// Obtener estados de pedido para mostrar el nombre
$estadosPedido = obtenerEstadosPedidoActivos();

// Obtener tipos de entrega para mostrar el nombre
$tiposEntrega = obtenerTiposEntregaActivos();

// Obtener información de menú para los detalles
$itemsMenu = obtenerMenuDisponible(); // Esta función debe estar en el módulo de menú

// Buscar nombres de estados y tipos de entrega
$nombreEstado = "Desconocido";
$nombreTipoEntrega = "Desconocido";

foreach ($estadosPedido as $estado) {
    if ($estado['ESTADO_PEDIDO_ID_PK'] == $pedido['ESTADO_ID_FK']) {
        $nombreEstado = $estado['ESTADO_PEDIDO_NOMBRE'];
        break;
    }
}

foreach ($tiposEntrega as $tipo) {
    if ($tipo['TIPO_ENTREGA_ID_PK'] == $pedido['PEDIDO_ID_TIPO_ENTREGA']) {
        $nombreTipoEntrega = $tipo['TIPO_ENTREGA_NOMBRE'];
        break;
    }
}

// Calcular total del pedido
$subtotal = 0;
foreach ($detallesPedido as $detalle) {
    $subtotal += ($detalle['CANTIDAD'] * $detalle['PRECIO_UNITARIO']);
}
$iva = $subtotal * 0.13;
$total = $subtotal + $iva;

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalle de Pedido #<?= $pedidoId ?></h1>
        <div>
            <a href="index.php" class="btn btn-secondary me-2">Volver al listado</a>
            
            <?php if (strtoupper($nombreEstado) !== 'ANULADO' && strtoupper($nombreEstado) !== 'ENTREGADO'): ?>
                <a href="actualizar_estado.php?id=<?= $pedidoId ?>" class="btn btn-warning me-2">Cambiar Estado</a>
            <?php endif; ?>
            
            <?php if (strtoupper($nombreEstado) === 'PENDIENTE'): ?>
                <a href="javascript:void(0);" onclick="confirmarAnulacion(<?= $pedidoId ?>)" class="btn btn-danger">Anular Pedido</a>
            <?php endif; ?>
            
            <?php if (strtoupper($nombreEstado) === 'ENTREGADO' && empty($pedido['PEDIDO_ID_FACTURA_FK'])): ?>
                <a href="../facturacion/crear_factura.php?pedido_id=<?= $pedidoId ?>" class="btn btn-success">Generar Factura</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Información general del pedido -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Número de Pedido:</strong> <?= htmlspecialchars($pedidoId) ?></p>
                            <p><strong>Fecha:</strong> <?= formatearFecha($pedido['PEDIDO_FECHA']) ?></p>
                            <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['PEDIDO_CEDULA_PERSO']) ?></p>
                            <p><strong>Estado:</strong> <span class="badge <?= strtoupper($nombreEstado) === 'ANULADO' ? 'bg-danger' : (strtoupper($nombreEstado) === 'ENTREGADO' ? 'bg-success' : 'bg-warning') ?>"><?= htmlspecialchars($nombreEstado) ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tipo de Entrega:</strong> <?= htmlspecialchars($nombreTipoEntrega) ?></p>
                            <p>
                                <strong>Factura:</strong> 
                                <?php if (!empty($pedido['PEDIDO_ID_FACTURA_FK'])): ?>
                                    <a href="../facturacion/ver_factura.php?id=<?= $pedido['PEDIDO_ID_FACTURA_FK'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-invoice"></i> Ver Factura #<?= $pedido['PEDIDO_ID_FACTURA_FK'] ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin factura</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Creado por:</strong> <?= htmlspecialchars($pedido['CREATED_BY']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detalles del pedido -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ítems del Pedido</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($detallesPedido)): ?>
                        <div class="alert alert-info">Este pedido no tiene ítems</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ítem</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detallesPedido as $index => $detalle): ?>
                                        <?php
                                        $nombreItem = "Desconocido";
                                        // Buscar el nombre del ítem de menú
                                        foreach ($itemsMenu as $item) {
                                            if ($item['MENU_ID_PK'] == $detalle['MENU_FK']) {
                                                $nombreItem = $item['MENU_NOMBRE'];
                                                break;
                                            }
                                        }
                                        $subtotalItem = $detalle['CANTIDAD'] * $detalle['PRECIO_UNITARIO'];
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($nombreItem) ?></td>
                                            <td><?= htmlspecialchars($detalle['CANTIDAD']) ?></td>
                                            <td><?= formatearMoneda($detalle['PRECIO_UNITARIO']) ?></td>
                                            <td class="text-end"><?= formatearMoneda($subtotalItem) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><?= formatearMoneda($subtotal) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>IVA (13%):</strong></td>
                                        <td class="text-end"><?= formatearMoneda($iva) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong><?= formatearMoneda($total) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Seguimiento del pedido -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Historial de Seguimiento</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($seguimiento)): ?>
                        <div class="alert alert-info">No hay registros de seguimiento</div>
                    <?php else: ?>
                        <ul class="timeline">
                            <?php foreach ($seguimiento as $index => $registro): ?>
                                <?php
                                $nombreEstadoSeguimiento = "Desconocido";
                                foreach ($estadosPedido as $estado) {
                                    if ($estado['ESTADO_PEDIDO_ID_PK'] == $registro['ESTADO_PEDIDO_FK']) {
                                        $nombreEstadoSeguimiento = $estado['ESTADO_PEDIDO_NOMBRE'];
                                        break;
                                    }
                                }
                                ?>
                                <li class="timeline-item mb-4">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h5 class="mb-2 d-flex justify-content-between">
                                            <span><?= htmlspecialchars($nombreEstadoSeguimiento) ?></span>
                                            <small class="text-muted"><?= formatearFecha($registro['FECHA_CAMBIO'], 'd/m/Y H:i') ?></small>
                                        </h5>
                                        <p><?= htmlspecialchars($registro['COMENTARIO'] ?? 'Sin comentarios') ?></p>
                                        <p class="text-muted small">Por: <?= htmlspecialchars($registro['CREATED_BY']) ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones disponibles -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (strtoupper($nombreEstado) !== 'ANULADO'): ?>
                            <a href="imprimir_pedido.php?id=<?= $pedidoId ?>" class="btn btn-info" target="_blank">
                                <i class="fas fa-print"></i> Imprimir Pedido
                            </a>
                            
                            <?php if (strtoupper($nombreEstado) === 'PENDIENTE'): ?>
                                <a href="editar_pedido.php?id=<?= $pedidoId ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar Pedido
                                </a>
                            <?php endif; ?>
                            
                            <?php if (strtoupper($nombreEstado) === 'ENTREGADO' && empty($pedido['PEDIDO_ID_FACTURA_FK'])): ?>
                                <a href="../facturacion/crear_factura.php?pedido_id=<?= $pedidoId ?>" class="btn btn-success">
                                    <i class="fas fa-file-invoice"></i> Generar Factura
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    border-left: 3px solid #727cf5;
    margin-left: 20px;
    padding-left: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #727cf5;
    border: 3px solid #fff;
}

.timeline-content {
    padding-left: 15px;
}
</style>

<script>
function confirmarAnulacion(id) {
    if (confirm('¿Está seguro que desea anular este pedido?')) {
        window.location.href = 'anular_pedido.php?id=' + id;
    }
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>