<?php
/**
 * Funciones para el módulo de facturación
 */
require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todas las facturas activas
 * @return array Lista de facturas
 */
function obtenerFacturasActivas() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_FACTURA_PKG.FACTURA_SELECCIONAR_ACTIVAS_SP(:cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    $estadoActivo = 1;
    oci_bind_by_name($stmt, ":estado", $estadoActivo);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    oci_execute($cursor);
    
    $facturas = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $facturas[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $facturas;
}

/**
 * Obtiene una factura por su ID
 * @param int $facturaId ID de la factura
 * @return array|null Datos de la factura o null si no existe
 */
function obtenerFacturaPorId($facturaId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_FACTURA_PKG.FACTURA_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $facturaId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $factura = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $factura = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    
    // Si hay factura, obtenemos sus detalles
    if ($factura) {
        $factura['DETALLES'] = obtenerDetallesFactura($facturaId, $conn);
    }
    
    oci_close($conn);
    return $factura;
}

/**
 * Obtiene los detalles de una factura
 * @param int $facturaId ID de la factura
 * @param resource $conn Conexión a Oracle (opcional)
 * @return array Detalles de la factura
 */
function obtenerDetallesFactura($facturaId, $conn = null) {
    $closeConn = false;
    if (!$conn) {
        $conn = getOracleConnection();
        $closeConn = true;
    }
    
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_DETALLE_FACTURA_PKG.DETALLE_FACTURA_SELECCIONAR_ACTIVOS_POR_FACTURA_SP(:factura_id, :cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        if ($closeConn) oci_close($conn);
        return [];
    }
    
    $estadoActivo = 1;
    oci_bind_by_name($stmt, ":factura_id", $facturaId);
    oci_bind_by_name($stmt, ":estado", $estadoActivo);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        if ($closeConn) oci_close($conn);
        return [];
    }
    
    oci_execute($cursor);
    
    $detalles = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $detalles[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    
    if ($closeConn) oci_close($conn);
    
    return $detalles;
}

/**
 * Obtiene pedidos pendientes de facturar
 * @return array Lista de pedidos
 */
function obtenerPedidosPendientesFacturar() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Suponiendo que existe un estado "Entregado" o "Listo para facturar"
    $estadoPendienteFacturar = 3; // Ajusta según tu esquema
    
    $sql = "BEGIN FIDE_PEDIDO_PKG.PEDIDO_SELECCIONAR_ACTIVOS_SP(:cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        oci_close($conn);
        return [];
    }
    
    oci_bind_by_name($stmt, ":estado", $estadoPendienteFacturar);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        oci_close($conn);
        return [];
    }
    
    oci_execute($cursor);
    
    $pedidos = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $pedidos[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $pedidos;
}

/**
 * Crea una factura a partir de un pedido
 * @param int $pedidoId ID del pedido
 * @param string $clienteId Cédula del cliente
 * @return int|false ID de la factura creada o false si hay error
 */
function crearFacturaDesdeOrdena($pedidoId, $clienteId) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // 1. Obtener los detalles del pedido
    $detallesPedido = obtenerDetallesPedido($pedidoId, $conn);
    if (empty($detallesPedido)) {
        oci_close($conn);
        return false;
    }
    
    // 2. Calcular totales
    $subtotal = 0;
    foreach ($detallesPedido as $detalle) {
        $subtotal += $detalle['CANTIDAD'] * $detalle['PRECIO_UNITARIO'];
    }
    
    $iva = $subtotal * 0.13; // 13% IVA en Costa Rica
    $total = $subtotal + $iva;
    
    // 3. Crear la factura - Iniciamos una transacción
    $facturaId = 0;
    
    // Crear factura
    $sql = "BEGIN FIDE_FACTURA_PKG.FACTURA_INSERTAR_SP(:fecha, :total, :iva, :estado, :cliente, :id_generado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        oci_close($conn);
        return false;
    }
    
    $fecha = date('Y-m-d');
    $estadoFactura = 1; // Activa/Emitida
    
    oci_bind_by_name($stmt, ":fecha", $fecha);
    oci_bind_by_name($stmt, ":total", $total);
    oci_bind_by_name($stmt, ":iva", $iva);
    oci_bind_by_name($stmt, ":estado", $estadoFactura);
    oci_bind_by_name($stmt, ":cliente", $clienteId);
    oci_bind_by_name($stmt, ":id_generado", $facturaId, 32, SQLT_INT);
    
    $result = oci_execute($stmt, OCI_DEFAULT); // No commit automático
    if (!$result) {
        oci_rollback($conn);
        oci_free_statement($stmt);
        oci_close($conn);
        return false;
    }
    
    oci_free_statement($stmt);
    
    // 4. Crear detalles de factura
    foreach ($detallesPedido as $detalle) {
        $detalleId = 0;
        $sql = "BEGIN FIDE_DETALLE_FACTURA_PKG.DETALLE_FACTURA_INSERTAR_SP(:factura, :articulo, :cantidad, :precio, :estado, :id_generado); END;";
        
        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            oci_rollback($conn);
            oci_close($conn);
            return false;
        }
        
        $estadoDetalle = 1; // Activo
        
        oci_bind_by_name($stmt, ":factura", $facturaId);
        oci_bind_by_name($stmt, ":articulo", $detalle['MENU_FK']);
        oci_bind_by_name($stmt, ":cantidad", $detalle['CANTIDAD']);
        oci_bind_by_name($stmt, ":precio", $detalle['PRECIO_UNITARIO']);
        oci_bind_by_name($stmt, ":estado", $estadoDetalle);
        oci_bind_by_name($stmt, ":id_generado", $detalleId, 32, SQLT_INT);
        
        $resultDetalle = oci_execute($stmt, OCI_DEFAULT); // No commit automático
        if (!$resultDetalle) {
            oci_rollback($conn);
            oci_free_statement($stmt);
            oci_close($conn);
            return false;
        }
        
        oci_free_statement($stmt);
    }
    
    // 5. Actualizar el estado del pedido a facturado
    $sql = "BEGIN FIDE_PEDIDO_PKG.PEDIDO_ACTUALIZAR_SP(:pedido_id, :fecha, :cedula, :factura_id, :tipo_entrega, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        oci_rollback($conn);
        oci_close($conn);
        return false;
    }
    
    $estadoPedido = 4; // Facturado (ajústar según tu esquema)
    
    // Necesitamos obtener los datos actuales del pedido primero
    $pedidoActual = obtenerPedidoPorId($pedidoId, $conn);
    if (!$pedidoActual) {
        oci_rollback($conn);
        oci_close($conn);
        return false;
    }
    
    oci_bind_by_name($stmt, ":pedido_id", $pedidoId);
    oci_bind_by_name($stmt, ":fecha", $pedidoActual['PEDIDO_FECHA']);
    oci_bind_by_name($stmt, ":cedula", $pedidoActual['PEDIDO_CEDULA_PERSO']);
    oci_bind_by_name($stmt, ":factura_id", $facturaId);
    oci_bind_by_name($stmt, ":tipo_entrega", $pedidoActual['PEDIDO_ID_TIPO_ENTREGA']);
    oci_bind_by_name($stmt, ":estado", $estadoPedido);
    
    $resultPedido = oci_execute($stmt, OCI_DEFAULT); // No commit automático
    if (!$resultPedido) {
        oci_rollback($conn);
        oci_free_statement($stmt);
        oci_close($conn);
        return false;
    }
    
    oci_free_statement($stmt);
    
    // 6. Si todo salió bien, hacemos commit
    oci_commit($conn);
    oci_close($conn);
    
    return $facturaId;
}

/**
 * Obtiene los detalles de un pedido
 * @param int $pedidoId ID del pedido
 * @param resource $conn Conexión a Oracle (opcional)
 * @return array Detalles del pedido
 */
function obtenerDetallesPedido($pedidoId, $conn = null) {
    $closeConn = false;
    if (!$conn) {
        $conn = getOracleConnection();
        $closeConn = true;
    }
    
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_DETALLE_PEDIDO_PKG.DETALLE_PEDIDO_SELECCIONAR_ACTIVOS_POR_PEDIDO_SP(:pedido_id, :cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        if ($closeConn) oci_close($conn);
        return [];
    }
    
    $estadoActivo = 1;
    oci_bind_by_name($stmt, ":pedido_id", $pedidoId);
    oci_bind_by_name($stmt, ":estado", $estadoActivo);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        if ($closeConn) oci_close($conn);
        return [];
    }
    
    oci_execute($cursor);
    
    $detalles = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $detalles[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    
    if ($closeConn) oci_close($conn);
    
    return $detalles;
}

/**
 * Obtiene un pedido por su ID
 * @param int $pedidoId ID del pedido
 * @param resource $conn Conexión a Oracle (opcional)
 * @return array|null Datos del pedido o null si no existe
 */
function obtenerPedidoPorId($pedidoId, $conn = null) {
    $closeConn = false;
    if (!$conn) {
        $conn = getOracleConnection();
        $closeConn = true;
    }
    
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_PEDIDO_PKG.PEDIDO_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        if ($closeConn) oci_close($conn);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $pedidoId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        if ($closeConn) oci_close($conn);
        return null;
    }
    
    oci_execute($cursor);
    
    $pedido = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $pedido = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    
    if ($closeConn) oci_close($conn);
    
    return $pedido;
}

/**
 * Anula una factura
 * @param int $facturaId ID de la factura
 * @return bool True si se anuló correctamente, False en caso contrario
 */
function anularFactura($facturaId) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // ID del estado "Anulado" en tu sistema
    $estadoAnulado = 3; // Ajusta según tu esquema
    
    $sql = "BEGIN FIDE_FACTURA_PKG.FACTURA_ANULAR_SP(:factura_id, :estado_anulado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        oci_close($conn);
        return false;
    }
    
    oci_bind_by_name($stmt, ":factura_id", $facturaId);
    oci_bind_by_name($stmt, ":estado_anulado", $estadoAnulado);
    
    $result = oci_execute($stmt);
    
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $result;
}
?>