<?php
/**
 * Ver detalles de un ítem del menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID&error=1');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos del ítem de menú
$menu = obtenerMenuPorId($id);

// Si no se encontró el ítem, redirigimos
if (!$menu) {
    header('Location: index.php?mensaje=No se encontró el ítem de menú&error=1');
    exit;
}

// Obtener categorías del menú
$categoriasMenu = [];
$categoriasMenuRaw = obtenerCategoriasDeMenu($id);
foreach ($categoriasMenuRaw as $cat) {
    $categoriaInfo = obtenerCategoriaMenuPorId($cat['CATEGORIA_MENU_FK']);
    if ($categoriaInfo) {
        $categoriasMenu[] = $categoriaInfo;
    }
}

// Obtener ingredientes (detalles) del menú
$detalles = obtenerDetallesMenu($id);

// Procesar formulario para agregar ingrediente si se envió
$mensaje = '';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_ingrediente'])) {
    $articulo_id = (int)$_POST['articulo_id'];
    $cantidad = (float)$_POST['cantidad'];
    $precio_unitario = floatval(str_replace(['₡', ','], '', $_POST['precio_unitario'] ?? 0));
    
    if ($articulo_id > 0 && $cantidad > 0 && $precio_unitario > 0) {
        $datos = [
            'menu_id' => $id,
            'articulo_id' => $articulo_id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario
        ];
        
        if (insertarDetalleMenu($datos)) {
            $mensaje = 'Ingrediente agregado correctamente';
            // Recargar detalles
            $detalles = obtenerDetallesMenu($id);
        } else {
            $error = true;
            $mensaje = 'Error al agregar el ingrediente';
        }
    } else {
        $error = true;
        $mensaje = 'Todos los campos son obligatorios y deben ser valores positivos';
    }
}

// Obtener artículos disponibles para ingredientes
$articulos = obtenerArticulosParaMenu();

// Incluir el encabezado
$pageTitle = 'Detalles del Ítem de Menú';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalles del Ítem de Menú</h1>
        <div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="edit.php?id=<?= $id ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
    
    <?php if (!empty($_GET['mensaje'])): ?>
        <div class="alert alert-<?= isset($_GET['error']) && $_GET['error'] ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($_GET['mensaje']) ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Información del Ítem</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h3><?= htmlspecialchars($menu['MENU_NOMBRE']) ?></h3>
                        <h4 class="text-primary">₡ <?= number_format($menu['MENU_PRECIO'], 2, ',', '.') ?></h4>
                        <p class="mb-2">
                            <strong>Disponibilidad:</strong>
                            <?php if ($menu['MENU_DISPONIBILIDAD'] == 'DISPONIBLE'): ?>
                                <span class="badge bg-success">Disponible</span>
                            <?php else: ?>
                                <span class="badge bg-danger">No Disponible</span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-2">
                            <strong>Estado:</strong>
                            <?php if ($menu['ESTADO_ID_FK'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Categorías</h5>
                        <?php if (empty($categoriasMenu)): ?>
                            <p class="text-muted">No hay categorías asignadas</p>
                        <?php else: ?>
                            <div>
                                <?php foreach ($categoriasMenu as $categoria): ?>
                                    <span class="badge bg-info me-1">
                                        <?= htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        Creado: <?= date('d/m/Y H:i', strtotime($menu['CREATION_DATE'])) ?>
                        <?php if (!empty($menu['LAST_UPDATE_DATE'])): ?>
                            | Última actualización: <?= date('d/m/Y H:i', strtotime($menu['LAST_UPDATE_DATE'])) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Ingredientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($detalles)): ?>
                        <p class="text-muted">No hay ingredientes registrados para este ítem.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Artículo</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $detalle): 
                                        // Obtener información del artículo
                                        $conn = getOracleConnection();
                                        $articulo = executeOracleCursorProcedure($conn, 'FIDE_ARTICULO_PKG', 'ARTICULO_SELECCIONAR_POR_ID_SP', [$detalle['ARTICULO_FK']]);
                                        oci_close($conn);
                                        $articuloNombre = !empty($articulo) ? $articulo[0]['ARTICULO_NOMBRE'] : 'Artículo #' . $detalle['ARTICULO_FK'];
                                        
                                        // Calcular subtotal
                                        $subtotal = $detalle['MENU_DETALLE_CANTIDAD'] * $detalle['MENU_DETALLE_PRECIO_UNITARIO'];
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($articuloNombre) ?></td>
                                            <td><?= $detalle['MENU_DETALLE_CANTIDAD'] ?></td>
                                            <td>₡ <?= number_format($detalle['MENU_DETALLE_PRECIO_UNITARIO'], 2, ',', '.') ?></td>
                                            <td>₡ <?= number_format($subtotal, 2, ',', '.') ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="confirmarEliminarDetalle(<?= $detalle['MENU_DETALLE_ID_PK'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarIngredienteModal">
                        <i class="fas fa-plus"></i> Agregar Ingrediente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar ingrediente -->
<div class="modal fade" id="agregarIngredienteModal" tabindex="-1" aria-labelledby="agregarIngredienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarIngredienteModalLabel">Agregar Ingrediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="articulo_id" class="form-label">Artículo *</label>
                        <select class="form-select" id="articulo_id" name="articulo_id" required>
                            <option value="">-- Seleccione un artículo --</option>
                            <?php foreach ($articulos as $articulo): ?>
                                <option value="<?= $articulo['ARTICULO_ID_PK'] ?>">
                                    <?= htmlspecialchars($articulo['ARTICULO_NOMBRE']) ?> - 
                                    ₡ <?= number_format($articulo['ARTICULO_PRECIO'], 2, ',', '.') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="precio_unitario" class="form-label">Precio Unitario *</label>
                        <div class="input-group">
                            <span class="input-group-text">₡</span>
                            <input type="text" class="form-control" id="precio_unitario" name="precio_unitario" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="agregar_ingrediente" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarEliminarDetalle(id) {
    if (confirm('¿Está seguro que desea eliminar este ingrediente?')) {
        window.location.href = 'delete_detalle.php?id=' + id + '&menu_id=<?= $id ?>';
    }
}

// Formato de moneda para el campo de precio unitario
document.getElementById('precio_unitario').addEventListener('input', function(e) {
    let value = this.value.replace(/[^\d]/g, '');
    if (value !== '') {
        value = parseInt(value, 10);
        this.value = new Intl.NumberFormat('es-CR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }
});

// Auto-rellenar precio unitario al seleccionar artículo
document.getElementById('articulo_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const precioText = selectedOption.text.split('₡')[1].trim();
        const precio = parseInt(precioText.replace(/\./g, '').replace(',', '.'));
        
        document.getElementById('precio_unitario').value = new Intl.NumberFormat('es-CR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(precio);
    }
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>