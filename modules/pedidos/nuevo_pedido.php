<?php
/**
 * Formulario para crear un nuevo pedido
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../menu/functions.php'; // Necesitamos acceder a funciones del menú

// Obtener datos necesarios para el formulario
$tiposEntrega = obtenerTiposEntregaActivos();
$estadosPedido = obtenerEstadosPedidoActivos();
$itemsMenu = obtenerMenuDisponible(); // Esta función debe estar en el módulo de menú

// Variable para almacenar mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entradas
    $cedula = sanitizeInput($_POST['cedula'] ?? '');
    $tipoEntregaId = intval($_POST['tipo_entrega_id'] ?? 0);
    $items = isset($_POST['items']) ? $_POST['items'] : [];
    $cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];
    
    // Validación básica
    $errores = [];
    
    if (empty($cedula) || !validarCedula($cedula)) {
        $errores[] = "La cédula es inválida";
    }
    
    if ($tipoEntregaId <= 0) {
        $errores[] = "Debe seleccionar un tipo de entrega";
    }
    
    if (empty($items)) {
        $errores[] = "Debe seleccionar al menos un ítem";
    }
    
    // Validar cantidades
    foreach ($cantidades as $index => $cantidad) {
        if (intval($cantidad) <= 0) {
            $errores[] = "La cantidad del ítem #" . ($index + 1) . " debe ser mayor que cero";
        }
    }
    
    // Si no hay errores, crear el pedido
    if (empty($errores)) {
        // Datos del pedido
        $datosPedido = [
            'cedula_persona' => $cedula,
            'tipo_entrega_id' => $tipoEntregaId,
            'estado_id' => 1 // Estado inicial (pendiente)
        ];
        
        // Insertar el pedido
        $pedidoId = insertarPedido($datosPedido);
        
        if ($pedidoId) {
            // Insertar detalles del pedido
            $detallesExitosos = true;
            
            foreach ($items as $index => $itemId) {
                // Obtener el precio del ítem desde el menú
                $precioUnitario = 0;
                foreach ($itemsMenu as $item) {
                    if ($item['MENU_ID_PK'] == $itemId) {
                        $precioUnitario = $item['MENU_PRECIO'];
                        break;
                    }
                }
                
                $datosDetalle = [
                    'pedido_id' => $pedidoId,
                    'menu_id' => $itemId,
                    'cantidad' => intval($cantidades[$index]),
                    'precio_unitario' => $precioUnitario
                ];
                
                $detalleId = insertarDetallePedido($datosDetalle);
                
                if (!$detalleId) {
                    $detallesExitosos = false;
                    break;
                }
            }
            
            if ($detallesExitosos) {
                // Registrar el seguimiento inicial
                $datosSeguimiento = [
                    'pedido_id' => $pedidoId,
                    'estado_id' => 1, // Estado inicial (pendiente)
                    'comentario' => 'Pedido creado'
                ];
                
                registrarSeguimientoPedido($datosSeguimiento);
                
                $mensaje = "Pedido creado exitosamente con ID: " . $pedidoId;
                $tipoMensaje = "success";
                
                // Registrar la acción en el log
                registrarLog('Creación de pedido', "Se creó el pedido con ID $pedidoId para el cliente $cedula");
                
                // Redireccionar a la vista del pedido
                header("refresh:2;url=ver_pedido.php?id=$pedidoId");
            } else {
                $mensaje = "Error al crear los detalles del pedido";
                $tipoMensaje = "danger";
            }
        } else {
            $mensaje = "Error al crear el pedido";
            $tipoMensaje = "danger";
        }
    } else {
        // Hay errores de validación
        $mensaje = "<ul><li>" . implode("</li><li>", $errores) . "</li></ul>";
        $tipoMensaje = "danger";
    }
}

// Incluir el encabezado
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nuevo Pedido</h1>
        <a href="index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Datos del Pedido</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="formPedido">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cedula" class="form-label">Cédula Cliente</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cedula" name="cedula" required 
                                value="<?= isset($_POST['cedula']) ? htmlspecialchars($_POST['cedula']) : '' ?>">
                            <button class="btn btn-outline-secondary" type="button" id="buscarCliente">Buscar</button>
                        </div>
                        <div class="form-text">Formato: Nacional (9 dígitos) o DIMEX (12 dígitos)</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="tipo_entrega_id" class="form-label">Tipo de Entrega</label>
                        <select class="form-select" id="tipo_entrega_id" name="tipo_entrega_id" required>
                            <option value="">Seleccione un tipo de entrega</option>
                            <?php foreach ($tiposEntrega as $tipo): ?>
                                <option value="<?= $tipo['TIPO_ENTREGA_ID_PK'] ?>" <?= (isset($_POST['tipo_entrega_id']) && $_POST['tipo_entrega_id'] == $tipo['TIPO_ENTREGA_ID_PK']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['TIPO_ENTREGA_NOMBRE']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3" id="infoCliente" style="display: none;">
                    <div class="alert alert-info">
                        <h5>Información del Cliente</h5>
                        <div id="datosCliente"></div>
                    </div>
                </div>
                
                <h5 class="mt-4 mb-3">Ítems del Pedido</h5>
                
                <div id="itemsContainer">
                    <div class="row mb-3 item-row">
                        <div class="col-md-6">
                            <label class="form-label">Ítem</label>
                            <select class="form-select item-select" name="items[]" required>
                                <option value="">Seleccione un ítem</option>
                                <?php foreach ($itemsMenu as $item): ?>
                                    <option value="<?= $item['MENU_ID_PK'] ?>" data-precio="<?= $item['MENU_PRECIO'] ?>">
                                        <?= htmlspecialchars($item['MENU_NOMBRE']) ?> - <?= formatearMoneda($item['MENU_PRECIO']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control cantidad-input" name="cantidades[]" min="1" value="1" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger eliminar-item" style="display: none;">Eliminar</button>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="button" id="agregarItem" class="btn btn-info">
                        <i class="fas fa-plus-circle"></i> Agregar Ítem
                    </button>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Resumen del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Total Ítems:</strong> <span id="totalItems">0</span></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p><strong>Subtotal:</strong> <span id="subtotal">₡ 0,00</span></p>
                                <p><strong>IVA (13%):</strong> <span id="iva">₡ 0,00</span></p>
                                <h4 class="text-primary"><strong>Total:</strong> <span id="total">₡ 0,00</span></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary me-md-2" onclick="window.location='index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formPedido = document.getElementById('formPedido');
    const itemsContainer = document.getElementById('itemsContainer');
    const agregarItemBtn = document.getElementById('agregarItem');
    const buscarClienteBtn = document.getElementById('buscarCliente');
    
    // Función para agregar un nuevo ítem
    agregarItemBtn.addEventListener('click', function() {
        const newRow = document.querySelector('.item-row').cloneNode(true);
        newRow.querySelector('.item-select').value = '';
        newRow.querySelector('.cantidad-input').value = 1;
        newRow.querySelector('.eliminar-item').style.display = 'block';
        itemsContainer.appendChild(newRow);
        
        // Asignar evento de eliminación
        newRow.querySelector('.eliminar-item').addEventListener('click', function() {
            newRow.remove();
            actualizarResumen();
        });
        
        // Asignar evento de cambio para recalcular
        newRow.querySelector('.item-select').addEventListener('change', actualizarResumen);
        newRow.querySelector('.cantidad-input').addEventListener('input', actualizarResumen);
    });
    
    // Asignar eventos iniciales
    document.querySelector('.item-select').addEventListener('change', actualizarResumen);
    document.querySelector('.cantidad-input').addEventListener('input', actualizarResumen);
    
    // Mostrar la primera fila de eliminación si hay más de una fila
    if (document.querySelectorAll('.item-row').length > 1) {
        document.querySelector('.eliminar-item').style.display = 'block';
    }
    
    // Función para buscar cliente
    buscarClienteBtn.addEventListener('click', function() {
        const cedula = document.getElementById('cedula').value;
        if (cedula.trim() === '') return;
        
        // Aquí deberías hacer una petición AJAX para buscar el cliente
        // Como ejemplo, mostramos un mensaje simple
        const infoCliente = document.getElementById('infoCliente');
        const datosCliente = document.getElementById('datosCliente');
        
        // Simulación de búsqueda (en producción deberías hacer una petición AJAX)
        setTimeout(() => {
            infoCliente.style.display = 'block';
            datosCliente.innerHTML = `<p>Cliente con cédula ${cedula} encontrado.</p>
                                      <p>Nombre: Juan Pérez</p>`;
        }, 500);
    });
    
    // Función para actualizar el resumen
    function actualizarResumen() {
        let totalItems = 0;
        let subtotal = 0;
        
        // Recorrer todas las filas de items
        const filas = document.querySelectorAll('.item-row');
        filas.forEach(fila => {
            const select = fila.querySelector('.item-select');
            const cantidad = parseInt(fila.querySelector('.cantidad-input').value) || 0;
            
            if (select.value) {
                const precio = parseFloat(select.options[select.selectedIndex].dataset.precio) || 0;
                subtotal += precio * cantidad;
                totalItems += cantidad;
            }
        });
        
        // Calcular impuestos y total
        const iva = subtotal * 0.13;
        const total = subtotal + iva;
        
        // Actualizar campos
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('subtotal').textContent = formatearMoneda(subtotal);
        document.getElementById('iva').textContent = formatearMoneda(iva);
        document.getElementById('total').textContent = formatearMoneda(total);
    }
    
    // Función para formatear moneda
    function formatearMoneda(valor) {
        return '₡ ' + valor.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    // Inicializar resumen
    actualizarResumen();
});
</script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>