<?php
// php_mvc_app/public/index.php

require_once __DIR__ . '/../config/app.php';

use App\Core\Router;
use App\Api\ApiController;
use App\Modules\Auth\AuthController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\Users\UserController;
use App\Modules\Alumnos\AlumnoController;
use App\Modules\Docentes\DocenteController;
use App\Modules\Coordinadores\CoordinadorController;
use App\Modules\Cursos\CursoController;
use App\Modules\CursoAbierto\CursoAbiertoController;
use App\Modules\CursoControl\CursoControlController;
use App\Modules\InscripcionCurso\InscripcionCursoController;
use App\Modules\Diplomados\DiplomadoController;

$router = new Router();

// Rutas Api
$router->add('GET', '/api/data/{table_name}', ApiController::class . '@getTableData');
$router->add('GET', '/api/search/{table_name}', ApiController::class . '@getAutocompleteData');

// Rutas de autenticación
$router->add('GET', '/login', AuthController::class . '@showLogin');
$router->add('POST', '/login', AuthController::class . '@processLogin');
$router->add('GET', '/logout', AuthController::class . '@logout');

// Rutas del Dashboard
$router->add('GET', '/dashboard', DashboardController::class . '@index');

// Rutas de Usuarios (CRUD)
$router->add('GET', '/users', UserController::class . '@index');
$router->add('GET', '/users/create', UserController::class . '@create');
$router->add('GET', '/users/edit/{id}', UserController::class . '@edit');
$router->add('POST', '/users/update/{id}', UserController::class . '@update');
$router->add('GET', '/users/delete/{id}', UserController::class . '@delete');
$router->add('POST', '/users/data', UserController::class . '@getUsersData');

// Rutas de Alumnos (CRUD)
$router->add('GET', '/alumnos', AlumnoController::class . '@index');
$router->add('GET', '/alumnos/create', AlumnoController::class . '@create');
$router->add('GET', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('POST', '/alumnos/update/{id}', AlumnoController::class . '@update');
$router->add('GET', '/alumnos/delete/{id}', AlumnoController::class . '@delete');
$router->add('POST', '/alumnos/data', AlumnoController::class . '@getAlumnosData');

// Rutas de Docentes (CRUD)
$router->add('GET', '/docentes', DocenteController::class . '@index');
$router->add('GET', '/docentes/create', DocenteController::class . '@create');
$router->add('GET', '/docentes/edit/{id}', DocenteController::class . '@edit');
$router->add('POST', '/docentes/update/{id}', DocenteController::class . '@update');
$router->add('GET', '/docentes/delete/{id}', DocenteController::class . '@delete');
$router->add('POST', '/docentes/data', DocenteController::class . '@getDocentesData');

// Rutas de Coordinadores (CRUD)
$router->add('GET', '/coordinadores', CoordinadorController::class . '@index');
$router->add('GET', '/coordinadores/create', CoordinadorController::class . '@create');
$router->add('GET', '/coordinadores/edit/{id}', CoordinadorController::class . '@edit');
$router->add('POST', '/coordinadores/update/{id}', CoordinadorController::class . '@update');
$router->add('GET', '/coordinadores/delete/{id}', CoordinadorController::class . '@delete');
$router->add('POST', '/coordinadores/data', CoordinadorController::class . '@getCoordinadoresData');

// Rutas de Cursos (CRUD)
$router->add('GET', '/cursos', CursoController::class . '@index');
$router->add('GET', '/cursos/create', CursoController::class . '@create');
$router->add('GET', '/cursos/edit/{id}', CursoController::class . '@edit');
$router->add('POST', '/cursos/update/{id}', CursoController::class . '@update');
$router->add('GET', '/cursos/delete/{id}', CursoController::class . '@delete');
$router->add('POST', '/cursos/data', CursoController::class . '@getCursosData');

// Rutas de Cursos Abiertos (CRUD)
$router->add('GET', '/cursos_abiertos', CursoAbiertoController::class . '@index');
$router->add('GET', '/cursos_abiertos/create', CursoAbiertoController::class . '@create');
$router->add('POST', '/cursos_abiertos/store', CursoAbiertoController::class . '@store'); // Descomenta en el controller si lo vas a usar
$router->add('GET', '/cursos_abiertos/edit/{id}', CursoAbiertoController::class . '@edit');
$router->add('POST', '/cursos_abiertos/update/{id}', CursoAbiertoController::class . '@update');
$router->add('GET', '/cursos_abiertos/delete/{id}', CursoAbiertoController::class . '@delete');
$router->add('POST', '/cursos_abiertos/data', CursoAbiertoController::class . '@getCursoAbiertoData');

// Rutas para CursoControl
$router->add('GET', '/curso_control', CursoControlController::class . '@index');
$router->add('GET', '/curso_control/create', CursoControlController::class . '@create');
$router->add('POST', '/curso_control/create', CursoControlController::class . '@create');
$router->add('GET', '/curso_control/edit/{id}', CursoControlController::class . '@edit');
$router->add('POST', '/curso_control/edit/{id}', CursoControlController::class . '@edit');
$router->add('POST', '/curso_control/delete/{id}', CursoControlController::class . '@delete');
$router->add('POST', '/curso_control/data', CursoControlController::class . '@getCursoControlData');

// Rutas para InscripcionCurso
$router->add('GET', '/inscripcion_curso', InscripcionCursoController::class . '@index');
$router->add('GET', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('POST', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('GET', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('POST', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('POST', '/inscripcion_curso/delete/{id}', InscripcionCursoController::class . '@delete');

// Ruta para el AJAX de DataTables (server-side processing)
$router->add('POST', '/api/inscripcion_curso_data', InscripcionCursoController::class . '@getInscripcionCursoData');

// Rutas de Diplomados (CRUD)
$router->add('GET', '/diplomados', DiplomadoController::class . '@index');
$router->add('GET', '/diplomados/create', DiplomadoController::class . '@create');
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
