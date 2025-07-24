<?php
// php_mvc_app/public/index.php

require_once __DIR__ . '/../config/app.php';

use App\Core\Router;
use App\Api\ApiController;
use App\Modules\Auth\AuthController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\Users\UserController;
use App\Modules\Alumnos\AlumnoController;
use App\Modules\Diplomados\DiplomadoController;

$router = new Router();

// Rutas Api
$router->add('GET', '/api/data/{table_name}', ApiController::class . '@getTableData');
$router->add('GET', '/api/users/search', ApiController::class . '@searchUsers');

// Rutas de autenticación
$router->add('GET', '/login', AuthController::class . '@showLogin');
$router->add('POST', '/login', AuthController::class . '@processLogin');
$router->add('GET', '/logout', AuthController::class . '@logout');

// Rutas del Dashboard
$router->add('GET', '/dashboard', DashboardController::class . '@index');

// Rutas de Usuarios (CRUD)
$router->add('GET', '/users', UserController::class . '@index');
$router->add('GET', '/users/create', UserController::class . '@create');
$router->add('POST', '/users/store', UserController::class . '@store');
$router->add('GET', '/users/edit/{id}', UserController::class . '@edit');
$router->add('POST', '/users/update/{id}', UserController::class . '@update');
$router->add('GET', '/users/delete/{id}', UserController::class . '@delete');

// Rutas de Alumnos (CRUD)
$router->add('GET', '/alumnos', AlumnoController::class . '@index');
$router->add('GET', '/alumnos/create', AlumnoController::class . '@create');
$router->add('POST', '/alumnos/store', AlumnoController::class . '@store');
$router->add('GET', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('POST', '/alumnos/update/{id}', AlumnoController::class . '@update');
$router->add('GET', '/alumnos/delete/{id}', AlumnoController::class . '@delete');
$router->add('POST', '/alumnos/data', AlumnoController::class . '@getAlumnosData');

// Rutas de Diplomados (CRUD)
$router->add('GET', '/diplomados', DiplomadoController::class . '@index');
$router->add('GET', '/diplomados/create', DiplomadoController::class . '@create');
$router->add('POST', '/diplomados/store', DiplomadoController::class . '@store');
$router->add('GET', '/diplomados/edit/{id}', DiplomadoController::class . '@edit');
$router->add('POST', '/diplomados/update/{id}', DiplomadoController::class . '@update');
$router->add('GET', '/diplomados/delete/{id}', DiplomadoController::class . '@delete');

// Obtener la URI actual
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/php_mvc_app/public';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Despachar la ruta
$router->dispatch($request_uri, $_SERVER['REQUEST_METHOD']);
