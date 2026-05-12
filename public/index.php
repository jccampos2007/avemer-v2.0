<?php
// php_mvc_app/public/index.php

require_once __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../vendor/autoload.php';

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
use App\Modules\Evento\EventoController;
use App\Modules\EventoAbierto\EventoAbiertoController;
use App\Modules\InscripcionEvento\InscripcionEventoController;
use App\Modules\Diplomado\DiplomadoController;
use App\Modules\DiplomadoAbierto\DiplomadoAbiertoController;
use App\Modules\InscripcionDiplomado\InscripcionDiplomadoController;
use App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoController;
use App\Modules\Capitulo\CapituloController;
use App\Modules\Maestria\MaestriaController;
use App\Modules\MaestriaAbierto\MaestriaAbiertoController;
use App\Modules\InscripcionMaestria\InscripcionMaestriaController;
use App\Modules\Cuota\CuotaController;
use App\Modules\Pagos\PagoController;
use App\Modules\Mensajes\MensajesController;
use App\Modules\Envios\EnviosController;
use App\Modules\Correo\CorreoController;
use App\Modules\Sede\SedeController;
use App\Modules\Banco\BancoController;
use App\Modules\Duracion\DuracionController;
use App\Modules\ProfesionOficio\ProfesionOficioController;
use App\Views\LandingPage\PreinscripcionLandingController;

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

// Rutas para InscripcionEvento
$router->add('GET', '/inscripcion_evento', InscripcionEventoController::class . '@index');
$router->add('GET', '/inscripcion_evento/create', InscripcionEventoController::class . '@create');
$router->add('POST', '/inscripcion_evento/create', InscripcionEventoController::class . '@create');
$router->add('GET', '/inscripcion_evento/edit/{id}', InscripcionEventoController::class . '@edit');
$router->add('POST', '/inscripcion_evento/edit/{id}', InscripcionEventoController::class . '@edit');
$router->add('GET', '/inscripcion_evento/delete/{id}', InscripcionEventoController::class . '@delete');
$router->add('POST', '/inscripcion_evento/data', InscripcionEventoController::class . '@getInscripcionEventoData');

// Rutas de Diplomados (CRUD)
$router->add('GET', '/diplomado', DiplomadoController::class . '@index');
$router->add('GET', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('POST', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('GET', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('POST', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('GET', '/diplomado/delete/{id}', DiplomadoController::class . '@delete');
$router->add('POST', '/diplomado/data', DiplomadoController::class . '@getDiplomadoData');

// Rutas para Capítulo
$router->add('GET', '/capitulo', CapituloController::class . '@index');
$router->add('GET', '/capitulo/create/{diplomadoId}', CapituloController::class . '@create');
$router->add('POST', '/capitulo/create', CapituloController::class . '@create');
$router->add('GET', '/capitulo/edit/{id}', CapituloController::class . '@edit');
$router->add('POST', '/capitulo/edit/{id}', CapituloController::class . '@edit');
$router->add('GET', '/capitulo/delete/{id}', CapituloController::class . '@delete');
$router->add('POST', '/capitulo/data', CapituloController::class . '@getCapituloData');

// Rutas para DiplomadoAbierto
$router->add('GET', '/diplomado_abierto', DiplomadoAbiertoController::class . '@index');
$router->add('GET', '/diplomado_abierto/create', DiplomadoAbiertoController::class . '@create');
$router->add('POST', '/diplomado_abierto/create', DiplomadoAbiertoController::class . '@create');
$router->add('GET', '/diplomado_abierto/edit/{id}', DiplomadoAbiertoController::class . '@edit');
$router->add('POST', '/diplomado_abierto/edit/{id}', DiplomadoAbiertoController::class . '@edit');
$router->add('GET', '/diplomado_abierto/delete/{id}', DiplomadoAbiertoController::class . '@delete');
$router->add('POST', '/diplomado_abierto/data', DiplomadoAbiertoController::class . '@getDiplomadoAbiertoData');

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

// Rutas para Maestria
$router->add('GET', '/maestria', MaestriaController::class . '@index');
$router->add('GET', '/maestria/create', MaestriaController::class . '@create');
$router->add('POST', '/maestria/create', MaestriaController::class . '@create');
$router->add('GET', '/maestria/edit/{id}', MaestriaController::class . '@edit');
$router->add('POST', '/maestria/edit/{id}', MaestriaController::class . '@edit');
$router->add('GET', '/maestria/delete/{id}', MaestriaController::class . '@delete');
$router->add('POST', '/maestria/data', MaestriaController::class . '@getMaestriaData');

// Rutas para MaestriaAbierto
$router->add('GET', '/maestria_abierto', MaestriaAbiertoController::class . '@index');
$router->add('GET', '/maestria_abierto/create', MaestriaAbiertoController::class . '@create');
$router->add('POST', '/maestria_abierto/create', MaestriaAbiertoController::class . '@create');
$router->add('GET', '/maestria_abierto/edit/{id}', MaestriaAbiertoController::class . '@edit');
$router->add('POST', '/maestria_abierto/edit/{id}', MaestriaAbiertoController::class . '@edit');
$router->add('GET', '/maestria_abierto/delete/{id}', MaestriaAbiertoController::class . '@delete');
$router->add('POST', '/maestria_abierto/data', MaestriaAbiertoController::class . '@getMaestriaAbiertoData');

// Rutas para InscripcionMaestria
$router->add('GET', '/inscripcion_maestria', InscripcionMaestriaController::class . '@index');
$router->add('GET', '/inscripcion_maestria/create', InscripcionMaestriaController::class . '@create');
$router->add('POST', '/inscripcion_maestria/create', InscripcionMaestriaController::class . '@create');
$router->add('GET', '/inscripcion_maestria/edit/{id}', InscripcionMaestriaController::class . '@edit');
$router->add('POST', '/inscripcion_maestria/edit/{id}', InscripcionMaestriaController::class . '@edit');
$router->add('GET', '/inscripcion_maestria/delete/{id}', InscripcionMaestriaController::class . '@delete');
$router->add('POST', '/inscripcion_maestria/data', InscripcionMaestriaController::class . '@getInscripcionMaestriaData');

// ERutas para Cuota
$router->add('GET', '/cuota', CuotaController::class . '@index');
$router->add('GET', '/cuota/create', CuotaController::class . '@create');
$router->add('POST', '/cuota/create', CuotaController::class . '@create');
$router->add('GET', '/cuota/edit/{id}', CuotaController::class . '@edit');
$router->add('POST', '/cuota/edit/{id}', CuotaController::class . '@edit');
$router->add('GET', '/cuota/delete/{id}', CuotaController::class . '@delete');
$router->add('POST', '/cuota/generateDebt', CuotaController::class . '@generateDebt');
$router->add('GET', '/cuota/getCuotasByOfferData', CuotaController::class . '@getCuotasByOfferData');
$router->add('GET', '/cuota/getAcademicOffersByType', CuotaController::class . '@getAcademicOffersByType');
$router->add('GET', '/cuota/getStudentsForDebtGeneration', CuotaController::class . '@getStudentsForDebtGeneration');

// Rutas para Sede
$router->add('GET', '/sede', SedeController::class . '@index');
$router->add('GET', '/sede/create', SedeController::class . '@create');
$router->add('POST', '/sede/store', SedeController::class . '@store');
$router->add('GET', '/sede/edit/{id}', SedeController::class . '@edit');
$router->add('POST', '/sede/update/{id}', SedeController::class . '@update');
$router->add('GET', '/sede/delete/{id}', SedeController::class . '@delete');
$router->add('POST', '/sede/getSedesData', SedeController::class . '@getSedesData');

// Rutas para Banco
$router->add('GET', '/banco', BancoController::class . '@index');
$router->add('GET', '/banco/create', BancoController::class . '@create');
$router->add('POST', '/banco/store', BancoController::class . '@store');
$router->add('GET', '/banco/edit/{id}', BancoController::class . '@edit');
$router->add('POST', '/banco/update/{id}', BancoController::class . '@update');
$router->add('GET', '/banco/delete/{id}', BancoController::class . '@delete');
$router->add('POST', '/banco/getBancosData', BancoController::class . '@getBancosData');

// Rutas para Duracion
$router->add('GET', '/duracion', DuracionController::class . '@index');
$router->add('GET', '/duracion/create', DuracionController::class . '@create');
$router->add('POST', '/duracion/store', DuracionController::class . '@store');
$router->add('GET', '/duracion/edit/{id}', DuracionController::class . '@edit');
$router->add('POST', '/duracion/update/{id}', DuracionController::class . '@update');
$router->add('GET', '/duracion/delete/{id}', DuracionController::class . '@delete');
$router->add('POST', '/duracion/getDuracionesData', DuracionController::class . '@getDuracionesData');

// Rutas para ProfesionOficio
$router->add('GET', '/profesion_oficio', ProfesionOficioController::class . '@index');
$router->add('GET', '/profesion_oficio/create', ProfesionOficioController::class . '@create');
$router->add('POST', '/profesion_oficio/store', ProfesionOficioController::class . '@store');
$router->add('GET', '/profesion_oficio/edit/{id}', ProfesionOficioController::class . '@edit');
$router->add('POST', '/profesion_oficio/update/{id}', ProfesionOficioController::class . '@update');
$router->add('GET', '/profesion_oficio/delete/{id}', ProfesionOficioController::class . '@delete');
$router->add('POST', '/profesion_oficio/getProfesionesData', ProfesionOficioController::class . '@getProfesionesData');
$router->add('GET', '/pago', PagoController::class . '@index');

// Rutas para mensajes
$router->add('GET', '/mensajes', MensajesController::class . '@index');
$router->add('GET', '/mensajes/create', MensajesController::class . '@create');
$router->add('POST', '/mensajes/create', MensajesController::class . '@create');
$router->add('GET', '/mensajes/edit/{id}', MensajesController::class . '@edit');
$router->add('POST', '/mensajes/edit/{id}', MensajesController::class . '@edit');
$router->add('GET', '/mensajes/delete/{id}', MensajesController::class . '@delete');
$router->add('POST', '/mensajes/data', MensajesController::class . '@getMensajesData');

// Rutas para envios
$router->add('GET', '/listaenvio', EnviosController::class . '@index'); 
$router->add('GET', '/listaenvio/create', EnviosController::class . '@create');
$router->add('POST', '/listaenvio/create', EnviosController::class . '@create');
$router->add('GET', '/listaenvio/edit/{id}', EnviosController::class . '@edit');
$router->add('POST', '/listaenvio/edit/{id}', EnviosController::class . '@edit');
$router->add('GET', '/listaenvio/delete/{id}', EnviosController::class . '@delete');
$router->add('POST', '/envios/data', EnviosController::class . '@getEnviosData');

// Rutas para correo
$router->add('GET', '/listacorreo', CorreoController::class . '@index');
$router->add('GET', '/correo/create', CorreoController::class . '@create');
$router->add('POST', '/correo/create', CorreoController::class . '@create');
$router->add('POST', '/correo/sendChecked', CorreoController::class . '@sendChecked');
$router->add('GET', '/correo/getCorreosByOfferData', CorreoController::class . '@getCorreosByOfferData');
$router->add('GET', '/correo/getAcademicOffersByType', CorreoController::class . '@getAcademicOffersByType');
$router->add('GET', '/correo/getStudentsForDebtGeneration', CorreoController::class . '@getStudentsForDebtGeneration');
$router->add('GET', '/correo/getMensajes', CorreoController::class . '@getMensajes');

$router->add('GET', '/preinscripcionlanding', PreinscripcionLandingController::class . '@index');

// Obtener la URI actual
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/php_mvc_app/public';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Despachar la ruta
$router->dispatch($request_uri, $_SERVER['REQUEST_METHOD']);
