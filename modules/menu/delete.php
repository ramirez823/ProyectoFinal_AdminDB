<?php
/**
 * Desactivación lógica de un ítem de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar si se ha enviado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = 'Error: No se especificó un ID de menú para desactivar.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del menú para confirmar que existe
$menu = obtenerMenuPorId($id);

if (!$menu) {
    $_SESSION['mensaje'] = 'Error: El ítem de menú solicitado no existe.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: index.php');
    exit;
}

// Intentar desactivar el ítem
if (desactivarMenu($id)) {
    $_SESSION['mensaje'] = 'El ítem "' . htmlspecialchars($menu['MENU_NOMBRE']) . '" ha sido desactivado correctamente.';
    $_SESSION['tipo_mensaje'] = 'success';
} else {
    $_SESSION['mensaje'] = 'Error: No se pudo desactivar el ítem de menú. Inténtelo nuevamente.';
    $_SESSION['tipo_mensaje'] = 'danger';
}

// Redireccionar al listado
header('Location: index.php');
exit;
?>