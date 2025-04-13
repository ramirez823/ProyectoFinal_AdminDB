<?php
/**
 * Menú de navegación principal
 * Contiene enlaces a todos los módulos de la aplicación
 */

// Determinar la ruta base para recursos
$basePath = '/restaurante-app';

// Determinar la página actual para marcar el elemento activo del menú
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Función helper para marcar elementos activos del menú
function isActive($module) {
    global $currentDir;
    return $currentDir === $module ? 'active' : '';
}

// Verificar si el usuario tiene sesión iniciada
$isLoggedIn = isset($_SESSION['user_id']);

// Verificar si el usuario es administrador (ajustar según tu lógica de roles)
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Enlace a inicio -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' && $currentDir === 'restaurante-app' ? 'active' : '' ?>" href="<?= $basePath ?>/index.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                <!-- Módulo de Personas -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('personas') ?>" href="#" id="navPersonas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users"></i> Personas
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navPersonas">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/personas/index.php">Lista de Personas</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/personas/create.php">Nueva Persona</a></li>
                        <?php if ($isAdmin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/clientes/index.php">Lista de Clientes</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/clientes/create.php">Nuevo Cliente</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Módulo de Menú -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('menu') ?>" href="#" id="navMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navMenu">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/menu/index.php">Catálogo de Menú</a></li>
                        <?php if ($isAdmin): ?>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/menu/create.php">Nuevo Ítem</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/menu/categorias.php">Categorías</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Módulo de Inventario -->
                <?php if ($isAdmin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('inventario') ?>" href="#" id="navInventario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-boxes"></i> Inventario
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navInventario">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/inventario/index.php">Gestión de Inventario</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/inventario/articulos.php">Artículos</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/inventario/movimientos.php">Movimientos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/inventario/vencimientos.php">Vencimientos</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/inventario/proveedores.php">Proveedores</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Módulo de Pedidos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('pedidos') ?>" href="#" id="navPedidos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clipboard-list"></i> Pedidos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navPedidos">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/pedidos/index.php">Lista de Pedidos</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/pedidos/create.php">Nuevo Pedido</a></li>
                        <?php if ($isAdmin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/pedidos/seguimiento.php">Seguimiento</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/pedidos/estados.php">Estados de Pedido</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Módulo de Facturación -->
                <?php if ($isAdmin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('facturacion') ?>" href="#" id="navFacturacion" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i> Facturación
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navFacturacion">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/facturacion/index.php">Facturas</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/facturacion/create.php">Nueva Factura</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/facturacion/pagos.php">Pagos</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/facturacion/medios-pago.php">Medios de Pago</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Módulo de Ubicaciones -->
                <?php if ($isAdmin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('ubicaciones') ?>" href="#" id="navUbicaciones" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-map-marker-alt"></i> Ubicaciones
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navUbicaciones">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/ubicaciones/index.php">Direcciones</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/ubicaciones/provincias.php">Provincias</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/ubicaciones/cantones.php">Cantones</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/modules/ubicaciones/distritos.php">Distritos</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php endif; // Fin de isLoggedIn ?>
            </ul>
            
            <?php if (!$isLoggedIn): ?>
            <!-- Opciones para usuarios no autenticados -->
            <div class="d-flex">
                <a href="<?= $basePath ?>/login.php" class="btn btn-light me-2">Iniciar Sesión</a>
                <a href="<?= $basePath ?>/register.php" class="btn btn-outline-light">Registrarse</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>