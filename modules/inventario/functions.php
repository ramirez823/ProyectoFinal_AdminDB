<?php
/**
 * Funciones para manejo del módulo de inventario
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todos los artículos activos
 * @return array Lista de artículos
 */
function obtenerArticulosActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $articulos = executeOracleCursorProcedure($conn, 'FIDE_ARTICULO_PKG', 'ARTICULO_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $articulos;
}

/**
 * Obtiene un artículo por su ID
 * @param int $id ID del artículo
 * @return array|null Datos del artículo o null si no existe
 */
function obtenerArticuloPorId($id) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $articulos = executeOracleCursorProcedure($conn, 'FIDE_ARTICULO_PKG', 'ARTICULO_SELECCIONAR_POR_ID_SP', [$id]);
    
    oci_close($conn);
    
    if (empty($articulos)) {
        return null;
    }
    
    return $articulos[0];
}

/**
 * Inserta un nuevo artículo
 * @param array $datos Datos del artículo
 * @return int|false ID del artículo creado o false en caso de error
 */
function insertarArticulo($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_ARTICULO_NOMBRE' => $datos['nombre'],
        'V_ARTICULO_PRECIO' => $datos['precio'],
        'V_TIPO_ARTICULO_FK' => $datos['tipo_id'],
        'V_PROVEEDOR_FK' => $datos['proveedor_id'],
        'V_ESTADO_ID_FK' => 1, // Activo
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_ARTICULO_PKG.ARTICULO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Actualiza un artículo existente
 * @param array $datos Datos actualizados del artículo
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarArticulo($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_ARTICULO_ID_PK' => $datos['id'],
        'V_ARTICULO_NOMBRE' => $datos['nombre'],
        'V_ARTICULO_PRECIO' => $datos['precio'],
        'V_TIPO_ARTICULO_FK' => $datos['tipo_id'],
        'V_PROVEEDOR_FK' => $datos['proveedor_id'],
        'V_ESTADO_ID_FK' => $datos['estado_id']
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_ARTICULO_PKG.ARTICULO_ACTUALIZAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Desactiva un artículo por su ID
 * @param int $id ID del artículo
 * @return bool True si se desactivó correctamente, False en caso contrario
 */
function desactivarArticulo($id) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_ARTICULO_ID_PK' => $id,
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_ARTICULO_PKG.ARTICULO_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Obtiene todos los tipos de artículo activos
 * @return array Lista de tipos de artículo
 */
function obtenerTiposArticuloActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $tipos = executeOracleCursorProcedure($conn, 'FIDE_TIPO_ARTICULO_PKG', 'TIPO_ARTICULO_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $tipos;
}

/**
 * Obtiene todos los proveedores activos
 * @return array Lista de proveedores
 */
function obtenerProveedoresActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $proveedores = executeOracleCursorProcedure($conn, 'FIDE_PROVEEDOR_PKG', 'PROVEEDOR_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $proveedores;
}

/**
 * Obtiene todos los registros de inventario activos
 * @return array Lista de registros de inventario
 */
function obtenerInventarioActivo() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $inventario = executeOracleCursorProcedure($conn, 'FIDE_INVENTARIO_PKG', 'INVENTARIO_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $inventario;
}

/**
 * Obtiene un registro de inventario por su ID
 * @param int $id ID del registro de inventario
 * @return array|null Datos del registro de inventario o null si no existe
 */
function obtenerInventarioPorId($id) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $inventario = executeOracleCursorProcedure($conn, 'FIDE_INVENTARIO_PKG', 'INVENTARIO_SELECCIONAR_POR_ID_SP', [$id]);
    
    oci_close($conn);
    
    if (empty($inventario)) {
        return null;
    }
    
    return $inventario[0];
}

/**
 * Inserta un nuevo registro de inventario
 * @param array $datos Datos del registro de inventario
 * @return int|false ID del registro creado o false en caso de error
 */
function insertarInventario($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_INVENTARIO_CANTIDAD_DISPONIBLE' => $datos['cantidad_disponible'],
        'V_INVENTARIO_CANTIDAD_MINIMA' => $datos['cantidad_minima'],
        'V_INVENTARIO_FECHA_INGRESO' => $datos['fecha_ingreso'],
        'V_INVENTARIO_PRECIO' => $datos['precio'],
        'V_ARTICULO_FK' => $datos['articulo_id'],
        'V_ESTANTE_FK' => $datos['estante_id'],
        'V_ESTADO_ID_FK' => 1, // Activo
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_INVENTARIO_PKG.INVENTARIO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Ajusta la cantidad de un producto en inventario
 * @param int $id ID del registro de inventario
 * @param int $nuevaCantidad Nueva cantidad disponible
 * @return bool True si se ajustó correctamente, False en caso contrario
 */
function ajustarCantidadInventario($id, $nuevaCantidad) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_INVENTARIO_ID_INVENTARIO_PK' => $id,
        'V_NUEVA_CANTIDAD_DISPONIBLE' => $nuevaCantidad
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_INVENTARIO_PKG.INVENTARIO_AJUSTAR_CANTIDAD_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Registra un movimiento de inventario
 * @param array $datos Datos del movimiento
 * @return int|false ID del movimiento creado o false en caso de error
 */
function registrarMovimientoInventario($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $idGenerado = null;
    
    $params = [
        'V_MOVIMIENTOS_INVENTARIO_TIPO' => $datos['tipo'],
        'V_MOVIMIENTOS_INVENTARIO_CANTIDAD' => $datos['cantidad'],
        'V_MOVIMIENTOS_INVENTARIO_FECHA' => $datos['fecha'],
        'V_INVENTARIO_FK' => $datos['inventario_id'],
        'V_ESTADO_ID_FK' => 1, // Activo
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_MOVIMIENTOS_INVENTARIO_PKG.MOVIMIENTOS_INVENTARIO_INSERTAR_SP', $params);
    
    oci_close($conn);
    
    if ($result && $idGenerado) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Obtiene todos los estantes activos
 * @return array Lista de estantes
 */
function obtenerEstantesActivos() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $estantes = executeOracleCursorProcedure($conn, 'FIDE_ESTANTE_PKG', 'ESTANTE_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $estantes;
}

/**
 * Obtiene los registros de vencimiento activos para un artículo
 * @param int $inventarioId ID del registro de inventario
 * @return array Lista de vencimientos
 */
function obtenerVencimientosPorInventario($inventarioId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $vencimientos = executeOracleCursorProcedure($conn, 'FIDE_VENCIMIENTO_PKG', 'VENCIMIENTO_SELECCIONAR_ACTIVOS_POR_INVENTARIO_SP', [$inventarioId, 1]);
    
    oci_close($conn);
    return $vencimientos;
}