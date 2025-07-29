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
use App\Modules\Evento\EventoController;
use App\Modules\EventoAbierto\EventoAbiertoController;
use App\Modules\Diplomado\DiplomadoController;
use App\Modules\InscripcionDiplomado\InscripcionDiplomadoController;
use App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoController;
use App\Modules\InscripcionCurso\InscripcionCursoController;

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
$router->add('POST', '/users/store', UserController::class . '@create');
$router->add('GET', '/users/edit/{id}', UserController::class . '@edit');
$router->add('POST', '/users/update/{id}', UserController::class . '@update');
$router->add('GET', '/users/delete/{id}', UserController::class . '@delete');
$router->add('POST', '/users/data', UserController::class . '@getUsersData');

// Rutas de Alumnos (CRUD)
$router->add('GET', '/alumnos', AlumnoController::class . '@index');
$router->add('GET', '/alumnos/create', AlumnoController::class . '@create');
$router->add('POST', '/alumnos/create', AlumnoController::class . '@create');
$router->add('GET', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('POST', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('GET', '/alumnos/delete/{id}', AlumnoController::class . '@delete');
$router->add('POST', '/alumnos/data', AlumnoController::class . '@getAlumnosData');

// Rutas de Docentes (CRUD)
$router->add('GET', '/docentes', DocenteController::class . '@index');
$router->add('GET', '/docentes/create', DocenteController::class . '@create');
$router->add('POST', '/docentes/create', DocenteController::class . '@create');
$router->add('GET', '/docentes/edit/{id}', DocenteController::class . '@edit');
$router->add('POST', '/docentes/edit/{id}', DocenteController::class . '@edit');
$router->add('GET', '/docentes/delete/{id}', DocenteController::class . '@delete');
$router->add('POST', '/docentes/data', DocenteController::class . '@getDocentesData');

// Rutas de Coordinadores (CRUD)
$router->add('GET', '/coordinadores', CoordinadorController::class . '@index');
$router->add('GET', '/coordinadores/create', CoordinadorController::class . '@create');
$router->add('POST', '/coordinadores/create', CoordinadorController::class . '@create');
$router->add('GET', '/coordinadores/edit/{id}', CoordinadorController::class . '@edit');
$router->add('POST', '/coordinadores/edit/{id}', CoordinadorController::class . '@updaeditte');
$router->add('GET', '/coordinadores/delete/{id}', CoordinadorController::class . '@delete');
$router->add('POST', '/coordinadores/data', CoordinadorController::class . '@getCoordinadoresData');

// Rutas de Cursos (CRUD)
$router->add('GET', '/cursos', CursoController::class . '@index');
$router->add('GET', '/cursos/create', CursoController::class . '@create');
$router->add('POST', '/cursos/create', CursoController::class . '@create');
$router->add('GET', '/cursos/edit/{id}', CursoController::class . '@edit');
$router->add('POST', '/cursos/edit/{id}', CursoController::class . '@edit');
$router->add('GET', '/cursos/delete/{id}', CursoController::class . '@delete');
$router->add('POST', '/cursos/data', CursoController::class . '@getCursosData');

// Rutas de Cursos Abiertos (CRUD)
$router->add('GET', '/cursos_abiertos', CursoAbiertoController::class . '@index');
$router->add('GET', '/cursos_abiertos/create', CursoAbiertoController::class . '@create');
$router->add('POST', '/cursos_abiertos/create', CursoAbiertoController::class . '@create');
$router->add('GET', '/cursos_abiertos/edit/{id}', CursoAbiertoController::class . '@edit');
$router->add('POST', '/cursos_abiertos/edit/{id}', CursoAbiertoController::class . '@edit');
$router->add('GET', '/cursos_abiertos/delete/{id}', CursoAbiertoController::class . '@delete');
$router->add('POST', '/cursos_abiertos/data', CursoAbiertoController::class . '@getCursoAbiertoData');

// Rutas para CursoControl
$router->add('GET', '/curso_control', CursoControlController::class . '@index');
$router->add('GET', '/curso_control/create', CursoControlController::class . '@create');
$router->add('POST', '/curso_control/create', CursoControlController::class . '@create');
$router->add('GET', '/curso_control/edit/{id}', CursoControlController::class . '@edit');
$router->add('POST', '/curso_control/edit/{id}', CursoControlController::class . '@edit');
$router->add('GET', '/curso_control/delete/{id}', CursoControlController::class . '@delete');
$router->add('GET', '/curso_control/data', CursoControlController::class . '@getCursoControlData');

// Rutas para InscripcionCurso
$router->add('GET', '/inscripcion_curso', InscripcionCursoController::class . '@index');
$router->add('GET', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('POST', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('GET', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('POST', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('GET', '/inscripcion_curso/delete/{id}', InscripcionCursoController::class . '@delete');
$router->add('POST', '/inscripcion_curso/data', InscripcionCursoController::class . '@getInscripcionCursoData');

// Rutas para Evento
$router->add('GET', '/evento', EventoController::class . '@index');
$router->add('GET', '/evento/create', EventoController::class . '@create');
$router->add('POST', '/evento/create', EventoController::class . '@create');
$router->add('GET', '/evento/edit/{id}', EventoController::class . '@edit');
$router->add('POST', '/evento/edit/{id}', EventoController::class . '@edit');
$router->add('GET', '/evento/delete/{id}', EventoController::class . '@delete');
$router->add('POST', '/evento/list', EventoController::class . '@getEventoData');

// Rutas para EventoAbierto
$router->add('GET', '/evento_abierto', EventoAbiertoController::class . '@index');
$router->add('GET', '/evento_abierto/create', EventoAbiertoController::class . '@create');
$router->add('POST', '/evento_abierto/create', EventoAbiertoController::class . '@create');
$router->add('GET', '/evento_abierto/edit/{id}', EventoAbiertoController::class . '@edit');
$router->add('POST', '/evento_abierto/edit/{id}', EventoAbiertoController::class . '@edit');
$router->add('GET', '/evento_abierto/delete/{id}', EventoAbiertoController::class . '@delete');
$router->add('POST', '/evento_abierto/data', EventoAbiertoController::class . '@getEventoAbiertoData');

// Rutas de Diplomados (CRUD)
$router->add('GET', '/diplomado', DiplomadoController::class . '@index');
$router->add('GET', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('POST', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('GET', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('POST', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('GET', '/diplomado/delete/{id}', DiplomadoController::class . '@delete');
$router->add('POST', '/diplomado/data', DiplomadoController::class . '@getDiplomadoData');

// Rutas para Capítulo
$router->add('GET', '/capitulo', App\Modules\Capitulo\CapituloController::class . '@index');
$router->add('GET', '/capitulo/create/{diplomadoId}', App\Modules\Capitulo\CapituloController::class . '@create');
$router->add('POST', '/capitulo/create', App\Modules\Capitulo\CapituloController::class . '@create');
$router->add('GET', '/capitulo/edit/{id}', App\Modules\Capitulo\CapituloController::class . '@edit');
$router->add('POST', '/capitulo/edit/{id}', App\Modules\Capitulo\CapituloController::class . '@edit');
$router->add('GET', '/capitulo/delete/{id}', App\Modules\Capitulo\CapituloController::class . '@delete');
$router->add('POST', '/capitulo/data', App\Modules\Capitulo\CapituloController::class . '@getCapituloData');

// Rutas para DiplomadoAbierto
$router->add('GET', '/diplomado_abierto', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@index');
$router->add('GET', '/diplomado_abierto/create', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@create');
$router->add('POST', '/diplomado_abierto/create', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@create');
$router->add('GET', '/diplomado_abierto/edit/{id}', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@edit');
$router->add('POST', '/diplomado_abierto/edit/{id}', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@edit');
$router->add('GET', '/diplomado_abierto/delete/{id}', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@delete');
$router->add('POST', '/diplomado_abierto/data', App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class . '@getDiplomadoAbiertoData');

// Rutas para InscripcionDiplomado
$router->add('GET', '/inscripcion_diplomado', InscripcionDiplomadoController::class . '@index');
$router->add('GET', '/inscripcion_diplomado/create', InscripcionDiplomadoController::class . '@create');
$router->add('POST', '/inscripcion_diplomado/create', InscripcionDiplomadoController::class . '@create');
$router->add('GET', '/inscripcion_diplomado/edit/{id}', InscripcionDiplomadoController::class . '@edit');
$router->add('POST', '/inscripcion_diplomado/edit/{id}', InscripcionDiplomadoController::class . '@edit');
$router->add('GET', '/inscripcion_diplomado/delete/{id}', InscripcionDiplomadoController::class . '@delete');
$router->add('POST', '/inscripcion_diplomado/data', InscripcionDiplomadoController::class . '@getInscripcionDiplomadoData');

// Rutas para PreinscripcionDiplomado
$router->add('GET', '/preinscripcion_diplomado', PreinscripcionDiplomadoController::class . '@index');
$router->add('GET', '/preinscripcion_diplomado/create', PreinscripcionDiplomadoController::class . '@create');
$router->add('POST', '/preinscripcion_diplomado/create', PreinscripcionDiplomadoController::class . '@create');
$router->add('POST', '/preinscripcion_diplomado/search_alumno', PreinscripcionDiplomadoController::class . '@searchAlumno');
$router->add('POST', '/preinscripcion_diplomado/create_alumno', PreinscripcionDiplomadoController::class . '@createAlumno');
$router->add('POST', '/preinscripcion_diplomado/get_diplomados_abiertos', PreinscripcionDiplomadoController::class . '@getDiplomadosAbiertosForPreinscripcion');

// Obtener la URI actual
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/php_mvc_app/public';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Despachar la ruta
$router->dispatch($request_uri, $_SERVER['REQUEST_METHOD']);
