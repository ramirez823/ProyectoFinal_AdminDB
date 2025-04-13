<?php
/**
 * Funciones helpers generales para la aplicación
 */

/**
 * Limpia y sanitiza una entrada para prevenir XSS
 * @param string $data Dato a sanitizar
 * @return string Dato sanitizado
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valida si una cédula tiene el formato correcto
 * @param string $cedula Cédula a validar
 * @return bool True si es válida, False en caso contrario
 */
function validarCedula($cedula) {
    // Validar cédula nacional (9 dígitos)
    if (preg_match('/^[0-9]{9}$/', $cedula)) {
        return true;
    }
    
    // Validar DIMEX (12 dígitos)
    if (preg_match('/^[0-9]{12}$/', $cedula)) {
        return true;
    }
    
    return false;
}

/**
 * Formatea una fecha de formato SQL a formato local
 * @param string $fecha Fecha en formato SQL (YYYY-MM-DD)
 * @param string $formato Formato de salida (default: d/m/Y)
 * @return string Fecha formateada
 */
function formatearFecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) return '';
    
    $date = new DateTime($fecha);
    return $date->format($formato);
}

/**
 * Formatea un número como moneda
 * @param float $monto Monto a formatear
 * @param string $simbolo Símbolo de moneda (default: ₡)
 * @return string Monto formateado
 */
function formatearMoneda($monto, $simbolo = '₡') {
    return $simbolo . ' ' . number_format($monto, 2, ',', '.');
}

/**
 * Obtiene el nombre completo concatenando nombre y apellidos
 * @param string $nombre Nombre
 * @param string $apellido1 Primer apellido
 * @param string $apellido2 Segundo apellido (opcional)
 * @return string Nombre completo
 */
function nombreCompleto($nombre, $apellido1, $apellido2 = '') {
    $nombreCompleto = $nombre . ' ' . $apellido1;
    if (!empty($apellido2)) {
        $nombreCompleto .= ' ' . $apellido2;
    }
    return $nombreCompleto;
}

/**
 * Genera un componente de paginación
 * @param int $totalRegistros Total de registros
 * @param int $registrosPorPagina Registros por página
 * @param int $paginaActual Página actual
 * @param string $urlBase URL base para los enlaces
 * @return string HTML del componente de paginación
 */
function generarPaginacion($totalRegistros, $registrosPorPagina, $paginaActual, $urlBase) {
    if ($totalRegistros <= $registrosPorPagina) {
        return ''; // No es necesario paginar
    }
    
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
    
    $html = '<nav aria-label="Paginación">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    $prevDisabled = $paginaActual <= 1 ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '">';
    $html .= '<a class="page-link" href="' . $urlBase . 'pagina=' . ($paginaActual - 1) . '" tabindex="-1" aria-disabled="' . ($prevDisabled ? 'true' : 'false') . '">Anterior</a>';
    $html .= '</li>';
    
    // Páginas
    $rangoInicio = max(1, $paginaActual - 2);
    $rangoFin = min($totalPaginas, $paginaActual + 2);
    
    if ($rangoInicio > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $urlBase . 'pagina=1">1</a></li>';
        if ($rangoInicio > 2) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $rangoInicio; $i <= $rangoFin; $i++) {
        $active = $i == $paginaActual ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $urlBase . 'pagina=' . $i . '">' . $i . '</a></li>';
    }
    
    if ($rangoFin < $totalPaginas) {
        if ($rangoFin < $totalPaginas - 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $urlBase . 'pagina=' . $totalPaginas . '">' . $totalPaginas . '</a></li>';
    }
    
    // Botón siguiente
    $nextDisabled = $paginaActual >= $totalPaginas ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="' . $urlBase . 'pagina=' . ($paginaActual + 1) . '" aria-disabled="' . ($nextDisabled ? 'true' : 'false') . '">Siguiente</a>';
    $html .= '</li>';
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Genera un log de acciones del sistema
 * @param string $accion Acción realizada
 * @param string $detalles Detalles de la acción
 * @param int $usuario_id ID del usuario que realizó la acción
 * @return bool True si se registró correctamente, False en caso contrario
 */
function registrarLog($accion, $detalles, $usuario_id = null) {
    // Si no hay usuario_id y hay sesión, tomarlo de ahí
    if (is_null($usuario_id) && isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];
    }
    
    // Registro básico en archivo de texto
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $navegador = $_SERVER['HTTP_USER_AGENT'];
    
    $logLine = "[{$fecha}] - Usuario: {$usuario_id} - IP: {$ip} - Acción: {$accion} - Detalles: {$detalles}\n";
    
    $logFile = __DIR__ . '/../logs/system_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    // Crear directorio si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    return file_put_contents($logFile, $logLine, FILE_APPEND) !== false;
}

/**
 * Determina si el usuario tiene un rol específico
 * @param string $rol Rol a verificar
 * @return bool True si tiene el rol, False en caso contrario
 */
function tieneRol($rol) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if ($_SESSION['user_role'] === 'admin') {
        return true; // El administrador tiene todos los roles
    }
    
    return $_SESSION['user_role'] === $rol;
}

/**
 * Verifica si un usuario tiene permiso para acceder a una página
 * Si no tiene permiso, lo redirige a otra página
 * @param string $rolRequerido Rol requerido para acceder
 * @param string $redireccion URL de redirección si no tiene permiso
 */
function verificarPermiso($rolRequerido, $redireccion = '/login.php') {
    if (!isset($_SESSION['user_id']) || !tieneRol($rolRequerido)) {
        $basePath = '/restaurante-app';
        $mensaje = urlencode('No tienes permiso para acceder a esta página');
        header("Location: {$basePath}{$redireccion}?mensaje={$mensaje}&error=1");
        exit;
    }
}

/**
 * Genera un token CSRF para formularios
 * @return string Token CSRF
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica si un token CSRF es válido
 * @param string $token Token a verificar
 * @return bool True si es válido, False en caso contrario
 */
function verificarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}