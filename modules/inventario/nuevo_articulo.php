<?php
/**
 * Formulario para crear un nuevo artículo
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

// Obtener tipos de artículo y proveedores para los select
$tiposArticulo = obtenerTiposArticuloActivos();
$proveedores = obtenerProveedoresActivos();

// Variable para almacenar mensajes de error/éxito
$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entradas
    $nombre = sanitizeInput($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $tipoId = intval($_POST['tipo_id'] ?? 0);
    $proveedorId = intval($_POST['proveedor_id'] ?? 0);
    
    // Validación básica
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre del artículo es obligatorio";
    }
    
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor que cero";
    }
    
    if ($tipoId <= 0) {
        $errores[] = "Debe seleccionar un tipo de artículo";
    }
    
    if ($proveedorId <= 0) {
        $errores[] = "Debe seleccionar un proveedor";
    }
    
    // Si no hay errores, insertar el artículo
    if (empty($errores)) {
        $datosArticulo = [
            'nombre' => $nombre,
            'precio' => $precio,
            'tipo_id' => $tipoId,
            'proveedor_id' => $proveedorId
        ];
        
        $resultado = insertarArticulo($datosArticulo);
        
        if ($resultado) {
            $mensaje = "Artículo creado correctamente con ID: " . $resultado;
            $tipoMensaje = "success";
            
            // Registrar la acción en el log
            registrarLog('Creación de artículo', "Se creó el artículo $nombre con ID $resultado");
            
            // Redireccionar después de 2 segundos
            header("refresh:2;url=articulos.php");
        } else {
            $mensaje = "Error al crear el artículo";
            $tipoMensaje = "danger";
        }
    } else {
        // Hay errores de validación
        $mensaje = "<ul><li>" . implode("</li><li>", $errores) . "</li></ul>";
        $tipoMensaje = "danger";
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nuevo Artículo</h1>
        <a href="articulos.php" class="btn btn-secondary">Volver al listado</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Datos del Artículo</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"></form>
            <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Artículo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <div class="input-group">
                        <span class="input-group-text">₡</span>
                        <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required value="<?= isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="tipo_id" class="form-label">Tipo de Artículo</label>
                    <select class="form-select" id="tipo_id" name="tipo_id" required>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tiposArticulo as $tipo): ?>
                            <option value="<?= $tipo['TIPO_ARTICULO_ID_PK'] ?>" <?= (isset($_POST['tipo_id']) && $_POST['tipo_id'] == $tipo['TIPO_ARTICULO_ID_PK']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['TIPO_ARTICULO_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="proveedor_id" class="form-label">Proveedor</label>
                    <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                        <option value="">Seleccione un proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?= $proveedor['PROVEEDOR_ID_PK'] ?>" <?= (isset($_POST['proveedor_id']) && $_POST['proveedor_id'] == $proveedor['PROVEEDOR_ID_PK']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($proveedor['PROVEEDOR_NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Limpiar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>