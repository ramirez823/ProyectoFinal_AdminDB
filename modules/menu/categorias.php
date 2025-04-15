<?php
/**
 * Listado de categorías de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Obtener todas las categorías
$categorias = obtenerCategoriasMenuActivas();

// Verificar mensajes de sesión
$mensaje = '';
$tipo_mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Incluir el encabezado
$pageTitle = 'Gestión de Categorías de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Categorías de Menú</h1>
        <div>
            <a href="categoria_create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Categoría
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver Menú
            </a>
        </div>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= htmlspecialchars($tipo_mensaje) ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="categoriasTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay categorías disponibles</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $categoria): ?>
                                <tr>
                                    <td><?= $categoria['CATEGORIA_MENU_ID_PK'] ?></td>
                                    <td><?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?></td>
                                    <td><?= htmlspecialchars($categoria['CATEGORIA_MENU_DESCRIPCION'] ?? '') ?></td>
                                    <td>
                                        <?php if ($categoria['ESTADO_ID_FK'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="categoria_edit.php?id=<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminar(<?= $categoria['CATEGORIA_MENU_ID_PK'] ?>, '<?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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
function confirmarEliminar(id, nombre) {
    if (confirm('¿Está seguro que desea desactivar la categoría "' + nombre + '"?')) {
        window.location.href = 'categoria_delete.php?id=' + id;
    }
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>