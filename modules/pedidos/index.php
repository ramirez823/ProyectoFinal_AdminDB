<?php
/**
 * Listado de pedidos
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

// Obtener todos los pedidos activos
$pedidos = obtenerPedidosActivos();

// Obtener estados de pedido para mostrar el nombre
$estadosPedido = obtenerEstadosPedidoActivos();

// Contar pedidos por estado
$contadorEstados = [];
foreach ($pedidos as $pedido) {
    $estadoId = $pedido['ESTADO_ID_FK'];
    if (!isset($contadorEstados[$estadoId])) {
        $contadorEstados[$estadoId] = 0;
    }
    $contadorEstados[$estadoId]++;
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4"></div>
<h1>Gestión de Pedidos</h1>
    
    <!-- Tarjetas de navegación rápida -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Nuevo Pedido</h5>
                    <p class="card-text">Crear un nuevo pedido</p>
                    <a href="nuevo_pedido.php" class="btn btn-primary">Crear</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pedidos Pendientes</h5>
                    <p class="card-text">Ver pedidos en espera</p>
                    <a href="index.php?estado=1" class="btn btn-warning">Ver</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pedidos Completados</h5>
                    <p class="card-text">Ver pedidos entregados</p>
                    <a href="index.php?estado=3" class="btn btn-success">Ver</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informes</h5>
                    <p class="card-text">Estadísticas de pedidos</p>
                    <a href="informes.php" class="btn btn-info">Ver</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen de pedidos por estado -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Resumen de Pedidos</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Obtener el nombre de los estados y mostrar contadores
                foreach ($estadosPedido as $estado) {
                    $estadoId = $estado['ESTADO_PEDIDO_ID_PK'];
                    $cantidad = isset($contadorEstados[$estadoId]) ? $contadorEstados[$estadoId] : 0;
                    
                    // Determinar color según el estado
                    $cardColor = "bg-light";
                    $textColor = "text-dark";
                    
                    switch(strtoupper($estado['ESTADO_PEDIDO_NOMBRE'])) {
                        case 'PENDIENTE':
                            $cardColor = "bg-warning";
                            break;
                        case 'EN PREPARACIÓN':
                        case 'EN PREPARACION':
                            $cardColor = "bg-info";
                            break;
                        case 'LISTO':
                            $cardColor = "bg-primary";
                            $textColor = "text-white";
                            break;
                        case 'ENTREGADO':
                            $cardColor = "bg-success";
                            $textColor = "text-white";
                            break;
                        case 'ANULADO':
                            $cardColor = "bg-danger";
                            $textColor = "text-white";
                            break;
                    }
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card <?= $cardColor ?> <?= $textColor ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($estado['ESTADO_PEDIDO_NOMBRE']) ?></h5>
                            <h2 class="card-text"><?= $cantidad ?></h2>
                            <a href="index.php?estado=<?= $estadoId ?>" class="btn btn-sm btn-light">Ver</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <!-- Filtro de estado -->
    <?php
    $estadoFiltro = isset($_GET['estado']) ? intval($_GET['estado']) : null;
    $estadoNombre = "Todos";
    
    if ($estadoFiltro) {
        foreach ($estadosPedido as $estado) {
            if ($estado['ESTADO_PEDIDO_ID_PK'] == $estadoFiltro) {
                $estadoNombre = $estado['ESTADO_PEDIDO_NOMBRE'];
                break;
            }
        }
        
        // Filtrar pedidos si hay un estado seleccionado
        $pedidosFiltrados = [];
        foreach ($pedidos as $pedido) {
            if ($pedido['ESTADO_ID_FK'] == $estadoFiltro) {
                $pedidosFiltrados[] = $pedido;
            }
        }
        $pedidos = $pedidosFiltrados;
    }
    ?>
    
    <!-- Lista de pedidos -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pedidos <?= htmlspecialchars($estadoNombre) ?></h5>
            <a href="nuevo_pedido.php" class="btn btn-light btn-sm">Nuevo Pedido</a>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="alert alert-info">
                    No hay pedidos <?= $estadoFiltro ? "en estado " . htmlspecialchars($estadoNombre) : "" ?> para mostrar.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Tipo Entrega</th>
                                <th>Estado</th>
                                <th>Factura</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <?php
                                // Obtener nombre del estado
                                $nombreEstado = "Desconocido";
                                $badgeClass = "bg-secondary";
                                
                                foreach ($estadosPedido as $estado) {
                                    if ($estado['ESTADO_PEDIDO_ID_PK'] == $pedido['ESTADO_ID_FK']) {
                                        $nombreEstado = $estado['ESTADO_PEDIDO_NOMBRE'];
                                        
                                        // Asignar clase de badge según el estado
                                        switch(strtoupper($nombreEstado)) {
                                            case 'PENDIENTE':
                                                $badgeClass = "bg-warning";
                                                break;
                                            case 'EN PREPARACIÓN':
                                            case 'EN PREPARACION':
                                                $badgeClass = "bg-info";
                                                break;
                                            case 'LISTO':
                                                $badgeClass = "bg-primary";
                                                break;
                                            case 'ENTREGADO':
                                                $badgeClass = "bg-success";
                                                break;
                                            case 'ANULADO':
                                                $badgeClass = "bg-danger";
                                                break;
                                        }
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($pedido['PEDIDO_ID_PK']) ?></td>
                                    <td><?= formatearFecha($pedido['PEDIDO_FECHA']) ?></td>
                                    <td><?= htmlspecialchars($pedido['PEDIDO_CEDULA_PERSO']) ?></td>
                                    <td><?= htmlspecialchars($pedido['PEDIDO_ID_TIPO_ENTREGA']) ?></td>
                                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($nombreEstado) ?></span></td>
                                    <td>
                                        <?php if (!empty($pedido['PEDIDO_ID_FACTURA_FK'])): ?>
                                            <a href="../facturacion/ver_factura.php?id=<?= $pedido['PEDIDO_ID_FACTURA_FK'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-invoice"></i> <?= $pedido['PEDIDO_ID_FACTURA_FK'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sin factura</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="ver_pedido.php?id=<?= $pedido['PEDIDO_ID_PK'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                        
                                        <?php if (strtoupper($nombreEstado) !== 'ANULADO' && strtoupper($nombreEstado) !== 'ENTREGADO'): ?>
                                            <a href="actualizar_estado.php?id=<?= $pedido['PEDIDO_ID_PK'] ?>" class="btn btn-sm btn-warning">Estado</a>
                                        <?php endif; ?>
                                        
                                        <?php if (strtoupper($nombreEstado) === 'PENDIENTE'): ?>
                                            <a href="javascript:void(0);" onclick="confirmarAnulacion(<?= $pedido['PEDIDO_ID_PK'] ?>)" class="btn btn-sm btn-danger">Anular</a>
                                        <?php endif; ?>
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