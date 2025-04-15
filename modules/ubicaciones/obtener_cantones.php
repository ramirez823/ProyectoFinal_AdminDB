<?php
/**
 * Endpoint para obtener cantones por provincia (AJAX)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar que se proporcionó el ID de provincia
if (empty($_GET['provincia_id'])) {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Debe proporcionar un ID de provincia']);
    exit;
}

$provinciaId = (int)$_GET['provincia_id'];

// Obtener cantones
$cantones = obtenerCantonesPorProvincia($provinciaId);

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($cantones);