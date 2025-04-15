<?php
/**
 * Página principal del Sistema de Gestión de Restaurante
 * Muestra un dashboard con información resumida y acceso a los principales módulos
 */

// Iniciar sesión
session_start();

// Incluir archivos de configuración
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

// Verificar si el usuario ha iniciado sesión (comentar durante desarrollo)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Establecer título de la página
$pageTitle = 'Dashboard - Sistema de Gestión de Restaurante';

// Incluir encabezado
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navigation.php';

// Obtener datos para el dashboard si el usuario está autenticado
$estadisticas = [];
$pedidosRecientes = [];
$productosAgotados = [];

if (isset($_SESSION['user_id'])) {
    // Obtener estadísticas básicas
    $conn = getOracleConnection();
    
    if ($conn) {
        // Obtener cantidad de personas activas
        $personasActivas = executeOracleCursorProcedure($conn, 'FIDE_PERSONAS_PKG', 'PERSONAS_SELECCIONAR_ACTIVOS_SP', [1]);
        $estadisticas['totalPersonas'] = count($personasActivas);
        
        // Si quieres obtener más estadísticas, aquí puedes agregar más llamadas a procedimientos
        
        // Cerrar conexión
        oci_close($conn);
    }
}
?>

<div class="container-fluid px-4 py-5">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h1 class="mb-4">Bienvenido al Sistema de Gestión de Restaurante</h1>
            <p class="lead">Administra eficazmente todos los aspectos de tu restaurante: clientes, menú, inventario, pedidos y facturación.</p>
        </div>
    </div>
    
    <!-- Tarjetas de Acceso Rápido -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h2 class="mb-3">Acceso Rápido</h2>
        </div>
        
        <!-- Tarjeta Personas -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title">Personas</h3>
                    <p class="card-text">Gestiona los datos de clientes, empleados y proveedores.</p>
                    <a href="modules/personas/index.php" class="btn btn-primary mt-2">Ir a Personas</a>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Menú -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-utensils fa-3x text-success"></i>
                    </div>
                    <h3 class="card-title">Menú</h3>
                    <p class="card-text">Administra los productos, categorías y precios de tu menú.</p>
                    <a href="modules/menu/index.php" class="btn btn-success mt-2">Ir a Menú</a>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Pedidos -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list fa-3x text-warning"></i>
                    </div>
                    <h3 class="card-title">Pedidos</h3>
                    <p class="card-text">Crea, gestiona y da seguimiento a los pedidos.</p>
                    <a href="modules/pedidos/index.php" class="btn btn-warning mt-2">Ir a Pedidos</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <!-- Estadísticas (Solo para administradores) -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h2 class="mb-3">Estadísticas</h2>
        </div>
        
        <!-- Tarjeta Estadística Clientes -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-primary text-white shadow stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Personas Registradas</h6>
                            <h3 class="mb-0 mt-2"><?= isset($estadisticas['totalPersonas']) ? $estadisticas['totalPersonas'] : '0' ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Estadística Inventario -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-success text-white shadow stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Productos en Inventario</h6>
                            <h3 class="mb-0 mt-2"><?= isset($estadisticas['totalProductos']) ? $estadisticas['totalProductos'] : '0' ?></h3>
                        </div>
                        <i class="fas fa-boxes fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Estadística Pedidos -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-warning text-dark shadow stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Pedidos Pendientes</h6>
                            <h3 class="mb-0 mt-2"><?= isset($estadisticas['pedidosPendientes']) ? $estadisticas['pedidosPendientes'] : '0' ?></h3>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta Estadística Ventas -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-info text-white shadow stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Ventas del Día</h6>
                            <h3 class="mb-0 mt-2"><?= isset($estadisticas['ventasHoy']) ? formatearMoneda($estadisticas['ventasHoy']) : '₡0' ?></h3>
                        </div>
                        <i class="fas fa-cash-register fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filas Informativas -->
    <div class="row g-4">
        <!-- Pedidos Recientes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Pedidos Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pedidosRecientes)): ?>
                        <p class="text-muted text-center py-3">No hay pedidos recientes para mostrar</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidosRecientes as $pedido): ?>
                                    <tr>
                                        <td><?= $pedido['PEDIDO_ID_PK'] ?></td>
                                        <td><?= $pedido['CLIENTE'] ?></td>
                                        <td><?= formatearFecha($pedido['PEDIDO_FECHA']) ?></td>
                                        <td>
                                            <span class="badge pedido-<?= strtolower($pedido['ESTADO']) ?>">
                                                <?= $pedido['ESTADO'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="modules/pedidos/view.php?id=<?= $pedido['PEDIDO_ID_PK'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="modules/pedidos/index.php" class="btn btn-sm btn-outline-primary">Ver todos los pedidos</a>
                </div>
            </div>
        </div>
        
        <!-- Productos Agotados o por Agotarse -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Productos Agotados o por Agotarse</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($productosAgotados)): ?>
                        <p class="text-muted text-center py-3">No hay productos agotados o por agotarse</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock</th>
                                        <th>Mínimo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productosAgotados as $producto): ?>
                                    <tr>
                                        <td><?= $producto['ARTICULO_NOMBRE'] ?></td>
                                        <td><?= $producto['CATEGORIA'] ?></td>
                                        <td class="<?= $producto['STOCK'] == 0 ? 'text-danger' : 'text-warning' ?> fw-bold">
                                            <?= $producto['STOCK'] ?>
                                        </td>
                                        <td><?= $producto['MINIMO'] ?></td>
                                        <td>
                                            <a href="modules/inventario/edit.php?id=<?= $producto['ARTICULO_ID_PK'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="modules/inventario/index.php" class="btn btn-sm btn-outline-primary">Ver todo el inventario</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Sección para usuarios no autenticados -->
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="row my-5">
        <div class="col-md-8 offset-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <h2 class="mb-3">¡Bienvenido al Sistema!</h2>
                    <p class="lead mb-4">Para acceder a todas las funcionalidades del sistema, por favor inicia sesión.</p>
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <a href="login.php" class="btn btn-primary btn-lg">Iniciar Sesión</a>
                        <a href="register.php" class="btn btn-outline-secondary">Registrarse</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Incluir pie de página
include_once __DIR__ . '/includes/footer.php';
?>