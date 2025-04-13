<?php
/**
 * Listado de personas
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Obtener todas las personas activas
$personas = obtenerPersonasActivas();

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <h1>Gestión de Personas</h1>
    
    <div class="mb-3">
        <a href="create.php" class="btn btn-primary">Agregar Nueva Persona</a>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido 1</th>
                <th>Apellido 2</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($personas)): ?>
                <tr>
                    <td colspan="5" class="text-center">No hay personas registradas</td>
                </tr>
            <?php else: ?>
                <?php foreach ($personas as $persona): ?>
                    <tr>
                        <td><?= htmlspecialchars($persona['PERSONAS_CEDULA_PERSONA_PK']) ?></td>
                        <td><?= htmlspecialchars($persona['PERSONAS_NOMBRE']) ?></td>
                        <td><?= htmlspecialchars($persona['PERSONAS_APELLIDO1']) ?></td>
                        <td><?= htmlspecialchars($persona['PERSONAS_APELLIDO2'] ?? '') ?></td>
                        <td>
                            <a href="view.php?cedula=<?= urlencode($persona['PERSONAS_CEDULA_PERSONA_PK']) ?>" class="btn btn-sm btn-info">Ver</a>
                            <a href="edit.php?cedula=<?= urlencode($persona['PERSONAS_CEDULA_PERSONA_PK']) ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="javascript:void(0);" onclick="confirmarEliminar('<?= $persona['PERSONAS_CEDULA_PERSONA_PK'] ?>')" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmarEliminar(cedula) {
    if (confirm('¿Está seguro que desea desactivar esta persona?')) {
        window.location.href = 'delete.php?cedula=' + encodeURIComponent(cedula);
    }
}
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>