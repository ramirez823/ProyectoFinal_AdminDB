<?php

$username = "ADMIN_P3";
$password = "1234";
$connection_string = "localhost/XEPDB1"; // O SID: "localhost:1521/ORCL"

$conn = oci_connect(
    'ADMIN_P3',
    '1234',
    '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=DESKTOP-BBC5U9Q)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SERVICE_NAME=XE)))'
);

if (!$conn) {
    $e = oci_error();
    echo "¡Error de conexión!: " . htmlentities($e['message'], ENT_QUOTES);
} else {
    echo "✅ ¡Conexión exitosa a Oracle!";
    echo "hola mundo";
    oci_close($conn);
}
?>
