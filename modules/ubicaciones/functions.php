<?php
/**
 * Funciones para el módulo de direcciones
 */
require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todas las direcciones activas
 * @return array Lista de direcciones
 */
function obtenerDireccionesActivas() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_SELECCIONAR_ACTIVOS_SP(:cursor, :estado); END;";
    
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
    
    $direcciones = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $direcciones[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $direcciones;
}

/**
 * Obtiene una dirección por su ID
 * @param int $direccionId ID de la dirección
 * @return array|null Datos de la dirección o null si no existe
 */
function obtenerDireccionPorId($direccionId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $direccionId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $direccion = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $direccion = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $direccion;
}

/**
 * Obtiene todas las provincias activas
 * @return array Lista de provincias
 */
function obtenerProvinciasActivas() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_PROVINCIA_PKG.PROVINCIA_SELECCIONAR_ACTIVOS_SP(:cursor, :estado); END;";
    
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
    
    $provincias = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $provincias[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $provincias;
}

/**
 * Obtiene cantones por provincia
 * @param int $provinciaId ID de la provincia
 * @return array Lista de cantones
 */
function obtenerCantonesPorProvincia($provinciaId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_CANTON_PKG.CANTON_SELECCIONAR_ACTIVOS_POR_PROVINCIA_SP(:provincia_id, :cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    $estadoActivo = 1;
    oci_bind_by_name($stmt, ":provincia_id", $provinciaId);
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
    
    $cantones = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $cantones[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $cantones;
}

/**
 * Obtiene distritos por cantón
 * @param int $cantonId ID del cantón
 * @return array Lista de distritos
 */
function obtenerDistritosPorCanton($cantonId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    $sql = "BEGIN FIDE_DISTRITO_PKG.DISTRITO_SELECCIONAR_ACTIVOS_POR_CANTON_SP(:canton_id, :cursor, :estado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    $estadoActivo = 1;
    oci_bind_by_name($stmt, ":canton_id", $cantonId);
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
    
    $distritos = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $distritos[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $distritos;
}

/**
 * Inserta una nueva dirección
 * @param array $datos Datos de la dirección
 * @return int|false ID de la dirección creada o false si hay error
 */
function insertarDireccion($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_INSERTAR_SP(:provincia_id, :canton_id, :distrito_id, :sennas, :estado_id, :id_generado); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    $direccionId = 0;
    
    oci_bind_by_name($stmt, ":provincia_id", $datos['provincia_id']);
    oci_bind_by_name($stmt, ":canton_id", $datos['canton_id']);
    oci_bind_by_name($stmt, ":distrito_id", $datos['distrito_id']);
    oci_bind_by_name($stmt, ":sennas", $datos['sennas']);
    oci_bind_by_name($stmt, ":estado_id", $datos['estado_id']);
    oci_bind_by_name($stmt, ":id_generado", $direccionId, 32, SQLT_INT);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $direccionId;
}

/**
 * Actualiza una dirección existente
 * @param array $datos Datos actualizados de la dirección
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarDireccion($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_ACTUALIZAR_SP(:id_direccion, :provincia_id, :canton_id, :distrito_id, :sennas, :estado_id); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    oci_bind_by_name($stmt, ":id_direccion", $datos['id_direccion']);
    oci_bind_by_name($stmt, ":provincia_id", $datos['provincia_id']);
    oci_bind_by_name($stmt, ":canton_id", $datos['canton_id']);
    oci_bind_by_name($stmt, ":distrito_id", $datos['distrito_id']);
    oci_bind_by_name($stmt, ":sennas", $datos['sennas']);
    oci_bind_by_name($stmt, ":estado_id", $datos['estado_id']);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    oci_free_statement($stmt);
    oci_close($conn);
    
    return true;
}

/**
 * Desactiva una dirección
 * @param int $direccionId ID de la dirección
 * @return bool True si se desactivó correctamente, False en caso contrario
 */
function desactivarDireccion($direccionId) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_DESACTIVAR_SP(:id_direccion, :estado_inactivo); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    $estadoInactivo = 2;
    
    oci_bind_by_name($stmt, ":id_direccion", $direccionId);
    oci_bind_by_name($stmt, ":estado_inactivo", $estadoInactivo);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    oci_free_statement($stmt);
    oci_close($conn);
    
    return true;
}

/**
 * Obtiene las personas asociadas a una dirección
 * @param int $direccionId ID de la dirección
 * @return array Lista de personas
 */
function obtenerPersonasPorDireccion($direccionId) {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Esta consulta depende de cómo esté estructurada tu base de datos
    // Aquí se asume que hay una relación entre FIDE_PERSONAS_TB y direcciones
    $sql = "SELECT p.PERSONAS_CEDULA_PERSONA_PK, p.PERSONAS_NOMBRE, p.PERSONAS_APELLIDO1, p.PERSONAS_APELLIDO2, p.ESTADO_ID_FK
            FROM FIDE_PERSONAS_TB p
            WHERE p.PERSONAS_ID_DIRECCION_FK = :direccion_id";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    oci_bind_by_name($stmt, ":direccion_id", $direccionId);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    $personas = [];
    while (($row = oci_fetch_assoc($stmt)) !== false) {
        $personas[] = $row;
    }
    
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $personas;

}
/**
 * Obtiene una provincia por su ID
 * @param int $provinciaId ID de la provincia
 * @return array|null Datos de la provincia o null si no existe
 */
function obtenerProvinciaPorId($provinciaId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_PROVINCIA_PKG.PROVINCIA_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $provinciaId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $provincia = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $provincia = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $provincia;
}

/**
 * Obtiene un cantón por su ID
 * @param int $cantonId ID del cantón
 * @return array|null Datos del cantón o null si no existe
 */
function obtenerCantonPorId($cantonId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_CANTON_PKG.CANTON_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $cantonId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $canton = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $canton = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $canton;
}

/**
 * Obtiene un distrito por su ID
 * @param int $distritoId ID del distrito
 * @return array|null Datos del distrito o null si no existe
 */
function obtenerDistritoPorId($distritoId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_DISTRITO_PKG.DISTRITO_SELECCIONAR_POR_ID_SP(:id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":id", $distritoId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $distrito = null;
    if (($row = oci_fetch_assoc($cursor)) !== false) {
        $distrito = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return $distrito;
}
?>