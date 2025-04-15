<?php
/**
 * Página de inicio de sesión
 */

// Iniciar sesión
session_start();

// Redireccionar si ya ha iniciado sesión
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Incluir archivos de configuración
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Inicializar mensaje de error
$error = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar credenciales
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validar que se proporcionaron credenciales
    if (empty($username) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        // Simular autenticación (para desarrollo)
        // En producción, esto debe validar con la base de datos
        if ($username === 'admin' && $password === 'admin123') {
            // Establecer variables de sesión
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Administrador';
            $_SESSION['user_role'] = 'admin';
            
            // Redireccionar al dashboard
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
        
        /* Para implementar autenticación con la BD Oracle, descomentar y adaptar:
        
        $conn = getOracleConnection();
        if ($conn) {
            // Implementar verificación con la base de datos
            // ...
            
            oci_close($conn);
        } else {
            $error = 'Error de conexión a la base de datos';
        }
        */
    }
}

// Establecer título de la página
$pageTitle = 'Iniciar Sesión - Sistema de Gestión de Restaurante';

// Incluir encabezado
include_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center my-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Iniciar Sesión</h1>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
                            <div class="invalid-feedback">Por favor ingrese su usuario</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">Por favor ingrese su contraseña</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
                        <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// En este caso, no incluimos el navbar, solo el footer
include_once __DIR__ . '/includes/footer.php';
?>