<?php
/**
 * Listado de inventario
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

// Obtener datos para el listado
$articulos = obtenerArticulosActivos();
$inventario = obtenerInventarioActivo();

// Productos con nivel crítico (cantidad disponible <= cantidad mínima)
$productosCriticos = [];
foreach ($inventario as $item) {
    if ($item['INVENTARIO_CANTIDAD_DISPONIBLE'] <= $item['INVENTARIO_CANTIDAD_MINIMA']) {
        $productosCriticos[] = $item;
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <h1>Gestión de Inventario</h1>
    
    <!-- Tarjetas de navegación rápida -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Artículos</h5>
                    <p class="card-text">Gestión de artículos en inventario</p>
                    <a href="articulos.php" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Movimientos</h5>
                    <p class="card-text">Registro de entradas y salidas</p>
                    <a href="movimientos.php" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Vencimientos</h5>
                    <p class="card-text">Control de fechas de vencimiento</p>
                    <a href="vencimientos.php" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informes</h5>
                    <p class="card-text">Reportes de inventario</p>
                    <a href="informes.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerta de productos críticos -->
    <?php if (!empty($productosCriticos)): ?>
    <div class="alert alert-warning">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Atención!</h4>
        <p>Existen <?= count($productosCriticos) ?> productos con nivel crítico de inventario.</p>
        <a href="#productosCriticos" class="btn btn-sm btn-warning">Ver productos críticos</a>
    </div>
    <?php endif; ?>
    
    <!-- Resumen de inventario -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Resumen de Inventario</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total de Artículos</h5>
                            <h2 class="card-text"><?= count($articulos) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Items en Inventario</h5>
                            <h2 class="card-text"><?= count($inventario) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Productos Críticos</h5>
                            <h2 class="card-text text-danger"><?= count($productosCriticos) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de productos en inventario -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Inventario Actual</h5>
            <a href="nuevo_inventario.php" class="btn btn-light btn-sm">Nuevo Registro</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Artículo</th>
                            <th>Disponible</th>
                            <th>Mínimo</th>
                            <th>Precio</th>
                            <th>Fecha Ingreso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventario)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay registros de inventario</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventario as $item): ?>
                                <?php 
                                // Buscar el nombre del artículo
                                $nombreArticulo = "Desconocido";
                                foreach ($articulos as $articulo) {
                                    if ($articulo['ARTICULO_ID_PK'] == $item['ARTICULO_FK']) {
                                        $nombreArticulo = $articulo['ARTICULO_NOMBRE'];
                                        break;
                                    }
                                }
                                
                                // Determinar estado visual
                                $estadoInventario = "Normal";
                                $claseBadge = "bg-success";
                                if ($item['INVENTARIO_CANTIDAD_DISPONIBLE'] <= $item['INVENTARIO_CANTIDAD_MINIMA']) {
                                    $estadoInventario = "Crítico";
                                    $claseBadge = "bg-danger";
                                } else if ($item['INVENTARIO_CANTIDAD_DISPONIBLE'] <= ($item['INVENTARIO_CANTIDAD_MINIMA'] * 1.5)) {
                                    $estadoInventario = "Bajo";
                                    $claseBadge = "bg-warning";
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['INVENTARIO_ID_INVENTARIO_PK']) ?></td>
                                    <td><?= htmlspecialchars($nombreArticulo) ?></td>
                                    <td><?= htmlspecialchars($item['INVENTARIO_CANTIDAD_DISPONIBLE']) ?></td>
                                    <td><?= htmlspecialchars($item['INVENTARIO_CANTIDAD_MINIMA']) ?></td>
                                    <td><?= formatearMoneda($item['INVENTARIO_PRECIO']) ?></td>
                                    <td><?= formatearFecha($item['INVENTARIO_FECHA_INGRESO']) ?></td>
                                    <td><span class="badge <?= $claseBadge ?>"><?= $estadoInventario ?></span></td>
                                    <td>
                                        <a href="ajustar_inventario.php?id=<?= $item['INVENTARIO_ID_INVENTARIO_PK'] ?>" class="btn btn-sm btn-primary">Ajustar</a>
                                        <a href="ver_movimientos.php?id=<?= $item['INVENTARIO_ID_INVENTARIO_PK'] ?>" class="btn btn-sm btn-info">Movimientos</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Lista de productos críticos -->
    <?php if (!empty($productosCriticos)): ?>
    <div class="card mb-4" id="productosCriticos">
        <div class="card-header bg-danger text-white">
            <h5>Productos con Nivel Crítico</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Artículo</th>
                            <th>Disponible</th>
                            <th>Mínimo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productosCriticos as $item): ?>
                            <?php 
                            // Buscar el nombre del artículo
                            $nombreArticulo = "Desconocido";
                            foreach ($articulos as $articulo) {
                                if ($articulo['ARTICULO_ID_PK'] == $item['ARTICULO_FK']) {
                                    $nombreArticulo = $articulo['ARTICULO_NOMBRE'];
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['INVENTARIO_ID_INVENTARIO_PK']) ?></td>
                                <td><?= htmlspecialchars($nombreArticulo) ?></td>
                                <td class="text-danger"><?= htmlspecialchars($item['INVENTARIO_CANTIDAD_DISPONIBLE']) ?></td>
                                <td><?= htmlspecialchars($item['INVENTARIO_CANTIDAD_MINIMA']) ?></td>
                                <td>
                                    <a href="ajustar_inventario.php?id=<?= $item['INVENTARIO_ID_INVENTARIO_PK'] ?>" class="btn btn-sm btn-warning">Ajustar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>