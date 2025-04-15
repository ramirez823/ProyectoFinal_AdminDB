<?php
/**
 * Desactivación lógica de una categoría de menú
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar si se ha enviado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = 'Error: No se especificó un ID de categoría para desactivar.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: categorias.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información de la categoría para confirmar que existe
$categoria = obtenerCategoriaMenuPorId($id);

if (!$categoria) {
    $_SESSION['mensaje'] = 'Error: La categoría solicitada no existe.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: categorias.php');
    exit;
}

// Intentar desactivar la categoría
if (desactivarCategoriaMenu($id)) {
    $_SESSION['mensaje'] = 'La categoría "' . htmlspecialchars($categoria['CATEGORIA_MENU_NOMBRE']) . '" ha sido desactivada correctamente.';
    $_SESSION['tipo_mensaje'] = 'success';
} else {
    $_SESSION['mensaje'] = 'Error: No se pudo desactivar la categoría. Inténtelo nuevamente.';
    $_SESSION['tipo_mensaje'] = 'danger';
}

// Redireccionar al listado
header('Location: categorias.php');
exit;
?>