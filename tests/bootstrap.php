<?php

session_start();
$_SESSION['user_id'] = 1;

define('BASE_URL', 'http://localhost/php_mvc_app/public/');
define('APP_ROOT', dirname(__DIR__));
define('MODULES_PATH', APP_ROOT . '/App/Modules/');
define('CORE_PATH', APP_ROOT . '/App/Core/');
define('VIEWS_LAYOUT_PATH', APP_ROOT . '/App/Layout/');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'php_mvc_app_test');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASS', getenv('DB_PASS') ?: 'Admin.2026*MySQL');

define('TIPO_USUARIO_ADMIN', 1);
define('TIPO_USUARIO_ALUMNO', 2);

require_once APP_ROOT . '/vendor/autoload.php';
