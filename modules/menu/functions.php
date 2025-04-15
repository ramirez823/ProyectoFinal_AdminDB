<?php
/**
 * Funciones para manejo del módulo de menú
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todos los ítems de menú activos
 * @return array Lista de ítems de menú
 */
function obtenerMenuActivo() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_MENU_PKG y su procedimiento MENU_SELECCIONAR_DISPONIBLES_ACTIVOS_SP
    $menu = executeOracleCursorProcedure($conn, 'FIDE_MENU_PKG', 'MENU_SELECCIONAR_DISPONIBLES_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $menu;
}

/**
 * Obtiene un ítem de menú por su ID
 * @param int $id ID del ítem de menú
 * @return array|null Datos del ítem o null si no existe
 */
function obtenerMenuPorId($id) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    // Utilizamos el paquete FIDE_MENU_PKG y su procedimiento MENU_SELECCIONAR_POR_ID_SP
    $menu = executeOracleCursorProcedure($conn, 'FIDE_MENU_PKG', 'MENU_SELECCIONAR_POR_ID_SP', [$id]);
    
    oci_close($conn);
    
    // Si no hay resultados, retornamos null
    if (empty($menu)) {
        return null;
    }
    
    // Retornamos el primer elemento del array
    return $menu[0];
}

/**
 * Inserta un nuevo ítem de menú
 * @param array $datos Datos del ítem de menú
 * @return int|false ID generado si se insertó correctamente, False en caso contrario
 */
function insertarMenu($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // Variable para almacenar el ID generado
    $idGenerado = 0;
    
    $params = [
        'V_MENU_NOMBRE' => $datos['nombre'],
        'V_MENU_PRECIO' => $datos['precio'],
        'V_MENU_DISPONIBILIDAD' => $datos['disponibilidad'],
        'V_ESTADO_ID_FK' => $datos['estado_id'] ?? 1,
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    // Ejecutar el procedimiento
    $stmt = oci_parse($conn, 'BEGIN FIDE_MENU_PKG.MENU_INSERTAR_SP(:V_MENU_NOMBRE, :V_MENU_PRECIO, :V_MENU_DISPONIBILIDAD, :V_ESTADO_ID_FK, :V_ID_GENERADO); END;');
    
    // Bind de parámetros
    oci_bind_by_name($stmt, ':V_MENU_NOMBRE', $params['V_MENU_NOMBRE']);
    oci_bind_by_name($stmt, ':V_MENU_PRECIO', $params['V_MENU_PRECIO']);
    oci_bind_by_name($stmt, ':V_MENU_DISPONIBILIDAD', $params['V_MENU_DISPONIBILIDAD']);
    oci_bind_by_name($stmt, ':V_ESTADO_ID_FK', $params['V_ESTADO_ID_FK']);
    oci_bind_by_name($stmt, ':V_ID_GENERADO', $idGenerado, 32);
    
    $result = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    
    if ($result) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Actualiza un ítem de menú existente
 * @param array $datos Datos actualizados del ítem de menú
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarMenu($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_MENU_ID_PK' => $datos['id'],
        'V_MENU_NOMBRE' => $datos['nombre'],
        'V_MENU_PRECIO' => $datos['precio'],
        'V_MENU_DISPONIBILIDAD' => $datos['disponibilidad'],
        'V_ESTADO_ID_FK' => $datos['estado_id']
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_MENU_PKG.MENU_ACTUALIZAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Desactiva un ítem de menú
 * @param int $id ID del ítem de menú
 * @return bool True si se desactivó correctamente, False en caso contrario
 */
function desactivarMenu($id) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_MENU_ID_PK' => $id,
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_MENU_PKG.MENU_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Obtiene todas las categorías de menú activas
 * @return array Lista de categorías
 */
function obtenerCategoriasMenuActivas() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_CATEGORIA_MENU_PKG y su procedimiento CATEGORIA_MENU_SELECCIONAR_ACTIVOS_SP
    $categorias = executeOracleCursorProcedure($conn, 'FIDE_CATEGORIA_MENU_PKG', 'CATEGORIA_MENU_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $categorias;
}

/**
 * Obtiene una categoría de menú por su ID
 * @param int $id ID de la categoría
 * @return array|null Datos de la categoría o null si no existe
 */
function obtenerCategoriaMenuPorId($id) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    // Utilizamos el paquete FIDE_CATEGORIA_MENU_PKG y su procedimiento CATEGORIA_MENU_SELECCIONAR_POR_ID_SP
    $categorias = executeOracleCursorProcedure($conn, 'FIDE_CATEGORIA_MENU_PKG', 'CATEGORIA_MENU_SELECCIONAR_POR_ID_SP', [$id]);
    
    oci_close($conn);
    
    // Si no hay resultados, retornamos null
    if (empty($categorias)) {
        return null;
    }
    
    // Retornamos el primer elemento del array
    return $categorias[0];
}

/**
 * Obtiene todos los ítems de menú disponibles para pedidos
 * Filtrado para mostrar solo los que están activos y disponibles
 * 
 * @return array Lista de ítems de menú disponibles
 */
function obtenerMenuDisponible() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el mismo procedimiento que ya tienes, que devuelve los menús activos y disponibles
    $menu = executeOracleCursorProcedure($conn, 'FIDE_MENU_PKG', 'MENU_SELECCIONAR_DISPONIBLES_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $menu;
}

/**
 * Inserta una nueva categoría de menú
 * @param array $datos Datos de la categoría
 * @return int|false ID generado si se insertó correctamente, False en caso contrario
 */
function insertarCategoriaMenu($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // Variable para almacenar el ID generado
    $idGenerado = 0;
    
    $params = [
        'V_CATEGORIA_MENU_NOMBRE' => $datos['nombre'],
        'V_CATEGORIA_MENU_DESCRIPCION' => $datos['descripcion'] ?? null,
        'V_ESTADO_ID_FK' => $datos['estado_id'] ?? 1,
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    // Ejecutar el procedimiento
    $stmt = oci_parse($conn, 'BEGIN FIDE_CATEGORIA_MENU_PKG.CATEGORIA_MENU_INSERTAR_SP(:V_CATEGORIA_MENU_NOMBRE, :V_CATEGORIA_MENU_DESCRIPCION, :V_ESTADO_ID_FK, :V_ID_GENERADO); END;');
    
    // Bind de parámetros
    oci_bind_by_name($stmt, ':V_CATEGORIA_MENU_NOMBRE', $params['V_CATEGORIA_MENU_NOMBRE']);
    oci_bind_by_name($stmt, ':V_CATEGORIA_MENU_DESCRIPCION', $params['V_CATEGORIA_MENU_DESCRIPCION']);
    oci_bind_by_name($stmt, ':V_ESTADO_ID_FK', $params['V_ESTADO_ID_FK']);
    oci_bind_by_name($stmt, ':V_ID_GENERADO', $idGenerado, 32);
    
    $result = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    
    if ($result) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Actualiza una categoría de menú existente
 * @param array $datos Datos actualizados de la categoría
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarCategoriaMenu($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_CATEGORIA_MENU_ID_PK' => $datos['id'],
        'V_CATEGORIA_MENU_NOMBRE' => $datos['nombre'],
        'V_CATEGORIA_MENU_DESCRIPCION' => $datos['descripcion'] ?? null,
        'V_ESTADO_ID_FK' => $datos['estado_id']
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_CATEGORIA_MENU_PKG.CATEGORIA_MENU_ACTUALIZAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Desactiva una categoría de menú
 * @param int $id ID de la categoría
 * @return bool True si se desactivó correctamente, False en caso contrario
 */
function desactivarCategoriaMenu($id) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_CATEGORIA_MENU_ID_PK' => $id,
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_CATEGORIA_MENU_PKG.CATEGORIA_MENU_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Obtiene los detalles de un ítem de menú
 * @param int $menuId ID del ítem de menú
 * @return array Detalles del ítem de menú
 */
function obtenerDetallesMenu($menuId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_MENU_DETAILE_PKG y su procedimiento MENU_DETAILE_SELECCIONAR_ACTIVOS_POR_MENU_SP
    $detalles = executeOracleCursorProcedure($conn, 'FIDE_MENU_DETAILE_PKG', 'MENU_DETAILE_SELECCIONAR_ACTIVOS_POR_MENU_SP', [$menuId, 1]);
    
    oci_close($conn);
    return $detalles;
}

/**
 * Obtiene las categorías asignadas a un ítem de menú
 * @param int $menuId ID del ítem de menú
 * @return array Categorías asignadas
 */
function obtenerCategoriasDeMenu($menuId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_MENU_CATEGORIA_PKG y su procedimiento MENU_CATEGORIA_SELEC_CAT_ACT_POR_MENU_SP
    $categorias = executeOracleCursorProcedure($conn, 'FIDE_MENU_CATEGORIA_PKG', 'MENU_CATEGORIA_SELEC_CAT_ACT_POR_MENU_SP', [$menuId, 1]);
    
    oci_close($conn);
    return $categorias;
}

/**
 * Asigna una categoría a un ítem de menú
 * @param int $menuId ID del ítem de menú
 * @param int $categoriaId ID de la categoría
 * @return bool True si se asignó correctamente, False en caso contrario
 */
function asignarCategoriaAMenu($menuId, $categoriaId) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_MENU_FK' => $menuId,
        'V_CATEGORIA_MENU_FK' => $categoriaId,
        'V_ESTADO_ID_FK' => 1 // Estado activo
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_MENU_CATEGORIA_PKG.MENU_CATEGORIA_INSERTAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Desasigna una categoría de un ítem de menú
 * @param int $menuId ID del ítem de menú
 * @param int $categoriaId ID de la categoría
 * @return bool True si se desasignó correctamente, False en caso contrario
 */
function desasignarCategoriaDeMenu($menuId, $categoriaId) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_MENU_FK' => $menuId,
        'V_CATEGORIA_MENU_FK' => $categoriaId,
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_MENU_CATEGORIA_PKG.MENU_CATEGORIA_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $success;
}

/**
 * Obtiene todos los artículos activos para usar en detalles de menú
 * @return array Lista de artículos
 */
function obtenerArticulosParaMenu() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_ARTICULO_PKG y su procedimiento ARTICULO_SELECCIONAR_ACTIVOS_SP
    $articulos = executeOracleCursorProcedure($conn, 'FIDE_ARTICULO_PKG', 'ARTICULO_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $articulos;
}

/**
 * Inserta un detalle de menú
 * @param array $datos Datos del detalle
 * @return int|false ID generado si se insertó correctamente, False en caso contrario
 */
function insertarDetalleMenu($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    // Variable para almacenar el ID generado
    $idGenerado = 0;
    
    $params = [
        'V_MENU_FK' => $datos['menu_id'],
        'V_ARTICULO_FK' => $datos['articulo_id'],
        'V_MENU_DETALLE_CANTIDAD' => $datos['cantidad'],
        'V_MENU_DETALLE_PRECIO_UNITARIO' => $datos['precio_unitario'],
        'V_ESTADO_ID_FK' => $datos['estado_id'] ?? 1,
        'V_ID_GENERADO' => &$idGenerado
    ];
    
    // Ejecutar el procedimiento
    $stmt = oci_parse($conn, 'BEGIN FIDE_MENU_DETAILE_PKG.MENU_DETAILE_INSERTAR_SP(:V_MENU_FK, :V_ARTICULO_FK, :V_MENU_DETALLE_CANTIDAD, :V_MENU_DETALLE_PRECIO_UNITARIO, :V_ESTADO_ID_FK, :V_ID_GENERADO); END;');
    
    // Bind de parámetros
    oci_bind_by_name($stmt, ':V_MENU_FK', $params['V_MENU_FK']);
    oci_bind_by_name($stmt, ':V_ARTICULO_FK', $params['V_ARTICULO_FK']);
    oci_bind_by_name($stmt, ':V_MENU_DETALLE_CANTIDAD', $params['V_MENU_DETALLE_CANTIDAD']);
    oci_bind_by_name($stmt, ':V_MENU_DETALLE_PRECIO_UNITARIO', $params['V_MENU_DETALLE_PRECIO_UNITARIO']);
    oci_bind_by_name($stmt, ':V_ESTADO_ID_FK', $params['V_ESTADO_ID_FK']);
    oci_bind_by_name($stmt, ':V_ID_GENERADO', $idGenerado, 32);
    
    $result = oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);
    
    if ($result) {
        return $idGenerado;
    }
    
    return false;
}

/**
 * Elimina un detalle de menú
 * @param int $id ID del detalle
 * @return bool True si se eliminó correctamente, False en caso contrario
 */
function eliminarDetalleMenu($id) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_MENU_DETALLE_ID_PK' => $id,
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    // Ejecutar el procedimiento
    $success = executeOracleProcedure($conn, 'FIDE_MENU_DETAILE_PKG.MENU_DETAILE_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $success;
}
?>