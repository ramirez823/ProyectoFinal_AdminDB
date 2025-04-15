<?php
/**
 * Funciones para manejo del módulo de personas
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Obtiene todas las personas activas
 * @return array Lista de personas
 */
function obtenerPersonasActivas() {
    $conn = getOracleConnection();
    if (!$conn) return [];
    
    // Utilizamos el paquete FIDE_PERSONAS_PKG y su procedimiento PERSONAS_SELECCIONAR_ACTIVOS_SP
    $personas = executeOracleCursorProcedure($conn, 'FIDE_PERSONAS_PKG', 'PERSONAS_SELECCIONAR_ACTIVOS_SP', [1]);
    
    oci_close($conn);
    return $personas;
}

/**
 * Obtiene una persona por su cédula
 * @param string $cedula Cédula de la persona
 * @return array|null Datos de la persona o null si no existe
 */
function obtenerPersonaPorCedula($cedula) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    // Usamos la función específica para este procedimiento
    $personas = ejecutarPersonasSeleccionarPorId($conn, $cedula);
    
    oci_close($conn);
    
    // Si no hay resultados, retornamos null
    if (empty($personas)) {
        return null;
    }
    
    // Retornamos el primer elemento del array
    return $personas[0];
}

/**
 * Ejecuta el procedimiento TIPO_PERSONA_SELECCIONAR_POR_ID_SP
 * @param resource $conn Conexión Oracle
 * @param int $tipoPersonaId ID del tipo de persona
 * @return array Datos del tipo de persona
 */
function ejecutarTipoPersonaSeleccionarPorId($conn, $tipoPersonaId) {
    $sql = "BEGIN FIDE_TIPO_PERSONA_PKG.TIPO_PERSONA_SELECCIONAR_POR_ID_SP(:tipoid, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Bind the ID parameter
    oci_bind_by_name($stmt, ":tipoid", $tipoPersonaId);
    
    // Bind the cursor
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Execute the cursor
    oci_execute($cursor);
    
    // Fetch all rows from the cursor
    $data = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $data[] = $row;
    }
    
    // Free resources
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    
    return $data;
}

/**
 * Obtiene un tipo de persona por su ID
 * @param int $tipoPersonaId ID del tipo de persona
 * @return array|null Datos del tipo de persona o null si no existe
 */
function obtenerTipoPersonaPorId($tipoPersonaId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $tipoPersona = ejecutarTipoPersonaSeleccionarPorId($conn, $tipoPersonaId);
    
    oci_close($conn);
    return !empty($tipoPersona) ? $tipoPersona[0] : null;
}

/**
 * Obtiene una dirección por su ID
 * @param int $direccionId ID de la dirección
 * @return array|null Datos de la dirección o null si no existe
 */
function obtenerDireccionPorId($direccionId) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $sql = "BEGIN FIDE_DIRECCION_PKG.DIRECCION_SELECCIONAR_POR_ID_SP(:direccion_id, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_bind_by_name($stmt, ":direccion_id", $direccionId);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return null;
    }
    
    oci_execute($cursor);
    
    $data = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $data[] = $row;
    }
    
    oci_free_statement($cursor);
    oci_free_statement($stmt);
    oci_close($conn);
    
    return !empty($data) ? $data[0] : null;
}

/**
 * Inserta una nueva persona
 * @param array $datos Datos de la persona
 * @return bool True si se insertó correctamente, False en caso contrario
 */
function insertarPersona($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_PERSONAS_CEDULA_PERSONA_PK' => $datos['cedula'],
        'V_PERSONAS_NOMBRE' => $datos['nombre'],
        'V_PERSONAS_APELLIDO1' => $datos['apellido1'],
        'V_PERSONAS_APELLIDO2' => $datos['apellido2'],
        'V_PERSONAS_ID_DIRECCION_FK' => $datos['direccion_id'],
        'V_PERSONAS_ID_TIPO_FK' => $datos['tipo_id']
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PERSONAS_PKG.PERSONAS_INSERTAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Actualiza una persona existente
 * @param array $datos Datos actualizados de la persona
 * @return bool True si se actualizó correctamente, False en caso contrario
 */
function actualizarPersona($datos) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_PERSONAS_CEDULA_PERSONA_PK' => $datos['cedula'],
        'V_PERSONAS_NOMBRE' => $datos['nombre'],
        'V_PERSONAS_APELLIDO1' => $datos['apellido1'],
        'V_PERSONAS_APELLIDO2' => $datos['apellido2'],
        'V_PERSONAS_ID_DIRECCION_FK' => $datos['direccion_id'],
        'V_PERSONAS_ID_TIPO_FK' => $datos['tipo_id'],
        'V_ESTADO_ID_FK' => $datos['estado_id']
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PERSONAS_PKG.PERSONAS_ACTUALIZAR_SP', $params);
    
    oci_close($conn);
    return $result;
}

/**
 * Desactiva una persona por su cédula
 * @param string $cedula Cédula de la persona
 * @return bool True si se desactivó correctamente, False en caso contrario
 */
function desactivarPersona($cedula) {
    $conn = getOracleConnection();
    if (!$conn) return false;
    
    $params = [
        'V_PERSONAS_CEDULA_PERSONA_PK' => $cedula,
        'V_ESTADO_INACTIVO_ID' => 2  // Estado inactivo
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PERSONAS_PKG.PERSONAS_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $result;
}
?>