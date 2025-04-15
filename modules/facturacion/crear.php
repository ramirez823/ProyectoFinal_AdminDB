<?php
/**
 * Crear nueva factura a partir de un pedido
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

// Inicializar variables
$mensaje = '';
$error = false;
$pedidos = obtenerPedidosPendientesFacturar();

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validamos y sanitizamos los datos
    $pedidoId = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
    $clienteId = isset($_POST['cliente_id']) ? trim($_POST['cliente_id']) : '';
    
    if (empty($pedidoId) || empty($clienteId)) {
        $error = true;
        $mensaje = 'Debe seleccionar un pedido y un cliente.';
    } else {
        // Intentamos crear la factura
        $facturaId = crearFacturaDesdeOrdena($pedidoId, $clienteId);
        
        if ($facturaId) {
            // Redireccionamos a la vista de la factura
            header('Location: ver.php?id=' . $facturaId . '&mensaje=Factura creada correctamente&tipo=success');
            exit;
        } else {
            $error = true;
            $mensaje = 'Error al crear la factura. Verifica los datos e intenta nuevamente.';
        }
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Crear Nueva Factura</h1>
        <a href="index.php" class="btn btn-secondary">Volver a la lista</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $error ? 'danger' : 'success' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Seleccionar Pedido para Facturar</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="alert alert-info">
                    No hay pedidos pendientes para facturar.
                    <a href="../pedidos/index.php" class="btn btn-sm btn-primary ms-3">Ir a Pedidos</a>
                </div>
            <?php else: ?>
                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="pedido_id" class="form-label">Seleccionar Pedido</label>
                        <select class="form-select" id="pedido_id" name="pedido_id" required>
                            <option value="">-- Seleccione un pedido --</option>
                            <?php foreach ($pedidos as $pedido): ?>
                                <option value="<?= $pedido['PEDIDO_ID_PK'] ?>" data-cliente="<?= htmlspecialchars($pedido['PEDIDO_CEDULA_PERSO']) ?>">
                                    Pedido #<?= $pedido['PEDIDO_ID_PK'] ?> - 
                                    Fecha: <?= date('d/m/Y', strtotime($pedido['PEDIDO_FECHA'])) ?> - 
                                    Cliente: <?= htmlspecialchars($pedido['PEDIDO_CEDULA_PERSO']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un pedido.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cédula del Cliente</label>
                        <input type="text" class="form-control" id="cliente_id" name="cliente_id" required readonly>
                        <div class="form-text">
                            Este campo se completa automáticamente al seleccionar un pedido.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Generar Factura</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Script para autocompletar la cédula del cliente al seleccionar un pedido
document.addEventListener('DOMContentLoaded', function() {
    const pedidoSelect = document.getElementById('pedido_id');
    const clienteInput = document.getElementById('cliente_id');
    
    pedidoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            clienteInput.value = selectedOption.dataset.cliente || '';
        } else {
            clienteInput.value = '';
        }
    });
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>