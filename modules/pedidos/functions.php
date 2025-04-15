<?php
/**
 * Funciones para manejo del módulo de pedidos
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todos los pedidos activos
 * @param int $estadoId ID del estado a filtrar (opcional)
 * @return array Lista de pedidos
 */
function obtenerPedidosActivos($estadoId = null) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $params = [];
    if ($estadoId !== null) {
        $params = [$estadoId];
    } else {
        // Si no se especifica estado, obtener todos los activos
        $params = [1]; // Asumiendo que 1 = Activo
    }
    
    $pedidos = executeOracleCursorProcedure($conn, 'FIDE_PEDIDO_PKG', 'PEDIDO_SELECCIONAR_ACTIVOS_SP', $params);
    
    oci_close($conn);
    return $pedidos;
}

/**
 * Obtiene un pedido por su ID
 * @param int $id ID del pedido
 * @return array|null Datos del pedido o null si no existe
 */
function obtenerPedidoPorId($id) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $pedidos = executeOracleCursorProcedure($conn, 'FIDE_PEDIDO_PKG', 'PEDIDO_SELECCIONAR_POR_ID_SP', [$id]);
    
    oci_close($conn);
    
    if (empty($pedidos)) {
        return null;
    }
    
    return $pedidos[0];
}

/**
 * Obtiene los pedidos de una persona
 * @param string $cedula Cédula de la persona
 * @param int $estadoId ID del estado a filtrar (opcional)
 * @return array Lista de pedidos
 */
function obtenerPedidosPorPersona($cedula, $estadoId = null) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $params = [$cedula];
    if ($estadoId !== null) {
        $params[] = $estadoId;
    } else {
        // Si no se especifica estado, obtener todos los activos
        $params[] = 1; // Asumiendo que 1 = Activo
    }
    
    $pedidos = executeOracleCursorProcedure($conn, 'FIDE_PEDIDO_PKG', 'PEDIDO_SELECCIONAR_ACTIVOS_POR_PERSONA_SP', $params);
    
    oci_close($conn);
    return $pedidos;
}

/**
 * Inserta un nuevo pedido
 * @param array $datos Datos del pedido
 * @return int|false ID del pedido creado o false en caso de error
 */
function insertarPedido($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_PEDIDO_FECHA' => $datos['fecha'] ?? date('Y-m-d'),
        'V_PEDIDO_CEDULA_PERSO' => $datos['cedula_persona'],
        'V_PEDIDO_ID_FACTURA_FK' => $datos['factura_id'] ?? null,
        'V_PEDIDO_ID_TIPO_ENTREGA' => $datos['tipo_entrega_id'],
        'V_ESTADO_ID_FK' => $datos['estado_id'] ?? 1, // Estado inicial (pendiente)
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PEDIDO_PKG.PEDIDO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Actualiza un pedido existente
 * @param array $datos Datos actualizados del pedido
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarPedido($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_PEDIDO_ID_PK' => $datos['id'],
        'V_PEDIDO_FECHA' => $datos['fecha'],
        'V_PEDIDO_CEDULA_PERSO' => $datos['cedula_persona'],
        'V_PEDIDO_ID_FACTURA_FK' => $datos['factura_id'],
        'V_PEDIDO_ID_TIPO_ENTREGA' => $datos['tipo_entrega_id'],
        'V_ESTADO_ID_FK' => $datos['estado_id']
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PEDIDO_PKG.PEDIDO_ACTUALIZAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Anula un pedido por su ID
 * @param int $id ID del pedido
 * @return bool True si se anuló correctamente, False en caso contrario
 */
function anularPedido($id) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_PEDIDO_ID_PK' => $id,
        'V_ESTADO_ANULADO_ID' => 4 // Asumiendo que 4 = Anulado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PEDIDO_PKG.PEDIDO_ANULAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Obtiene los detalles de un pedido
 * @param int $pedidoId ID del pedido
 * @return array Lista de detalles
 */
function obtenerDetallesPedido($pedidoId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $detalles = executeOracleCursorProcedure($conn, 'FIDE_DETALLE_PEDIDO_PKG', 'DETALLE_PEDIDO_SELECCIONAR_ACTIVOS_POR_PEDIDO_SP', [$pedidoId, 1]);
    
    oci_close($conn);
    return $detalles;
}

/**
 * Inserta un nuevo detalle de pedido
 * @param array $datos Datos del detalle
 * @return int|false ID del detalle creado o false en caso de error
 */
function insertarDetallePedido($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_PEDIDO_FK' => $datos['pedido_id'],
        'V_MENU_FK' => $datos['menu_id'],
        'V_CANTIDAD' => $datos['cantidad'],
        'V_PRECIO_UNITARIO' => $datos['precio_unitario'],
        'V_ESTADO_ID_FK' => 1, // Activo
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_DETALLE_PEDIDO_PKG.DETALLE_PEDIDO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Obtiene todos los tipos de entrega activos
 * @return array Lista de tipos de entrega
 */
function obtenerTiposEntregaActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $tipos = executeOracleCursorProcedure($conn, 'FIDE_TIPO_ENTREGA_PKG', 'TIPO_ENTREGA_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $tipos;
}

/**
 * Obtiene todos los estados de pedido activos
 * @return array Lista de estados de pedido
 */
function obtenerEstadosPedidoActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $estados = executeOracleCursorProcedure($conn, 'FIDE_ESTADO_PEDIDO_PKG', 'ESTADO_PEDIDO_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $estados;
}

/**
 * Registra un seguimiento de pedido
 * @param array $datos Datos del seguimiento
 * @return int|false ID del seguimiento creado o false en caso de error
 */
function registrarSeguimientoPedido($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_PEDIDO_FK' => $datos['pedido_id'],
        'V_ESTADO_PEDIDO_FK' => $datos['estado_id'],
        'V_FECHA_CAMBIO' => $datos['fecha'] ?? date('Y-m-d'),
        'V_COMENTARIO' => $datos['comentario'] ?? null,
        'V_ESTADO_ID_FK' => 1, // Activo
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_SEGUIMIENTO_PEDIDO_PKG.SEGUIMIENTO_PEDIDO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Obtiene el historial de seguimiento de un pedido
 * @param int $pedidoId ID del pedido
 * @return array Historial de seguimiento
 */
function obtenerSeguimientoPedido($pedidoId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $seguimiento = executeOracleCursorProcedure($conn, 'FIDE_SEGUIMIENTO_PEDIDO_PKG', 'SEGUIMIENTO_PEDIDO_SELECCIONAR_ACTIVOS_POR_PEDIDO_SP', [$pedidoId, 1]);
    
    oci_close($conn);
    return $seguimiento;
}

/**
 * Cambiar el estado de un pedido y registrar el seguimiento
 * @param int $pedidoId ID del pedido
 * @param int $estadoId ID del nuevo estado
 * @param string $comentario Comentario sobre el cambio
 * @return bool True si se realizó correctamente, False en caso contrario
 */
function cambiarEstadoPedido($pedidoId, $estadoId, $comentario) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // Obtener el pedido actual
    $pedido = obtenerPedidoPorId($pedidoId);
    if (!$pedido) {
        return false;
    }
    
    // Actualizar el estado del pedido
    $datosPedido = [
        'id' => $pedidoId,
        'fecha' => $pedido['PEDIDO_FECHA'],
        'cedula_persona' => $pedido['PEDIDO_CEDULA_PERSO'],
        'factura_id' => $pedido['PEDIDO_ID_FACTURA_FK'],
        'tipo_entrega_id' => $pedido['PEDIDO_ID_TIPO_ENTREGA'],
        'estado_id' => $estadoId
    ];
    
    $resultadoActualizar = actualizarPedido($datosPedido);
    
    if (!$resultadoActualizar) {
        return false;
    }
    
    // Registrar el seguimiento
    $datosSeguimiento = [
        'pedido_id' => $pedidoId,
        'estado_id' => $estadoId,
        'fecha' => date('Y-m-d'),
        'comentario' => $comentario
    ];
    
    $resultadoSeguimiento = registrarSeguimientoPedido($datosSeguimiento);
    
    return $resultadoSeguimiento !== false;
}
