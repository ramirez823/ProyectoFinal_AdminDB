<?php
/**
 * Ver detalles de una dirección
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó un ID
if (empty($_GET['id'])) {
    header('Location: index.php?mensaje=Debe especificar un ID de dirección&tipo=warning');
    exit;
}

$direccionId = (int)$_GET['id'];

// Obtener datos de la dirección
$direccion = obtenerDireccionPorId($direccionId);

// Si no se encontró la dirección, redirigimos
if (!$direccion) {
    header('Location: index.php?mensaje=No se encontró la dirección especificada&tipo=danger');
    exit;
}

// Obtener datos relacionados (provincia, cantón, distrito)
$provincia = null;
if (!empty($direccion['ID_PROVINCIA_FK'])) {
    $provincia = obtenerProvinciaPorId($direccion['ID_PROVINCIA_FK']);
}

$canton = null;
if (!empty($direccion['ID_CANTON_FK'])) {
    $canton = obtenerCantonPorId($direccion['ID_CANTON_FK']);
}

$distrito = null;
if (!empty($direccion['ID_DISTRITO_FK'])) {
    $distrito = obtenerDistritoPorId($direccion['ID_DISTRITO_FK']);
}

// Obtener personas asociadas a esta dirección
$personasAsociadas = obtenerPersonasPorDireccion($direccionId);

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalles de Dirección</h1>
        <div class="btn-group">
            <a href="index.php" class="btn btn-secondary">Volver</a>
            <a href="editar.php?id=<?= $direccionId ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
        </div>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-<?= isset($_GET['tipo']) ? $_GET['tipo'] : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_GET['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Información de Dirección</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID de Dirección:</strong> <?= htmlspecialchars($direccion['ID_DIRECCION_PK']) ?></p>
                    <p><strong>Provincia:</strong> <?= $provincia ? htmlspecialchars($provincia['PROVINCIA_NOMBRE']) : 'No especificada' ?></p>
                    <p><strong>Cantón:</strong> <?= $canton ? htmlspecialchars($canton['CANTON_NOMBRE']) : 'No especificado' ?></p>
                    <p><strong>Distrito:</strong> <?= $distrito ? htmlspecialchars($distrito['DISTRITO_NOMBRE']) : 'No especificado' ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Señas:</strong> <?= !empty($direccion['SENNAS']) ? htmlspecialchars($direccion['SENNAS']) : 'No especificadas' ?></p>
                    <p><strong>Estado:</strong> <?= $direccion['ESTADO_ID_FK'] == 1 ? 'Activo' : 'Inactivo' ?></p>
                    <p><strong>Fecha de Creación:</strong> <?= date('d/m/Y H:i:s', strtotime($direccion['CREATION_DATE'])) ?></p>
                    <p><strong>Última Actualización:</strong> 
                        <?= !empty($direccion['LAST_UPDATE_DATE']) ? date('d/m/Y H:i:s', strtotime($direccion['LAST_UPDATE_DATE'])) : 'No ha sido actualizada' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($personasAsociadas)): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Personas asociadas a esta dirección</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($personasAsociadas as $persona): ?>
                            <tr>
                                <td><?= htmlspecialchars($persona['PERSONAS_CEDULA_PERSONA_PK']) ?></td>
                                <td><?= htmlspecialchars($persona['PERSONAS_NOMBRE']) ?></td>
                                <td>
                                    <?= htmlspecialchars($persona['PERSONAS_APELLIDO1']) ?>
                                    <?= !empty($persona['PERSONAS_APELLIDO2']) ? ' ' . htmlspecialchars($persona['PERSONAS_APELLIDO2']) : '' ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $persona['ESTADO_ID_FK'] == 1 ? 'success' : 'danger' ?>">
                                        <?= $persona['ESTADO_ID_FK'] == 1 ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../personas/ver.php?cedula=<?= $persona['PERSONAS_CEDULA_PERSONA_PK'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        No hay personas asociadas a esta dirección.
    </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>