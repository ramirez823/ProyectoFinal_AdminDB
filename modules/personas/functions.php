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
    
    // Utilizamos el paquete FIDE_PERSONAS_PKG y su procedimiento PERSONAS_SELECCIONAR_POR_ID_SP
    $personas = executeOracleCursorProcedure($conn, 'FIDE_PERSONAS_PKG', 'PERSONAS_SELECCIONAR_POR_ID_SP', [$cedula]);
    
    oci_close($conn);
    
    // Si no hay resultados, retornamos null
    if (empty($personas)) {
        return null;
    }
    
    // Retornamos el primer elemento del array
    return $personas[0];
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
        'V_ESTADO_INACTIVO_ID' => 2 // Estado inactivo
    ];
    
    $result = executeOracleProcedure($conn, 'FIDE_PERSONAS_PKG.PERSONAS_DESACTIVAR_SP', $params);
    
    oci_close($conn);
    return $result;
}
?>