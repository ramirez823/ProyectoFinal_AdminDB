<?php
/**
 * Gestión de ingredientes (detalles) para un ítem de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar si se proporcionó un ID de menú
if (!isset($_GET['menu_id']) || empty($_GET['menu_id'])) {
    $_SESSION['mensaje'] = 'Debe especificar un ítem de menú para gestionar sus ingredientes.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: index.php');
    exit;
}

$menu_id = (int)$_GET['menu_id'];

// Obtener información del menú
$menu = obtenerMenuPorId($menu_id);
if (!$menu) {
    $_SESSION['mensaje'] = 'El ítem de menú especificado no existe.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: index.php');
    exit;
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$articulo_id = '';
$cantidad = '';
$precio_unitario = '';

// Verificar mensajes de sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Procesar el formulario de agregar detalle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $articulo_id = (int)$_POST['articulo_id'];
    $cantidad = floatval($_POST['cantidad']);
    $precio_unitario = floatval(str_replace(['₡', ','], '', $_POST['precio_unitario']));
    
    // Validación básica
    if ($articulo_id <= 0 || $cantidad <= 0 || $precio_unitario <= 0) {
        $mensaje = 'Todos los campos son obligatorios y deben tener valores positivos.';
        $tipo_mensaje = 'danger';
    } else {
        // Preparar datos para insertar
        $datos = [
            'menu_id' => $menu_id,
            'articulo_id' => $articulo_id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario
        ];
        
        // Intentar insertar el detalle
        $detalle_id = insertarDetalleMenu($datos);
        
        if ($detalle_id) {
            $mensaje = 'Ingrediente agregado correctamente.';
            $tipo_mensaje = 'success';
            
            // Limpiar variables para nuevo ingreso
            $articulo_id = '';
            $cantidad = '';
            $precio_unitario = '';
        } else {
            $mensaje = 'Error al agregar el ingrediente. Verifica los datos e intenta nuevamente.';
            $tipo_mensaje = 'danger';
        }
    }
}

// Procesar eliminación de detalle
if (isset($_GET['eliminar_detalle']) && !empty($_GET['eliminar_detalle'])) {
    $detalle_id = (int)$_GET['eliminar_detalle'];
    
    if (eliminarDetalleMenu($detalle_id)) {
        $mensaje = 'Ingrediente eliminado correctamente.';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al eliminar el ingrediente. Inténtelo nuevamente.';
        $tipo_mensaje = 'danger';
    }
}

// Obtener los detalles actuales del menú
$detalles = obtenerDetallesMenu($menu_id);

// Obtener todos los artículos disponibles para el selector
$articulos = obtenerArticulosParaMenu();

// Incluir el encabezado
$pageTitle = 'Gestionar Ingredientes del Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestionar Ingredientes: <?= htmlspecialchars($menu['MENU_NOMBRE']) ?></h1>
        <div>
            <a href="view.php?id=<?= $menu_id ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Detalles
            </a>
        </div>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= htmlspecialchars($tipo_mensaje) ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Formulario para agregar ingrediente -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Agregar Ingrediente</h5>
                </div>
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="accion" value="agregar">
                        
                        <div class="mb-3">
                            <label for="articulo_id" class="form-label">Artículo *</label>
                            <select class="form-select" id="articulo_id" name="articulo_id" required>
                                <option value="">Seleccione un artículo</option>
                                <?php foreach ($articulos as $articulo): ?>
                                    <option value="<?= $articulo['ARTICULO_ID_PK'] ?>" <?= $articulo_id == $articulo['ARTICULO_ID_PK'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($articulo['ARTICULO_NOMBRE']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un artículo.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   value="<?= htmlspecialchars($cantidad) ?>" step="0.01" min="0.01" required>
                            <div class="invalid-feedback">
                                Por favor ingrese una cantidad válida.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="precio_unitario" class="form-label">Precio Unitario *</label>
                            <div class="input-group">
                                <span class="input-group-text">₡</span>
                                <input type="text" class="form-control" id="precio_unitario" name="precio_unitario" 
                                       value="<?= htmlspecialchars($precio_unitario) ?>" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido.
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Agregar Ingrediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Listado de ingredientes actuales -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ingredientes de <?= htmlspecialchars($menu['MENU_NOMBRE']) ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($detalles)): ?>
                        <div class="alert alert-info">
                            Este ítem de menú aún no tiene ingredientes registrados.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Artículo</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($detalles as $detalle): 
                                        // Obtener información del artículo
                                        $conn = getOracleConnection();
                                        $articulo = executeOracleCursorProcedure($conn, 'FIDE_ARTICULO_PKG', 'ARTICULO_SELECCIONAR_POR_ID_SP', [$detalle['ARTICULO_FK']]);
                                        oci_close($conn);
                                        
                                        $articulo_nombre = !empty($articulo) ? $articulo[0]['ARTICULO_NOMBRE'] : 'Artículo Desconocido';
                                        $subtotal = $detalle['CANTIDAD'] * $detalle['PRECIO_UNITARIO'];
                                        $total += $subtotal;
                                    ?>
                                        <tr>
                                            <td><?= $detalle['MENU_DETALLE_ID_PK'] ?></td>
                                            <td><?= htmlspecialchars($articulo_nombre) ?></td>
                                            <td><?= number_format($detalle['CANTIDAD'], 2, ',', '.') ?></td>
                                            <td><?= '₡' . number_format($detalle['PRECIO_UNITARIO'], 2, ',', '.') ?></td>
                                            <td><?= '₡' . number_format($subtotal, 2, ',', '.') ?></td>
                                            <td>
                                                <a href="?menu_id=<?= $menu_id ?>&eliminar_detalle=<?= $detalle['MENU_DETALLE_ID_PK'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Está seguro que desea eliminar este ingrediente?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th><?= '₡' . number_format($total, 2, ',', '.') ?></th>
                                        <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th><?= '₡' . number_format($total, 2, ',', '.') ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sugerencia de precio de venta -->
<?php if (!empty($detalles)): ?>
<div class="card mt-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5>Sugerencia de Precio</h5>
                <p>Basado en los ingredientes, el precio sugerido de venta sería:</p>
                <ul>
                    <li><strong>Costo de ingredientes:</strong> ₡<?= number_format($total, 2, ',', '.') ?></li>
                    <li><strong>Precio sugerido (30% margen):</strong> ₡<?= number_format($total * 1.3, 2, ',', '.') ?></li>
                    <li><strong>Precio sugerido (50% margen):</strong> ₡<?= number_format($total * 1.5, 2, ',', '.') ?></li>
                </ul>
                <p>El precio actual configurado es: <strong>₡<?= number_format($menu['MENU_PRECIO'], 2, ',', '.') ?></strong></p>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-end">
                <a href="edit.php?id=<?= $menu_id ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Actualizar Precio
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>