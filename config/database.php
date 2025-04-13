<?php
/**
 * Configuración y funciones de conexión a Oracle
 */

// Datos de conexión
define('DB_USERNAME', 'PROYECTO_FINAL'); // Tu usuario
define('DB_PASSWORD', 'PROYECTOFINAL'); // Tu contraseña
define('DB_CONNECTION_STRING', '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SERVICE_NAME=XEPDB1)))'); // Conexión correcta

/**
 * Establece conexión con la base de datos Oracle
 * @return resource Conexión Oracle o false en caso de error
 */
function getOracleConnection() {
    $conn = oci_connect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING, 'AL32UTF8'); // Cambia 'AL32UTF8' si tu base de datos tiene otro juego de caracteres

    if (!$conn) {
        $e = oci_error();
        trigger_error("❌ ¡Error de conexión!: " . htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return false;
    }
    return $conn;
}

// Conexión y verificación
$conn = getOracleConnection();

if ($conn) {
    echo "✅ ¡Conexión establecida correctamente! <br> hola mundo xdddd";
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
 * Ejecuta una consulta que retorna un cursor
 * @param resource $conn Conexión Oracle
 * @param string $packageName Nombre del paquete
 * @param string $procedureName Nombre del procedimiento
 * @param array $params Parámetros del procedimiento
 * @return array Resultados de la consulta
 */
function executeOracleCursorProcedure($conn, $packageName, $procedureName, $params = []) {
    $fullProcedureName = "$packageName.$procedureName";
    
    $sql = "BEGIN $fullProcedureName(";
    
    $paramPlaceholders = [];
    for ($i = 0; $i < count($params); $i++) {
        $paramPlaceholders[] = ":param$i";
    }
    $paramPlaceholders[] = ":cursor";  // Add cursor parameter
    
    $sql .= implode(', ', $paramPlaceholders);
    $sql .= "); END;";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        return [];
    }
    
    // Bind the parameters
    for ($i = 0; $i < count($params); $i++) {
        oci_bind_by_name($stmt, ":param$i", $params[$i]);
    }
    
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
?>


