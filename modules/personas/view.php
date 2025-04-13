<?php
/**
 * Ver detalles de una persona
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Verificamos si se proporcionó una cédula
if (empty($_GET['cedula'])) {
    header('Location: index.php?mensaje=Debe especificar una cédula para ver detalles');
    exit;
}

$cedula = trim($_GET['cedula']);

// Obtener datos de la persona
$persona = obtenerPersonaPorCedula($cedula);

// Si no se encontró la persona, redirigimos
if (!$persona) {
    header('Location: index.php?mensaje=No se encontró la persona con la cédula especificada');
    exit;
}

// Obtener datos adicionales (tipo de persona, dirección, etc.)
$conn = getOracleConnection();

// Obtener el tipo de persona
$tipoPersona = null;
if (!empty($persona['PERSONAS_ID_TIPO_FK'])) {
    $tipoPersonaData = executeOracleCursorProcedure($conn, 'FIDE_TIPO_PERSONA_PKG', 'TIPO_PERSONA_SELECCIONAR_POR_ID_SP', [$persona['PERSONAS_ID_TIPO_FK']]);
    if (!empty($tipoPersonaData)) {
        $tipoPersona = $tipoPersonaData[0];
    }
}

// Obtener la dirección
$direccion = null;
if (!empty($persona['PERSONAS_ID_DIRECCION_FK'])) {
    $direccionData = executeOracleCursorProcedure($conn, 'FIDE_DIRECCION_PKG', 'DIRECCION_SELECCIONAR_POR_ID_SP', [$persona['PERSONAS_ID_DIRECCION_FK']]);
    if (!empty($direccionData)) {
        $direccion = $direccionData[0];
        
        // Obtener provincia, cantón y distrito si es necesario
        if (!empty($direccion['ID_PROVINCIA_FK'])) {
            $provinciaData = executeOracleCursorProcedure($conn, 'FIDE_PROVINCIA_PKG', 'PROVINCIA_SELECCIONAR_POR_ID_SP', [$direccion['ID_PROVINCIA_FK']]);
            if (!empty($provinciaData)) {
                $direccion['PROVINCIA'] = $provinciaData[0];
            }
        }
        
        if (!empty($direccion['ID_CANTON_FK'])) {
            $cantonData = executeOracleCursorProcedure($conn, 'FIDE_CANTON_PKG', 'CANTON_SELECCIONAR_POR_ID_SP', [$direccion['ID_CANTON_FK']]);
            if (!empty($cantonData)) {
                $direccion['CANTON'] = $cantonData[0];
            }
        }
        
        if (!empty($direccion['ID_DISTRITO_FK'])) {
            $distritoData = executeOracleCursorProcedure($conn, 'FIDE_DISTRITO_PKG', 'DISTRITO_SELECCIONAR_POR_ID_SP', [$direccion['ID_DISTRITO_FK']]);
            if (!empty($distritoData)) {
                $direccion['DISTRITO'] = $distritoData[0];
            }
        }
    }
}

// Obtener estado
$estado = null;
if (!empty($persona['ESTADO_ID_FK'])) {
    $estadoData = executeOracleCursorProcedure($conn, 'FIDE_ESTADO_PKG', 'ESTADO_SELECCIONAR_POR_ID_SP', [$persona['ESTADO_ID_FK']]);
    if (!empty($estadoData)) {
        $estado = $estadoData[0];
    }
}

// Verificar si es cliente
$esCliente = false;
$clienteData = executeOracleCursorProcedure($conn, 'FIDE_CLIENTES_PKG', 'CLIENTES_SELECCIONAR_POR_ID_SP', [$cedula]);
if (!empty($clienteData)) {
    $esCliente = true;
    $cliente = $clienteData[0];
}

oci_close($conn);

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalles de Persona</h1>
        <div>
            <a href="index.php" class="btn btn-secondary">Volver</a>
            <a href="edit.php?cedula=<?= urlencode($cedula) ?>" class="btn btn-warning">Editar</a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Información Personal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Cédula/DIMEX:</strong> <?= htmlspecialchars($persona['PERSONAS_CEDULA_PERSONA_PK']) ?></p>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($persona['PERSONAS_NOMBRE']) ?></p>
                    <p><strong>Primer Apellido:</strong> <?= htmlspecialchars($persona['PERSONAS_APELLIDO1']) ?></p>
                    <p><strong>Segundo Apellido:</strong> <?= htmlspecialchars($persona['PERSONAS_APELLIDO2'] ?? 'No especificado') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tipo de Persona:</strong> <?= $tipoPersona ? htmlspecialchars($tipoPersona['TIPO_PERSONA_NOMBRE']) : 'No especificado' ?></p>
                    <p><strong>Estado:</strong> <?= $estado ? htmlspecialchars($estado['ESTADO_NOMBRE']) : 'No especificado' ?></p>
                    <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($persona['CREATION_DATE']))) ?></p>
                    <p><strong>Última Actualización:</strong> 
                        <?= !empty($persona['LAST_UPDATE_DATE']) ? htmlspecialchars(date('d/m/Y H:i:s', strtotime($persona['LAST_UPDATE_DATE']))) : 'No ha sido actualizado' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($direccion): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Información de Dirección</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID Dirección:</strong> <?= htmlspecialchars($direccion['ID_DIRECCION_PK']) ?></p>
                    <p><strong>Provincia:</strong> <?= isset($direccion['PROVINCIA']) ? htmlspecialchars($direccion['PROVINCIA']['PROVINCIA_NOMBRE']) : 'No especificada' ?></p>
                    <p><strong>Cantón:</strong> <?= isset($direccion['CANTON']) ? htmlspecialchars($direccion['CANTON']['CANTON_NOMBRE']) : 'No especificado' ?></p>
                    <p><strong>Distrito:</strong> <?= isset($direccion['DISTRITO']) ? htmlspecialchars($direccion['DISTRITO']['DISTRITO_NOMBRE']) : 'No especificado' ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Señas:</strong> <?= !empty($direccion['SENNAS']) ? htmlspecialchars($direccion['SENNAS']) : 'No especificadas' ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($esCliente): ?>
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Información de Cliente</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha de Registro:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($cliente['CLIENTES_FECHA_REGISTRO']))) ?></p>
                    <p><strong>Preferencia de Facturación:</strong> <?= !empty($cliente['CLIENTES_PREFERENCIA_FACTURACION_ID_FK']) ? htmlspecialchars($cliente['CLIENTES_PREFERENCIA_FACTURACION_ID_FK']) : 'No especificada' ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Estado del Cliente:</strong> <?= htmlspecialchars($cliente['ESTADO_ID_FK'] == 1 ? 'Activo' : 'Inactivo') ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="alert alert-warning">
                Esta persona no está registrada como cliente.
                <?php if ($persona['ESTADO_ID_FK'] == 1): // Si la persona está activa ?>
                <a href="../clientes/create.php?cedula=<?= urlencode($cedula) ?>" class="btn btn-sm btn-primary ms-3">Registrar como Cliente</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>