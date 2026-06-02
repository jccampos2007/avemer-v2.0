<?php
// php_mvc_app/public/index.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Core Imports
use App\Core\Router;
use App\Core\AssetController;
use App\Api\ApiController;

// Modules Imports (Alphabetical)
use App\Modules\Alumnos\AlumnoController;
use App\Modules\Auth\AuthController;
use App\Modules\Banco\BancoController;
use App\Modules\Capitulo\CapituloController;
use App\Modules\Ciudad\CiudadController;
use App\Modules\Coordinadores\CoordinadorController;
use App\Modules\Correo\CorreoController;
use App\Modules\Cronograma\CronogramaController;
use App\Modules\Cuota\CuotaController;
use App\Modules\CursoAbierto\CursoAbiertoController;
use App\Modules\Cursos\CursoController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\Diplomado\DiplomadoController;
use App\Modules\DiplomadoAbierto\DiplomadoAbiertoController;
use App\Modules\DiplomadoControl\DiplomadoControlController;
use App\Modules\Docentes\DocenteController;
use App\Modules\Duracion\DuracionController;
use App\Modules\Envios\EnviosController;
use App\Modules\Evento\EventoController;
use App\Modules\EventoAbierto\EventoAbiertoController;
use App\Modules\Grupo\GrupoController;
use App\Modules\InscripcionCurso\InscripcionCursoController;
use App\Modules\InscripcionDiplomado\InscripcionDiplomadoController;
use App\Modules\InscripcionEvento\InscripcionEventoController;
use App\Modules\InscripcionMaestria\InscripcionMaestriaController;
use App\Modules\Maestria\MaestriaController;
use App\Modules\MaestriaAbierto\MaestriaAbiertoController;
use App\Modules\Mensajes\MensajesController;
use App\Modules\Pagos\PagoController;
use App\Modules\Asistencia\AsistenciaController;
use App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoController;
use App\Modules\PreinscripcionLanding\PreinscripcionLandingController;
use App\Modules\ProfesionOficio\ProfesionOficioController;
use App\Modules\Sede\SedeController;
use App\Modules\Users\UserController;

$router = new Router();

// ==========================================
// 1. RUTAS DE API Y CORE
// ==========================================
$router->add('GET', '/api/data/{table_name}', ApiController::class . '@getTableData');
$router->add('GET', '/api/search/{table_name}', ApiController::class . '@getAutocompleteData');
$router->add('GET', '/asset/js/{module}/{file}', AssetController::class . '@serveJs');

// ==========================================
// 2. AUTENTICACIÓN Y PANEL PRINCIPAL (DASHBOARD)
// ==========================================
$router->add('GET', '/login', AuthController::class . '@showLogin');
$router->add('POST', '/login', AuthController::class . '@processLogin');
$router->add('GET', '/logout', AuthController::class . '@logout');
$router->add('GET', '/dashboard', DashboardController::class . '@index');

// ==========================================
// 3. GESTIÓN DE USUARIOS Y ROLES (GRUPOS/PERMISOS)
// ==========================================
$router->add('GET', '/users', UserController::class . '@index');
$router->add('GET', '/users/create', UserController::class . '@create');
$router->add('POST', '/users/store', UserController::class . '@store');
$router->add('GET', '/users/edit/{id}', UserController::class . '@edit');
$router->add('POST', '/users/update/{id}', UserController::class . '@update');
$router->add('GET', '/users/delete/{id}', UserController::class . '@delete');
$router->add('POST', '/users/data', UserController::class . '@getUsersData');

$router->add('GET', '/grupo', GrupoController::class . '@index');
$router->add('GET', '/grupo/create', GrupoController::class . '@create');
$router->add('POST', '/grupo/store', GrupoController::class . '@store');
$router->add('GET', '/grupo/edit/{id}', GrupoController::class . '@edit');
$router->add('POST', '/grupo/update/{id}', GrupoController::class . '@update');
$router->add('GET', '/grupo/delete/{id}', GrupoController::class . '@delete');
$router->add('POST', '/grupo/data', GrupoController::class . '@getGroupsData');
$router->add('GET', '/grupo/permissions/{id}', GrupoController::class . '@getPermissions');
$router->add('POST', '/grupo/save_permissions', GrupoController::class . '@savePermissions');

// ==========================================
// 4. MÓDULOS DE ALUMNOS, INSTRUCTORES (DOCENTES) Y COORDINADORES
// ==========================================
// Alumnos
$router->add('GET', '/alumnos', AlumnoController::class . '@index');
$router->add('GET', '/alumnos/create', AlumnoController::class . '@create');
$router->add('POST', '/alumnos/create', AlumnoController::class . '@create');
$router->add('GET', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('POST', '/alumnos/edit/{id}', AlumnoController::class . '@edit');
$router->add('GET', '/alumnos/delete/{id}', AlumnoController::class . '@delete');
$router->add('POST', '/alumnos/data', AlumnoController::class . '@getAlumnosData');

// Docentes (Instructores)
$router->add('GET', '/docentes', DocenteController::class . '@index');
$router->add('GET', '/docentes/create', DocenteController::class . '@create');
$router->add('POST', '/docentes/create', DocenteController::class . '@create');
$router->add('GET', '/docentes/edit/{id}', DocenteController::class . '@edit');
$router->add('POST', '/docentes/edit/{id}', DocenteController::class . '@edit');
$router->add('GET', '/docentes/delete/{id}', DocenteController::class . '@delete');
$router->add('POST', '/docentes/data', DocenteController::class . '@getDocentesData');

// Coordinadores (Corregida ruta POST edit para apuntar al método @edit)
$router->add('GET', '/coordinadores', CoordinadorController::class . '@index');
$router->add('GET', '/coordinadores/create', CoordinadorController::class . '@create');
$router->add('POST', '/coordinadores/create', CoordinadorController::class . '@create');
$router->add('GET', '/coordinadores/edit/{id}', CoordinadorController::class . '@edit');
$router->add('POST', '/coordinadores/edit/{id}', CoordinadorController::class . '@edit');
$router->add('GET', '/coordinadores/delete/{id}', CoordinadorController::class . '@delete');
$router->add('POST', '/coordinadores/data', CoordinadorController::class . '@getCoordinadoresData');

// ==========================================
// 5. MÓDULOS DE CURSOS
// ==========================================
$router->add('GET', '/cursos', CursoController::class . '@index');
$router->add('GET', '/cursos/create', CursoController::class . '@create');
$router->add('POST', '/cursos/create', CursoController::class . '@create');
$router->add('GET', '/cursos/edit/{id}', CursoController::class . '@edit');
$router->add('POST', '/cursos/edit/{id}', CursoController::class . '@edit');
$router->add('GET', '/cursos/delete/{id}', CursoController::class . '@delete');
$router->add('POST', '/cursos/data', CursoController::class . '@getCursosData');

$router->add('GET', '/cursos_abiertos', CursoAbiertoController::class . '@index');
$router->add('GET', '/cursos_abiertos/create', CursoAbiertoController::class . '@create');
$router->add('POST', '/cursos_abiertos/create', CursoAbiertoController::class . '@create');
$router->add('GET', '/cursos_abiertos/edit/{id}', CursoAbiertoController::class . '@edit');
$router->add('POST', '/cursos_abiertos/edit/{id}', CursoAbiertoController::class . '@edit');
$router->add('GET', '/cursos_abiertos/delete/{id}', CursoAbiertoController::class . '@delete');
$router->add('POST', '/cursos_abiertos/data', CursoAbiertoController::class . '@getCursoAbiertoData');

$router->add('GET', '/inscripcion_curso', InscripcionCursoController::class . '@index');
$router->add('GET', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('POST', '/inscripcion_curso/create', InscripcionCursoController::class . '@create');
$router->add('GET', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('POST', '/inscripcion_curso/edit/{id}', InscripcionCursoController::class . '@edit');
$router->add('GET', '/inscripcion_curso/delete/{id}', InscripcionCursoController::class . '@delete');
$router->add('POST', '/inscripcion_curso/data', InscripcionCursoController::class . '@getInscripcionCursoData');

// ==========================================
// 6. MÓDULOS DE DIPLOMADOS Y PREINSCRIPCIÓN INTERNA
// ==========================================
$router->add('GET', '/diplomado', DiplomadoController::class . '@index');
$router->add('GET', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('POST', '/diplomado/create', DiplomadoController::class . '@create');
$router->add('GET', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('POST', '/diplomado/edit/{id}', DiplomadoController::class . '@edit');
$router->add('GET', '/diplomado/delete/{id}', DiplomadoController::class . '@delete');
$router->add('POST', '/diplomado/data', DiplomadoController::class . '@getDiplomadoData');

$router->add('GET', '/capitulo', CapituloController::class . '@index');
$router->add('GET', '/capitulo/create', CapituloController::class . '@create');
$router->add('POST', '/capitulo/create', CapituloController::class . '@create');
$router->add('GET', '/capitulo/edit/{id}', CapituloController::class . '@edit');
$router->add('POST', '/capitulo/edit/{id}', CapituloController::class . '@edit');
$router->add('GET', '/capitulo/delete/{id}', CapituloController::class . '@delete');
$router->add('POST', '/capitulo/data', CapituloController::class . '@getCapituloData');

$router->add('GET', '/diplomado_abierto', DiplomadoAbiertoController::class . '@index');
$router->add('GET', '/diplomado_abierto/create', DiplomadoAbiertoController::class . '@create');
$router->add('POST', '/diplomado_abierto/create', DiplomadoAbiertoController::class . '@create');
$router->add('GET', '/diplomado_abierto/edit/{id}', DiplomadoAbiertoController::class . '@edit');
$router->add('POST', '/diplomado_abierto/edit/{id}', DiplomadoAbiertoController::class . '@edit');
$router->add('GET', '/diplomado_abierto/delete/{id}', DiplomadoAbiertoController::class . '@delete');
$router->add('POST', '/diplomado_abierto/data', DiplomadoAbiertoController::class . '@getDiplomadoAbiertoData');

// Diplomado Control
$router->add('GET', '/diplomadocontrol', DiplomadoControlController::class . '@index');
$router->add('GET', '/diplomadocontrol/create', DiplomadoControlController::class . '@create');
$router->add('POST', '/diplomadocontrol/create', DiplomadoControlController::class . '@create');
$router->add('GET', '/diplomadocontrol/edit/{id}', DiplomadoControlController::class . '@edit');
$router->add('POST', '/diplomadocontrol/edit/{id}', DiplomadoControlController::class . '@edit');
$router->add('GET', '/diplomadocontrol/getCapitulosAjax', DiplomadoControlController::class . '@getCapitulosAjax');
$router->add('POST', '/diplomadocontrol/data', DiplomadoControlController::class . '@getDiplomadosData');

$router->add('GET', '/inscripcion_diplomado', InscripcionDiplomadoController::class . '@index');
$router->add('GET', '/inscripcion_diplomado/create', InscripcionDiplomadoController::class . '@create');
$router->add('POST', '/inscripcion_diplomado/create', InscripcionDiplomadoController::class . '@create');
$router->add('GET', '/inscripcion_diplomado/edit/{id}', InscripcionDiplomadoController::class . '@edit');
$router->add('POST', '/inscripcion_diplomado/edit/{id}', InscripcionDiplomadoController::class . '@edit');
$router->add('GET', '/inscripcion_diplomado/delete/{id}', InscripcionDiplomadoController::class . '@delete');
$router->add('POST', '/inscripcion_diplomado/data', InscripcionDiplomadoController::class . '@getInscripcionDiplomadoData');

$router->add('GET', '/preinscripcion_diplomado', PreinscripcionDiplomadoController::class . '@index');
$router->add('GET', '/preinscripcion_diplomado/create', PreinscripcionDiplomadoController::class . '@create');
$router->add('POST', '/preinscripcion_diplomado/create', PreinscripcionDiplomadoController::class . '@create');
$router->add('POST', '/preinscripcion_diplomado/search_alumno', PreinscripcionDiplomadoController::class . '@searchAlumno');
$router->add('POST', '/preinscripcion_diplomado/create_alumno', PreinscripcionDiplomadoController::class . '@createAlumno');
$router->add('POST', '/preinscripcion_diplomado/get_diplomados_abiertos', PreinscripcionDiplomadoController::class . '@getDiplomadosAbiertosForPreinscripcion');

// ==========================================
// 7. MÓDULOS DE EVENTOS
// ==========================================
$router->add('GET', '/evento', EventoController::class . '@index');
$router->add('GET', '/evento/create', EventoController::class . '@create');
$router->add('POST', '/evento/create', EventoController::class . '@create');
$router->add('GET', '/evento/edit/{id}', EventoController::class . '@edit');
$router->add('POST', '/evento/edit/{id}', EventoController::class . '@edit');
$router->add('GET', '/evento/delete/{id}', EventoController::class . '@delete');
$router->add('POST', '/evento/list', EventoController::class . '@getEventoData');

$router->add('GET', '/evento_abierto', EventoAbiertoController::class . '@index');
$router->add('GET', '/evento_abierto/create', EventoAbiertoController::class . '@create');
$router->add('POST', '/evento_abierto/create', EventoAbiertoController::class . '@create');
$router->add('GET', '/evento_abierto/edit/{id}', EventoAbiertoController::class . '@edit');
$router->add('POST', '/evento_abierto/edit/{id}', EventoAbiertoController::class . '@edit');
$router->add('GET', '/evento_abierto/delete/{id}', EventoAbiertoController::class . '@delete');
$router->add('POST', '/evento_abierto/data', EventoAbiertoController::class . '@getEventoAbiertoData');

$router->add('GET', '/inscripcion_evento', InscripcionEventoController::class . '@index');
$router->add('GET', '/inscripcion_evento/create', InscripcionEventoController::class . '@create');
$router->add('POST', '/inscripcion_evento/create', InscripcionEventoController::class . '@create');
$router->add('GET', '/inscripcion_evento/edit/{id}', InscripcionEventoController::class . '@edit');
$router->add('POST', '/inscripcion_evento/edit/{id}', InscripcionEventoController::class . '@edit');
$router->add('GET', '/inscripcion_evento/delete/{id}', InscripcionEventoController::class . '@delete');
$router->add('POST', '/inscripcion_evento/data', InscripcionEventoController::class . '@getInscripcionEventoData');

// ==========================================
// 8. MÓDULOS DE MAESTRÍAS
// ==========================================
$router->add('GET', '/maestria', MaestriaController::class . '@index');
$router->add('GET', '/maestria/create', MaestriaController::class . '@create');
$router->add('POST', '/maestria/create', MaestriaController::class . '@create');
$router->add('GET', '/maestria/edit/{id}', MaestriaController::class . '@edit');
$router->add('POST', '/maestria/edit/{id}', MaestriaController::class . '@edit');
$router->add('GET', '/maestria/delete/{id}', MaestriaController::class . '@delete');
$router->add('POST', '/maestria/data', MaestriaController::class . '@getMaestriaData');

$router->add('GET', '/maestria_abierto', MaestriaAbiertoController::class . '@index');
$router->add('GET', '/maestria_abierto/create', MaestriaAbiertoController::class . '@create');
$router->add('POST', '/maestria_abierto/create', MaestriaAbiertoController::class . '@create');
$router->add('GET', '/maestria_abierto/edit/{id}', MaestriaAbiertoController::class . '@edit');
$router->add('POST', '/maestria_abierto/edit/{id}', MaestriaAbiertoController::class . '@edit');
$router->add('GET', '/maestria_abierto/delete/{id}', MaestriaAbiertoController::class . '@delete');
$router->add('POST', '/maestria_abierto/data', MaestriaAbiertoController::class . '@getMaestriaAbiertoData');

$router->add('GET', '/inscripcion_maestria', InscripcionMaestriaController::class . '@index');
$router->add('GET', '/inscripcion_maestria/create', InscripcionMaestriaController::class . '@create');
$router->add('POST', '/inscripcion_maestria/create', InscripcionMaestriaController::class . '@create');
$router->add('GET', '/inscripcion_maestria/edit/{id}', InscripcionMaestriaController::class . '@edit');
$router->add('POST', '/inscripcion_maestria/edit/{id}', InscripcionMaestriaController::class . '@edit');
$router->add('GET', '/inscripcion_maestria/delete/{id}', InscripcionMaestriaController::class . '@delete');
$router->add('POST', '/inscripcion_maestria/data', InscripcionMaestriaController::class . '@getInscripcionMaestriaData');

// ==========================================
// 9. PAGOS, COMPENSACIONES Y CRONOGRAMA
// ==========================================
$router->add('GET', '/cuota', CuotaController::class . '@index');
$router->add('GET', '/cuota/create', CuotaController::class . '@create');
$router->add('POST', '/cuota/create', CuotaController::class . '@create');
$router->add('GET', '/cuota/edit/{id}', CuotaController::class . '@edit');
$router->add('POST', '/cuota/edit/{id}', CuotaController::class . '@edit');
$router->add('POST', '/cuota/generateDebt', CuotaController::class . '@generateDebt');
$router->add('GET', '/cuota/getCuotasByOfferData', CuotaController::class . '@getCuotasByOfferData');
$router->add('GET', '/cuota/getAcademicOffersByType', CuotaController::class . '@getAcademicOffersByType');
$router->add('GET', '/cuota/getStudentsForDebtGeneration', CuotaController::class . '@getStudentsForDebtGeneration');
$router->add('GET', '/cuota/getOfertaInfoAjax', CuotaController::class . '@getOfertaInfoAjax');
$router->add('GET', '/cuota/getDiplomadoControlesAjax', CuotaController::class . '@getDiplomadoControlesAjax');

$router->add('GET', '/pago', PagoController::class . '@index');
$router->add('GET', '/pago/create', PagoController::class . '@create');
$router->add('POST', '/pago/create', PagoController::class . '@create');
$router->add('GET', '/pago/edit/{id}', PagoController::class . '@edit');
$router->add('POST', '/pago/edit/{id}', PagoController::class . '@edit');
$router->add('GET', '/pago/delete/{id}', PagoController::class . '@delete');
$router->add('POST', '/pago/getPagosData', PagoController::class . '@getPagosData');
$router->add('GET', '/pago/getAlumnosAjax', PagoController::class . '@getAlumnosAjax');
$router->add('GET', '/pago/getCuotasByAlumnoAjax', PagoController::class . '@getCuotasByAlumnoAjax');
$router->add('POST', '/pago/confirm/{id}', PagoController::class . '@confirm');
$router->add('POST', '/pago/softDelete/{id}', PagoController::class . '@softDelete');
$router->add('GET', '/cronograma', CronogramaController::class . '@index');

$router->add('GET', '/asistencia', AsistenciaController::class . '@index');
$router->add('GET', '/asistencia/getAcademicOffersByType', AsistenciaController::class . '@getAcademicOffersByType');
$router->add('GET', '/asistencia/getOfertaInfoAjax', AsistenciaController::class . '@getOfertaInfoAjax');
$router->add('GET', '/asistencia/initAsistencia', AsistenciaController::class . '@initAsistencia');
$router->add('GET', '/asistencia/getAlumnosAjax', AsistenciaController::class . '@getAlumnosAjax');
$router->add('POST', '/asistencia/save', AsistenciaController::class . '@saveAsistencia');
$router->add('POST', '/asistencia/getData', AsistenciaController::class . '@getData');

// ==========================================
// 10. MANTENIMIENTOS Y PARAMÉTRICAS (GEOGRAFÍA/SEDES/BANCOS)
// ==========================================
$router->add('GET', '/sede', SedeController::class . '@index');
$router->add('GET', '/sede/create', SedeController::class . '@create');
$router->add('POST', '/sede/store', SedeController::class . '@store');
$router->add('GET', '/sede/edit/{id}', SedeController::class . '@edit');
$router->add('POST', '/sede/update/{id}', SedeController::class . '@update');
$router->add('GET', '/sede/delete/{id}', SedeController::class . '@delete');
$router->add('POST', '/sede/getSedesData', SedeController::class . '@getSedesData');

$router->add('GET', '/ciudad', CiudadController::class . '@index');
$router->add('GET', '/ciudad/create', CiudadController::class . '@create');
$router->add('POST', '/ciudad/store', CiudadController::class . '@store');
$router->add('GET', '/ciudad/edit/{id}', CiudadController::class . '@edit');
$router->add('POST', '/ciudad/update/{id}', CiudadController::class . '@update');
$router->add('GET', '/ciudad/delete/{id}', CiudadController::class . '@delete');
$router->add('POST', '/ciudad/getData', CiudadController::class . '@getData');

$router->add('GET', '/banco', BancoController::class . '@index');
$router->add('GET', '/banco/create', BancoController::class . '@create');
$router->add('POST', '/banco/store', BancoController::class . '@store');
$router->add('GET', '/banco/edit/{id}', BancoController::class . '@edit');
$router->add('POST', '/banco/update/{id}', BancoController::class . '@update');
$router->add('GET', '/banco/delete/{id}', BancoController::class . '@delete');
$router->add('POST', '/banco/getBancosData', BancoController::class . '@getBancosData');

$router->add('GET', '/duracion', DuracionController::class . '@index');
$router->add('GET', '/duracion/create', DuracionController::class . '@create');
$router->add('POST', '/duracion/store', DuracionController::class . '@store');
$router->add('GET', '/duracion/edit/{id}', DuracionController::class . '@edit');
$router->add('POST', '/duracion/update/{id}', DuracionController::class . '@update');
$router->add('GET', '/duracion/delete/{id}', DuracionController::class . '@delete');
$router->add('POST', '/duracion/getDuracionesData', DuracionController::class . '@getDuracionesData');

$router->add('GET', '/profesion_oficio', ProfesionOficioController::class . '@index');
$router->add('GET', '/profesion_oficio/create', ProfesionOficioController::class . '@create');
$router->add('POST', '/profesion_oficio/store', ProfesionOficioController::class . '@store');
$router->add('GET', '/profesion_oficio/edit/{id}', ProfesionOficioController::class . '@edit');
$router->add('POST', '/profesion_oficio/update/{id}', ProfesionOficioController::class . '@update');
$router->add('GET', '/profesion_oficio/delete/{id}', ProfesionOficioController::class . '@delete');
$router->add('POST', '/profesion_oficio/getProfesionesData', ProfesionOficioController::class . '@getProfesionesData');

// ==========================================
// 11. COMUNICACIONES Y MENSAJERÍA (CORREOS/ENVÍOS)
// ==========================================
$router->add('GET', '/mensajes', MensajesController::class . '@index');
$router->add('GET', '/mensajes/create', MensajesController::class . '@create');
$router->add('POST', '/mensajes/create', MensajesController::class . '@create');
$router->add('GET', '/mensajes/edit/{id}', MensajesController::class . '@edit');
$router->add('POST', '/mensajes/edit/{id}', MensajesController::class . '@edit');
$router->add('GET', '/mensajes/delete/{id}', MensajesController::class . '@delete');
$router->add('POST', '/mensajes/data', MensajesController::class . '@getMensajesData');

$router->add('GET', '/listaenvio', EnviosController::class . '@index'); 
$router->add('GET', '/listaenvio/create', EnviosController::class . '@create');
$router->add('POST', '/listaenvio/create', EnviosController::class . '@create');
$router->add('GET', '/listaenvio/edit/{id}', EnviosController::class . '@edit');
$router->add('POST', '/listaenvio/edit/{id}', EnviosController::class . '@edit');
$router->add('GET', '/listaenvio/delete/{id}', EnviosController::class . '@delete');
$router->add('POST', '/envios/data', EnviosController::class . '@getEnviosData');

$router->add('GET', '/listacorreo', CorreoController::class . '@index');
$router->add('GET', '/correo/create', CorreoController::class . '@create');
$router->add('POST', '/correo/create', CorreoController::class . '@create');
$router->add('POST', '/correo/sendChecked', CorreoController::class . '@sendChecked');
$router->add('GET', '/correo/getCorreosByOfferData', CorreoController::class . '@getCorreosByOfferData');
$router->add('GET', '/correo/getAcademicOffersByType', CorreoController::class . '@getAcademicOffersByType');
$router->add('GET', '/correo/getStudentsForDebtGeneration', CorreoController::class . '@getStudentsForDebtGeneration');
$router->add('GET', '/correo/getMensajes', CorreoController::class . '@getMensajes');

// ==========================================
// 12. LANDING PAGE PÚBLICA (PREINSCRIPCIÓN WEB)
// ==========================================
$router->add('GET', '/preinscripcionlanding', PreinscripcionLandingController::class . '@index');
$router->add('POST', '/preinscripcionlanding/search_alumno', PreinscripcionLandingController::class . '@searchAlumno');
$router->add('POST', '/preinscripcionlanding/create_alumno', PreinscripcionLandingController::class . '@createAlumno');
$router->add('POST', '/preinscripcionlanding/get_ofertas_abiertas', PreinscripcionLandingController::class . '@getOfertasAbiertas');
$router->add('POST', '/preinscripcionlanding/process_preinscripcion', PreinscripcionLandingController::class . '@processPreinscripcion');

// ==========================================
// DESPACHO DE SOLICITUDES
// ==========================================
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/php_mvc_app/public';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

try {
    $router->dispatch($request_uri, $_SERVER['REQUEST_METHOD']);
} catch (\Throwable $e) {
    // Registrar el error en el log de errores de PHP
    error_log("Excepción no capturada: " . $e->getMessage() . " en " . $e->getFile() . " en la línea " . $e->getLine());

    // Asegurarse de enviar un código de estado 500
    if (!headers_sent()) {
        http_response_code(500);
    }

    // Si es una petición AJAX (como DataTables o fetch)
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Ha ocurrido un error interno en el servidor.',
            // Opcional para DataTables:
            'draw' => isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ]);
        exit;
    }

    // Si es una petición normal del navegador, mostramos una página amigable
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de Sistema</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; color: #111827; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .error-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); max-width: 500px; text-align: center; }
            .error-card h1 { color: #dc2626; margin-top: 0; font-size: 2rem; }
            .error-card p { color: #4b5563; font-size: 1.1rem; margin-bottom: 30px; }
            .btn { background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.2s; }
            .btn:hover { background-color: #1d4ed8; }
            .icon { font-size: 4rem; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="icon">⚠️</div>
            <h1>Error Interno</h1>
            <p>Lo sentimos, ha ocurrido un problema técnico en el servidor. Por favor, inténtelo de nuevo más tarde o contacte al administrador.</p>
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn">Volver al inicio</a>
        </div>
    </body>
    </html>
    <?php
}