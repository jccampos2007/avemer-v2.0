<?php
// php_mvc_app/config/app.php
// Configuración de la aplicación y la base de datos

// Iniciar sesión (necesario para todas las páginas que la usen)
session_start();

// Load .env
$env = parse_ini_file(__DIR__ . '/../.env');

define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);

// Constantes para los tipos de usuario
define('TIPO_USUARIO_ADMIN', 1);
define('TIPO_USUARIO_ALUMNO', 2);

// Base URL de la aplicación (ajusta si es necesario para subdirectorios)
define('BASE_URL', 'http://localhost/php_mvc_app/public/'); // Si está en un subdirectorio, ej: 'http://localhost/php_mvc_app/public/'

// Rutas de directorios (relativas al archivo que las incluye)
define('APP_ROOT', dirname(__DIR__)); // Directorio 'php_mvc_app'
define('MODULES_PATH', APP_ROOT . '/App/Modules/'); // Nuevo directorio para módulos (ajustado para la nueva base_dir)
define('CORE_PATH', APP_ROOT . '/App/Core/'); // Ajustado para la nueva base_dir
define('VIEWS_LAYOUT_PATH', APP_ROOT . '/App/Views/layout/'); // Vistas de layout globales (ajustado para la nueva base_dir)

// Autoload de clases (PSR-4 compatible mejorado)
spl_autoload_register(function ($class) {
    // Prefijo del namespace del proyecto
    $prefix = 'App\\';

    // Directorio base para el prefijo del namespace (ej. /path/to/php_mvc_app/app/)
    // Asegura que el base_dir siempre apunte a la carpeta 'app'
    $base_dir = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

    // ¿La clase usa el prefijo del namespace?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, pasar al siguiente autoloader registrado
        return;
    }

    // Obtener el nombre de la clase relativo (sin el prefijo del namespace)
    $relative_class = substr($class, $len); // Ej: 'Core\Router' o 'Modules\Users\UserModel'

    // Reemplazar los separadores de namespace con separadores de directorio,
    // añadir el directorio base y la extensión .php
    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    // Si el archivo existe, incluirlo
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Opcional: Para depuración, puedes registrar las clases que no se encuentran
        error_log("Autoload: Clase no encontrada - " . $class . " (Buscado en: " . $file . ")");
    }
});
