<?php
/**
 * Formulario para actualizar el estado de un pedido
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';

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

// Obtener estados de pedido disponibles
$estadosPedido = obtenerEstadosPedidoActivos();

// Buscar el nombre del estado actual
$estadoActualId = $pedido['ESTADO_ID_FK'];
$nombreEstadoActual = "Desconocido";

foreach ($estadosPedido as $estado) {
    if ($estado['ESTADO_PEDIDO_ID_PK'] == $estadoActualId) {
        $nombreEstadoActual = $estado['ESTADO_PEDIDO_NOMBRE'];
        break;
    }
}

// Variable para almacenar mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entradas
    $nuevoEstadoId = intval($_POST['estado_id'] ?? 0);
    $comentario = sanitizeInput($_POST['comentario'] ?? '');
    
    // Validación básica
    $errores = [];
    
    if ($nuevoEstadoId <= 0) {
        $errores[] = "Debe seleccionar un estado válido";
    }
    
    if (empty($comentario)) {
        $errores[] = "Debe proporcionar un comentario";
    }
    
    // Si no hay errores, actualizar el estado
    if (empty($errores)) {
        $resultado = cambiarEstadoPedido($pedidoId, $nuevoEstadoId, $comentario);
        
        if ($resultado) {
            $mensaje = "Estado actualizado correctamente";
            $tipoMensaje = "success";
            
            // Registrar la acción en el log
            registrarLog('Actualización de estado de pedido', "Se actualizó el estado del pedido #$pedidoId a estado #$nuevoEstadoId");
            
            // Redireccionar después de 2 segundos
            header("refresh:2;url=ver_pedido.php?id=$pedidoId");
        } else {
            $mensaje = "Error al actualizar el estado";
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
        <h1>Actualizar Estado de Pedido #<?= $pedidoId ?></h1>
        <a href="ver_pedido.php?id=<?= $pedidoId ?>" class="btn btn-secondary">Volver al pedido</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cambio de Estado</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>Estado actual:</strong> <span class="badge <?= strtoupper($nombreEstadoActual) === 'ANULADO' ? 'bg-danger' : (strtoupper($nombreEstadoActual) === 'ENTREGADO' ? 'bg-success' : 'bg-warning') ?>"><?= htmlspecialchars($nombreEstadoActual) ?></span></p>
                    </div>
                    
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $pedidoId ?>">
                        <div class="mb-3">
                            <label for="estado_id" class="form-label">Nuevo Estado</label>
                            <select class="form-select" id="estado_id" name="estado_id" required>
                                <option value="">Seleccione un estado</option>
                                <?php foreach ($estadosPedido as $estado): ?>
                                    <?php if ($estado['ESTADO_PEDIDO_ID_PK'] != $estadoActualId): ?>
                                        <option value="<?= $estado['ESTADO_PEDIDO_ID_PK'] ?>">
                                            <?= htmlspecialchars($estado['ESTADO_PEDIDO_NOMBRE']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                            <div class="form-text">Explique el motivo del cambio de estado</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" onclick="window.location='ver_pedido.php?id=<?= $pedidoId ?>'">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Flujo de Estados</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Nota:</strong> Los estados siguen una secuencia lógica. Asegúrese de seguir el flujo correcto.
                    </div>
                    
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Pendiente</div>
                                El pedido ha sido creado pero no se ha iniciado su preparación
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">En Preparación</div>
                                El pedido está siendo preparado en cocina
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Listo</div>
                                El pedido está listo para ser entregado o retirado
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Entregado</div>
                                El pedido ha sido entregado al cliente
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start list-group-item-danger">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Anulado</div>
                                El pedido ha sido cancelado
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>