<?php
/**
 * Header común para todas las páginas
 * Incluye metadatos, CSS, JavaScript y la parte superior de la estructura HTML
 */
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar el título de la página
$pageTitle = $pageTitle ?? 'Sistema de Gestión de Restaurante';

// Determinar la ruta base para recursos
$basePath = '/restaurante-app';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= $basePath ?>/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- jQuery primero, luego Popper.js, luego Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Contenido del encabezado -->
        <header class="bg-dark text-white py-3">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0">
                        <a href="<?= $basePath ?>/index.php" class="text-white text-decoration-none">
                            <i class="fas fa-utensils me-2"></i>Gestión de Restaurante
                        </a>
                    </h1>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="<?= $basePath ?>/profile.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $basePath ?>/logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <!-- Mostrar mensajes de alerta si existen -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?= isset($_GET['error']) && $_GET['error'] ? 'danger' : 'success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['mensaje']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Inicio del contenido principal -->
        <main class="py-4"></main>