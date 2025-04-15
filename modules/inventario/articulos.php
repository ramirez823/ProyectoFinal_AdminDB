<?php
/**
 * Listado de artículos
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

// Obtener todos los artículos activos
$articulos = obtenerArticulosActivos();
$tiposArticulo = obtenerTiposArticuloActivos();

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Artículos</h1>
        <a href="nuevo_articulo.php" class="btn btn-primary">Nuevo Artículo</a>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Listado de Artículos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Tipo</th>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($articulos)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay artículos registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($articulos as $articulo): ?>
                                <?php 
                                // Buscar el nombre del tipo de artículo
                                $tipoNombre = "Desconocido";
                                foreach ($tiposArticulo as $tipo) {
                                    if ($tipo['TIPO_ARTICULO_ID_PK'] == $articulo['TIPO_ARTICULO_FK']) {
                                        $tipoNombre = $tipo['TIPO_ARTICULO_NOMBRE'];
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($articulo['ARTICULO_ID_PK']) ?></td>
                                    <td><?= htmlspecialchars($articulo['ARTICULO_NOMBRE']) ?></td>
                                    <td><?= formatearMoneda($articulo['ARTICULO_PRECIO']) ?></td>
                                    <td><?= htmlspecialchars($tipoNombre) ?></td>
                                    <td><?= htmlspecialchars($articulo['PROVEEDOR_FK']) ?></td>
                                    <td>
                                        <a href="editar_articulo.php?id=<?= $articulo['ARTICULO_ID_PK'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="javascript:void(0);" onclick="confirmarEliminar('<?= $articulo['ARTICULO_ID_PK'] ?>')" class="btn btn-sm btn-danger">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro que desea desactivar este artículo?')) {
        window.location.href = 'eliminar_articulo.php?id=' + encodeURIComponent(id);
    }
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>