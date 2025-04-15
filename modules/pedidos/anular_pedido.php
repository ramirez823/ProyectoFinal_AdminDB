<?php
/**
 * Procesador para anular un pedido
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

// Obtener datos del pedido para verificar que existe y su estado
$pedido = obtenerPedidoPorId($pedidoId);
if (!$pedido) {
    // Si el pedido no existe, redirigir al listado
    header('Location: index.php');
    exit;
}

// Verificar que el pedido esté en un estado que permita anulación (generalmente solo pendiente)
$estadosPedido = obtenerEstadosPedidoActivos();
$nombreEstadoActual = "Desconocido";

foreach ($estadosPedido as $estado) {
    if ($estado['ESTADO_PEDIDO_ID_PK'] == $pedido['ESTADO_ID_FK']) {
        $nombreEstadoActual = $estado['ESTADO_PEDIDO_NOMBRE'];
        break;
    }
}

// Si el pedido ya está anulado o entregado, no permitir anulación
if (strtoupper($nombreEstadoActual) === 'ANULADO' || strtoupper($nombreEstadoActual) === 'ENTREGADO') {
    // Redirigir con mensaje de error
    header('Location: ver_pedido.php?id=' . $pedidoId . '&error=1&mensaje=' . urlencode('No se puede anular un pedido en estado ' . $nombreEstadoActual));
    exit;
}

// Definir el estado de anulado (asumiendo que es 4)
$estadoAnulado = 4;

// Buscar el ID del estado "ANULADO"
foreach ($estadosPedido as $estado) {
    if (strtoupper($estado['ESTADO_PEDIDO_NOMBRE']) === 'ANULADO') {
        $estadoAnulado = $estado['ESTADO_PEDIDO_ID_PK'];
        break;
    }
}

// Anular el pedido
$resultado = cambiarEstadoPedido(
    $pedidoId, 
    $estadoAnulado, 
    'Pedido anulado por el usuario ' . ($_SESSION['user_name'] ?? 'sistema')
);

if ($resultado) {
    // Registrar la acción en el log
    registrarLog('Anulación de pedido', "Se anuló el pedido #$pedidoId");
    
    // Redirigir con mensaje de éxito
    header('Location: ver_pedido.php?id=' . $pedidoId . '&success=1&mensaje=' . urlencode('Pedido anulado correctamente'));
    exit;
} else {
    // Redirigir con mensaje de error
    header('Location: ver_pedido.php?id=' . $pedidoId . '&error=1&mensaje=' . urlencode('Error al anular el pedido'));
    exit;
}
?>