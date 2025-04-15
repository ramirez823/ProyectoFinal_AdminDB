<?php
/**
 * Desactivar una persona
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Activar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificamos si se proporcionó una cédula
if (empty($_GET['cedula'])) {
    echo "Error: No se proporcionó una cédula.<br>";
    echo "<a href='index.php'>Volver al listado</a>";
    exit;
}

$cedula = trim($_GET['cedula']);
echo "Intentando desactivar persona con cédula: " . htmlspecialchars($cedula) . "<br>";

// Verificamos si la persona existe
$persona = obtenerPersonaPorCedula($cedula);
if (!$persona) {
    echo "Error: No se encontró la persona con la cédula: " . htmlspecialchars($cedula) . "<br>";
    echo "<a href='index.php'>Volver al listado</a>";
    exit;
}

echo "Persona encontrada: " . htmlspecialchars($persona['PERSONAS_NOMBRE']) . " " . 
     htmlspecialchars($persona['PERSONAS_APELLIDO1']) . "<br>";

// Intentamos desactivar la persona
echo "Intentando ejecutar desactivarPersona()...<br>";
$resultado = desactivarPersona($cedula);
echo "Resultado: " . ($resultado ? "Éxito" : "Fallo") . "<br>";

if ($resultado) {
    echo "Persona desactivada correctamente.<br>";
    echo "<a href='index.php'>Volver al listado</a>";
} else {
    echo "Error al desactivar la persona.<br>";
    echo "<a href='index.php'>Volver al listado</a>";
}
exit;
?>