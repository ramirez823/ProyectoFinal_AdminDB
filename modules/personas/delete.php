<?php
/**
 * Desactivar una persona
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó una cédula
if (empty($_GET['cedula'])) {
    header('Location: index.php?mensaje=Debe especificar una cédula para desactivar');
    exit;
}

$cedula = trim($_GET['cedula']);

// Verificamos si la persona existe
$persona = obtenerPersonaPorCedula($cedula);
if (!$persona) {
    header('Location: index.php?mensaje=No se encontró la persona con la cédula especificada');
    exit;
}

// Intentamos desactivar la persona
if (desactivarPersona($cedula)) {
    header('Location: index.php?mensaje=Persona desactivada correctamente');
} else {
    // Si hay error, redireccionamos con mensaje de error
    header('Location: index.php?mensaje=Error al desactivar la persona&error=1');
}
exit;
?>