<?php
/**
 * Lista de direcciones
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Obtener todas las direcciones activas
$direcciones = obtenerDireccionesActivas();

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Direcciones</h1>
        <a href="crear.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Dirección
        </a>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-<?= isset($_GET['tipo']) ? $_GET['tipo'] : 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_GET['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Direcciones Registradas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($direcciones)): ?>
                <div class="alert alert-info">
                    No se encontraron direcciones en el sistema.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Provincia</th>
                                <th>Cantón</th>
                                <th>Distrito</th>
                                <th>Señas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($direcciones as $direccion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($direccion['ID_DIRECCION_PK']) ?></td>
                                    <td>
                                        <?php
                                        // Obtener nombre de provincia
                                        $provincia = obtenerProvinciaPorId($direccion['ID_PROVINCIA_FK']);
                                        echo $provincia ? htmlspecialchars($provincia['PROVINCIA_NOMBRE']) : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Obtener nombre de cantón
                                        $canton = obtenerCantonPorId($direccion['ID_CANTON_FK']);
                                        echo $canton ? htmlspecialchars($canton['CANTON_NOMBRE']) : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Obtener nombre de distrito
                                        $distrito = obtenerDistritoPorId($direccion['ID_DISTRITO_FK']);
                                        echo $distrito ? htmlspecialchars($distrito['DISTRITO_NOMBRE']) : 'N/A';
                                        ?>
                                    </td>
                                    <td><?= !empty($direccion['SENNAS']) ? htmlspecialchars($direccion['SENNAS']) : 'No especificadas' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="ver.php?id=<?= $direccion['ID_DIRECCION_PK'] ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?= $direccion['ID_DIRECCION_PK'] ?>" class="btn btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?= $direccion['ID_DIRECCION_PK'] ?>" class="btn btn-danger" title="Eliminar" 
                                               onclick="return confirm('¿Está seguro de que desea eliminar esta dirección?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>