<?php
/**
 * Constantes globales para la aplicación
 */

// Información de la aplicación
define('APP_NAME', 'Sistema de Gestión de Restaurante');
define('APP_VERSION', '1.0.0');
define('APP_COMPANY', 'Tu Empresa');

// Configuración de rutas
define('BASE_URL', '/restaurante-app');
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('APP_ROOT', DOCUMENT_ROOT . BASE_URL);

// Estados de registros
define('ESTADO_ACTIVO', 1);
define('ESTADO_INACTIVO', 2);
define('ESTADO_PENDIENTE', 3);
define('ESTADO_ELIMINADO', 4);

// Estados de pedidos
define('ESTADO_PEDIDO_PENDIENTE', 1);    // Ajustar según la BD
define('ESTADO_PEDIDO_PREPARACION', 2);  // Ajustar según la BD
define('ESTADO_PEDIDO_LISTO', 3);        // Ajustar según la BD
define('ESTADO_PEDIDO_ENTREGADO', 4);    // Ajustar según la BD
define('ESTADO_PEDIDO_CANCELADO', 5);    // Ajustar según la BD

// Tipos de persona
define('TIPO_PERSONA_CLIENTE', 1);    // Ajustar según la BD
define('TIPO_PERSONA_EMPLEADO', 2);   // Ajustar según la BD
define('TIPO_PERSONA_PROVEEDOR', 3);  // Ajustar según la BD

// Configuración de paginación
define('REGISTROS_POR_PAGINA', 10);

// Roles de usuario
define('ROL_ADMIN', 'admin');
define('ROL_EMPLEADO', 'empleado');
define('ROL_CLIENTE', 'cliente');

// Rutas de archivos
define('LOGS_PATH', APP_ROOT . '/logs');
define('UPLOADS_PATH', APP_ROOT . '/uploads');

// Configuración de fechas
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('DEFAULT_TIMEZONE', 'America/Costa_Rica');

// Configuración de moneda
define('CURRENCY_SYMBOL', '₡');
define('CURRENCY_CODE', 'CRC');
define('DECIMAL_SEPARATOR', ',');
define('THOUSANDS_SEPARATOR', '.');
define('DECIMAL_PLACES', 2);

// Configuración de IVA
define('IVA_RATE', 0.13); // 13%