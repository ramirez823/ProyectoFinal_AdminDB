<?php
/**
 * Endpoint para obtener distritos por cantón (AJAX)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar que se proporcionó el ID de cantón
if (empty($_GET['canton_id'])) {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Debe proporcionar un ID de cantón']);
    exit;
}

$cantonId = (int)$_GET['canton_id'];

// Obtener distritos
$distritos = obtenerDistritosPorCanton($cantonId);

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($distritos);