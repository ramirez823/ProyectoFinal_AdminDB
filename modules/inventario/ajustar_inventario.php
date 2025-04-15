<?php
/**
 * Formulario para ajustar cantidad de inventario
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$inventarioId = intval($_GET['id']);

// Obtener datos del inventario
$inventario = obtenerInventarioPorId($inventarioId);
if (!$inventario) {
    header('Location: index.php');
    exit;
}

// Obtener datos del artículo asociado
$articulo = obtenerArticuloPorId($inventario['ARTICULO_FK']);

// Variable para almacenar mensajes de error/éxito
$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entradas
    $nuevaCantidad = intval($_POST['nueva_cantidad'] ?? 0);
    $tipoMovimiento = sanitizeInput($_POST['tipo_movimiento'] ?? '');
    $cantidadMovimiento = intval($_POST['cantidad_movimiento'] ?? 0);
    $motivo = sanitizeInput($_POST['motivo'] ?? '');
    
    // Validación básica
    $errores = [];
    
    if ($tipoMovimiento === 'directo') {
        // Ajuste directo
        if ($nuevaCantidad < 0) {
            $errores[] = "La cantidad no puede ser negativa";
        }
        
        if (empty($motivo)) {
            $errores[] = "Debe proporcionar un motivo para el ajuste directo";
        }
        
        // Si no hay errores, ajustar el inventario
        if (empty($errores)) {
            $resultado = ajustarCantidadInventario($inventarioId, $nuevaCantidad);
            
            if ($resultado) {
                // Registrar el movimiento
                $datosMovimiento = [
                    'tipo' => 'AJUSTE',
                    'cantidad' => $nuevaCantidad - $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'],
                    'fecha' => date('Y-m-d'),
                    'inventario_id' => $inventarioId
                ];
                
                registrarMovimientoInventario($datosMovimiento);
                
                $mensaje = "Inventario ajustado correctamente.";
                $tipoMensaje = "success";
                
                // Registrar la acción en el log
                registrarLog('Ajuste de inventario', "Ajuste directo en inventario ID $inventarioId. Nueva cantidad: $nuevaCantidad. Motivo: $motivo");
                
                // Redireccionar después de 2 segundos
                header("refresh:2;url=index.php");
            } else {
                $mensaje = "Error al ajustar el inventario";
                $tipoMensaje = "danger";
            }
        } else {
            // Hay errores de validación
            $mensaje = "<ul><li>" . implode("</li><li>", $errores) . "</li></ul>";
            $tipoMensaje = "danger";
        }
    } else {
        // Entrada o salida
        if ($cantidadMovimiento <= 0) {
            $errores[] = "La cantidad debe ser mayor que cero";
        }
        
        if ($tipoMovimiento === 'salida' && $cantidadMovimiento > $inventario['INVENTARIO_CANTIDAD_DISPONIBLE']) {
            $errores[] = "No puede retirar más de lo disponible (" . $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] . ")";
        }
        
        if (empty($motivo)) {
            $errores[] = "Debe proporcionar un motivo para el movimiento";
        }
        
        // Si no hay errores, realizar el movimiento
        if (empty($errores)) {
            $nuevaCantidad = $tipoMovimiento === 'entrada' 
                ? $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] + $cantidadMovimiento
                : $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] - $cantidadMovimiento;
            
            $resultado = ajustarCantidadInventario($inventarioId, $nuevaCantidad);
            
            if ($resultado) {
                // Registrar el movimiento
                $datosMovimiento = [
                    'tipo' => $tipoMovimiento === 'entrada' ? 'ENTRADA' : 'SALIDA',
                    'cantidad' => $cantidadMovimiento,
                    'fecha' => date('Y-m-d'),
                    'inventario_id' => $inventarioId
                ];
                
                registrarMovimientoInventario($datosMovimiento);
                
                $mensaje = "Movimiento de inventario registrado correctamente.";
                $tipoMensaje = "success";
                
                // Registrar la acción en el log
                registrarLog('Movimiento de inventario', "Movimiento '" . ($tipoMovimiento === 'entrada' ? 'ENTRADA' : 'SALIDA') . 
                    "' en inventario ID $inventarioId. Cantidad: $cantidadMovimiento. Motivo: $motivo");
                
                // Redireccionar después de 2 segundos
                header("refresh:2;url=index.php");
            } else {
                $mensaje = "Error al registrar el movimiento";
                $tipoMensaje = "danger";
            }
        } else {
            // Hay errores de validación
            $mensaje = "<ul><li>" . implode("</li><li>", $errores) . "</li></ul>";
            $tipoMensaje = "danger";
        }
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Ajustar Inventario</h1>
        <a href="index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información del Artículo</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID Inventario:</strong> <?= htmlspecialchars($inventario['INVENTARIO_ID_INVENTARIO_PK']) ?></p>
                    <p><strong>Artículo:</strong> <?= htmlspecialchars($articulo['ARTICULO_NOMBRE']) ?></p>
                    <p><strong>Precio Unitario:</strong> <?= formatearMoneda($inventario['INVENTARIO_PRECIO']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Cantidad Disponible:</strong> <?= htmlspecialchars($inventario['INVENTARIO_CANTIDAD_DISPONIBLE']) ?></p>
                    <p><strong>Cantidad Mínima:</strong> <?= htmlspecialchars($inventario['INVENTARIO_CANTIDAD_MINIMA']) ?></p>
                    <p><strong>Fecha Ingreso:</strong> <?= formatearFecha($inventario['INVENTARIO_FECHA_INGRESO']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ajustar Inventario</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="ajusteTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="entrada-tab" data-bs-toggle="tab" data-bs-target="#entrada" type="button" role="tab" aria-controls="entrada" aria-selected="true">Entrada</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="salida-tab" data-bs-toggle="tab" data-bs-target="#salida" type="button" role="tab" aria-controls="salida" aria-selected="false">Salida</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ajuste-tab" data-bs-toggle="tab" data-bs-target="#ajuste" type="button" role="tab" aria-controls="ajuste" aria-selected="false">Ajuste Directo</button>
                </li>
            </ul>
            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="ajusteTabContent">
                <!-- Pestaña de Entrada -->
                <div class="tab-pane fade show active" id="entrada" role="tabpanel" aria-labelledby="entrada-tab">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $inventarioId ?>">
                        <input type="hidden" name="tipo_movimiento" value="entrada">
                        
                        <div class="mb-3">
                            <label for="cantidad_movimiento" class="form-label">Cantidad a Ingresar</label>
                            <input type="number" class="form-control" id="cantidad_movimiento" name="cantidad_movimiento" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo de la Entrada</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="2" required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success">Registrar Entrada</button>
                        </div>
                    </form>
                </div>
                
                <!-- Pestaña de Salida -->
                <div class="tab-pane fade" id="salida" role="tabpanel" aria-labelledby="salida-tab">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $inventarioId ?>">
                        <input type="hidden" name="tipo_movimiento" value="salida">
                        
                        <div class="mb-3">
                            <label for="cantidad_movimiento" class="form-label">Cantidad a Retirar</label>
                            <input type="number" class="form-control" id="cantidad_movimiento" name="cantidad_movimiento" min="1" max="<?= $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] ?>" required>
                            <div class="form-text">Disponible: <?= $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo de la Salida</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="2" required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-danger">Registrar Salida</button>
                        </div>
                    </form>
                </div>
                
                <!-- Pestaña de Ajuste Directo -->
                <div class="tab-pane fade" id="ajuste" role="tabpanel" aria-labelledby="ajuste-tab">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $inventarioId ?>">
                        <input type="hidden" name="tipo_movimiento" value="directo">
                        
                        <div class="mb-3">
                            <label for="nueva_cantidad" class="form-label">Nueva Cantidad</label>
                            <input type="number" class="form-control" id="nueva_cantidad" name="nueva_cantidad" min="0" required value="<?= $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] ?>">
                            <div class="form-text">Cantidad actual: <?= $inventario['INVENTARIO_CANTIDAD_DISPONIBLE'] ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo del Ajuste</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="2" required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-warning">Realizar Ajuste</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>