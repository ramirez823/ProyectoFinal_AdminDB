<?php
/**
 * Configuración y funciones de conexión a Oracle
 */

// Datos de conexión
define('DB_USERNAME', 'PROYECTO_FINAL'); 
define('DB_PASSWORD', 'PROYECTOFINAL'); 
define('DB_CONNECTION_STRING', '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SERVICE_NAME=XEPDB1)))'); // Conexión correcta

/**
 * Establece conexión con la base de datos Oracle
 * @return resource Conexión Oracle o false en caso de error
 */
function getOracleConnection() {
    $conn = oci_connect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING, 'AL32UTF8'); 

    if (!$conn) {
        $e = oci_error();
        trigger_error("❌ ¡Error de conexión!: " . htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }else {
        # echo "hola";
    }
    return $conn;
}

// Conexión y verificación
$conn = getOracleConnection();

if ($conn) {

    oci_close($conn); // Cerramos la conexión
} else {
    echo "❌ No se pudo conectar a la base de datos.";
}


/**
 * Ejecuta un procedimiento almacenado con parámetros
 * @param resource $conn Conexión Oracle
 * @param string $procedureName Nombre del procedimiento
 * @param array $params Parámetros del procedimiento
 * @return mixed Resultado del procedimiento o false
 */
function executeOracleProcedure($conn, $procedureName, $params = []) {
    $sql = "BEGIN $procedureName(";
    
    $paramPlaceholders = [];
    foreach ($params as $key => $value) {
        $paramPlaceholders[] = ":$key";
    }
    
    $sql .= implode(', ', $paramPlaceholders);
    $sql .= "); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    // Bind the parameters
    foreach ($params as $key => &$value) {
        oci_bind_by_name($stmt, ":$key", $value);
    }
    
    $result = oci_execute($stmt);
    if (!$result) {
        $e = oci_error($stmt);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    
    oci_free_statement($stmt);
    return true;
}

/**
 * Ejecuta una consulta que retorna un cursor como primer parámetro (por la manera en que diseñaron los paquetes)
 * @param resource $conn Conexión Oracle
 * @param string $packageName Nombre del paquete
 * @param string $procedureName Nombre del procedimiento
 * @param array $params Parámetros del procedimiento (sin incluir el cursor)
 * @return array Resultados de la consulta
 */
function executeOracleCursorProcedure($conn, $packageName, $procedureName, $params = []) {
    $fullProcedureName = "$packageName.$procedureName";
    
    $sql = "BEGIN $fullProcedureName(";
    
    // El cursor es el primer parámetro
    $paramPlaceholders = [":cursor"];
    
    // Luego vienen los demás parámetros
    for ($i = 0; $i < count($params); $i++) {
        $paramPlaceholders[] = ":param$i";
    }
    
    $sql .= implode(', ', $paramPlaceholders);
    $sql .= "); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Bind the cursor first
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    
    // Then bind the other parameters
    for ($i = 0; $i < count($params); $i++) {
        oci_bind_by_name($stmt, ":param$i", $params[$i]);
    }
    
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
 * Ejecuta específicamente el procedimiento PERSONAS_SELECCIONAR_POR_ID_SP
 * en el modulo personas/ functions.... un tema con el cursor, toca hacerlo asi para que no reviente
 * @param resource $conn Conexión Oracle
 * @param string $cedula Cédula de la persona
 * @return array Datos de la persona
 */
function ejecutarPersonasSeleccionarPorId($conn, $cedula) {
    $sql = "BEGIN FIDE_PERSONAS_PKG.PERSONAS_SELECCIONAR_POR_ID_SP(:cedula, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Bind the cedula parameter
    oci_bind_by_name($stmt, ":cedula", $cedula);
    
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
 * Ejecuta el procedimiento CLIENTES_SELECCIONAR_POR_ID_SP
 * @param resource $conn Conexión Oracle
 * @param string $cedula Cédula de la persona/cliente
 * @return array Datos del cliente
 */
function ejecutarClientesSeleccionarPorId($conn, $cedula) {
    $sql = "BEGIN FIDE_CLIENTES_PKG.CLIENTES_SELECCIONAR_POR_ID_SP(:cedula, :cursor); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Bind the cedula parameter
    oci_bind_by_name($stmt, ":cedula", $cedula);
    
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
 * Obtiene un cliente por su cédula
 * @param string $cedula Cédula del cliente
 * @return array|null Datos del cliente o null si no existe
 */
function obtenerClientePorCedula($cedula) {
    $conn = getOracleConnection();
    if (!$conn) return null;
    
    $cliente = ejecutarClientesSeleccionarPorId($conn, $cedula);
    
    oci_close($conn);
    return !empty($cliente) ? $cliente[0] : null;
}

?>


