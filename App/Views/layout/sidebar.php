<?php
// php_mvc_app/app/Views/layout/sidebar.php
use App\Core\Auth;

// Obtener la URI relativa para manejar estados activos
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH);
$relativeUri = str_replace($baseUrlPath, '', $currentUri);
$relativeUri = trim($relativeUri, '/');

// Lógica para determinar qué secciones deben estar abiertas inicialmente
$uriSegments = explode('/', $relativeUri);
$module = $uriSegments[0] ?? '';

// Definición de visibilidad basada en permisos (Keywords exactos de la BD)
$canSeeAlumnos = Auth::hasPermission('alumnos');
$canSeeDocentes = Auth::hasPermission('docentes');
$canSeeCoordinadores = Auth::hasPermission('coordinadores');

$canSeeCursos = Auth::hasPermission('cursos');
$canSeeCursosAbiertos = Auth::hasPermission('cursos_abiertos');
$canSeeInscripcionCurso = Auth::hasPermission('inscripcion_curs');

$canSeeEvento = Auth::hasPermission('evento');
$canSeeEventoAbierto = Auth::hasPermission('evento_abierto');
$canSeeInscripcionEvento = Auth::hasPermission('inscripcion_even');

$canSeeDiplomado = Auth::hasPermission('diplomado');
$canSeeCapitulo = Auth::hasPermission('capitulo');
$canSeeDiplomadoAbierto = Auth::hasPermission('diplomado_abiert');
$canSeeInscripcionDiplomado = Auth::hasPermission('inscripcion_dipl');
$canSeePreinscripcionDiplomado = Auth::hasPermission('preinscripcion_d');

$canSeeMaestria = Auth::hasPermission('maestria');
$canSeeMaestriaAbierto = Auth::hasPermission('maestria_abierto');
$canSeeInscripcionMaestria = Auth::hasPermission('inscripcion_maes');

$canSeeCuota = Auth::hasPermission('cuota');
$canSeePago = Auth::hasPermission('pago');
$canSeeCompensar = Auth::hasPermission('compensar');
$canSeeCronograma = Auth::hasPermission('cronograma');

$canSeeListaCorreo = Auth::hasPermission('listacorreo');
$canSeeMensajes = Auth::hasPermission('mensajes');
$canSeeListaEnvio = Auth::hasPermission('listaenvio');

$canSeeSede = Auth::hasPermission('sede');
$canSeeBanco = Auth::hasPermission('banco');
$canSeeDuracion = Auth::hasPermission('duracion');
$canSeeProfesion = Auth::hasPermission('profesion_oficio');

$canSeeCiudad = Auth::hasPermission('estado');

$canSeeUsers = Auth::hasPermission('users');
$canSeeGrupo = Auth::hasPermission('grupo');

// Visibilidad de secciones
$showRegistro = $canSeeAlumnos || $canSeeDocentes || $canSeeCoordinadores;
$showTalleres = $canSeeCursos || $canSeeCursosAbiertos || $canSeeInscripcionCurso;
$showEventos = $canSeeEvento || $canSeeEventoAbierto || $canSeeInscripcionEvento;
$showDiplomados = $canSeeDiplomado || $canSeeCapitulo || $canSeeDiplomadoAbierto || $canSeeInscripcionDiplomado || $canSeePreinscripcionDiplomado;
$showMaestrias = $canSeeMaestria || $canSeeMaestriaAbierto || $canSeeInscripcionMaestria;
$showPagos = $canSeeCuota || $canSeePago || $canSeeCompensar || $canSeeCronograma;
$showMensajes = $canSeeListaCorreo || $canSeeMensajes || $canSeeListaEnvio;
$showMantenimiento = $canSeeSede || $canSeeBanco || $canSeeDuracion || $canSeeProfesion || $canSeeCiudad;
$showSeguridad = $canSeeUsers || $canSeeGrupo;

// Estados activos
$isRegistroActive = in_array($module, ['alumnos', 'docentes', 'coordinadores']);
$isTalleresActive = in_array($module, ['cursos', 'cursos_abiertos', 'inscripcion_curso']);
$isEventosActive = in_array($module, ['evento', 'evento_abierto', 'inscripcion_evento']);
$isDiplomadosActive = in_array($module, ['diplomado', 'capitulo', 'diplomado_abierto', 'inscripcion_diplomado', 'preinscripcion_diplomado']);
$isMaestriasActive = in_array($module, ['maestria', 'maestria_abierto', 'inscripcion_maestria']);
$isPagosActive = in_array($module, ['cuota', 'pago', 'compensar', 'cronograma']);
$isMensajesActive = in_array($module, ['listacorreo', 'correo', 'mensajes', 'listaenvio', 'envios']);
$isMantenimientoActive = in_array($module, ['sede', 'banco', 'duracion', 'profesion_oficio', 'ciudad']);
$isSeguridadActive = in_array($module, ['users', 'grupo']);
?>
<aside class="w-64 bg-gray-800 text-white h-auto min-h-screen max-h-screen overflow-y-auto p-4 flex flex-col justify-between rounded-r-lg shadow-lg">
    <div>
        <h1 class="mb-8 bg-sky-50 rounded-md p-2">
            <img src="<?php echo BASE_URL; ?>image/logo-grupo-avemer.webp" alt="Avemer Logo" class="w-full h-auto">
        </h1>
        <nav>
            <ul class="text-white space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>dashboard"
                        class="block py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo ($module == 'dashboard' || empty($relativeUri)) ? 'bg-gray-700 text-blue-300 font-semibold' : ''; ?>">
                        <i class="fa fa-home mr-2"></i> Dashboard
                    </a>
                </li>

                <!-- Registro -->
                <?php if ($showRegistro): ?>
                <li x-data="{ registroOpen: <?php echo $isRegistroActive ? 'true' : 'false'; ?> }">
                    <button @click="registroOpen = !registroOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isRegistroActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-users mr-2"></i> Registro</span>
                        <svg :class="{'rotate-180': registroOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="registroOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeAlumnos): ?>
                        <li><a href="<?php echo BASE_URL; ?>alumnos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'alumnos') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-regular fa-user mr-2"></i> Alumnos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeDocentes): ?>
                        <li><a href="<?php echo BASE_URL; ?>docentes" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'docentes') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-user mr-2"></i> Instructores</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCoordinadores): ?>
                        <li><a href="<?php echo BASE_URL; ?>coordinadores" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'coordinadores') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-user-gear mr-2"></i> Coordinadores</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Talleres -->
                <?php if ($showTalleres): ?>
                <li x-data="{ talleresOpen: <?php echo $isTalleresActive ? 'true' : 'false'; ?> }">
                    <button @click="talleresOpen = !talleresOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isTalleresActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-chalkboard-teacher mr-2"></i> Talleres / Cursos</span>
                        <svg :class="{'rotate-180': talleresOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="talleresOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeCursos): ?>
                        <li><a href="<?php echo BASE_URL; ?>cursos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'cursos') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-book mr-2"></i> Talleres / Cursos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCursosAbiertos): ?>
                        <li><a href="<?php echo BASE_URL; ?>cursos_abiertos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'cursos_abiertos') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeInscripcionCurso): ?>
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_curso" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'inscripcion_curso') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fas fa-user-check mr-2"></i> Inscripción</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Eventos -->
                <?php if ($showEventos): ?>
                <li x-data="{ eventosOpen: <?php echo $isEventosActive ? 'true' : 'false'; ?> }">
                    <button @click="eventosOpen = !eventosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isEventosActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-calendar-alt mr-2"></i> Eventos</span>
                        <svg :class="{'rotate-180': eventosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="eventosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeEvento): ?>
                        <li><a href="<?php echo BASE_URL; ?>evento" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'evento') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-star mr-2"></i> Eventos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeEventoAbierto): ?>
                        <li><a href="<?php echo BASE_URL; ?>evento_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'evento_abierto') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeInscripcionEvento): ?>
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_evento" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'inscripcion_evento') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fas fa-user-check mr-2"></i> Inscripción</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Diplomados -->
                <?php if ($showDiplomados): ?>
                <li x-data="{ diplomadosOpen: <?php echo $isDiplomadosActive ? 'true' : 'false'; ?> }">
                    <button @click="diplomadosOpen = !diplomadosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isDiplomadosActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-book mr-2"></i> Diplomados</span>
                        <svg :class="{'rotate-180': diplomadosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="diplomadosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeDiplomado): ?>
                        <li><a href="<?php echo BASE_URL; ?>diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'diplomado') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-graduation-cap mr-2"></i> Diplomados</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCapitulo): ?>
                        <li><a href="<?php echo BASE_URL; ?>capitulo" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'capitulo') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-list-alt mr-2"></i> Capítulos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeDiplomadoAbierto): ?>
                        <li><a href="<?php echo BASE_URL; ?>diplomado_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'diplomado_abierto') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeInscripcionDiplomado): ?>
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'inscripcion_diplomado') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fas fa-user-check mr-2"></i> Inscripción</a></li>
                        <?php endif; ?>
                        <?php if ($canSeePreinscripcionDiplomado): ?>
                        <li><a href="<?php echo BASE_URL; ?>preinscripcion_diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'preinscripcion_diplomado') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-pen-to-square mr-2"></i> Pre-Inscripción</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Maestrías -->
                <?php if ($showMaestrias): ?>
                <li x-data="{ maestriasOpen: <?php echo $isMaestriasActive ? 'true' : 'false'; ?> }">
                    <button @click="maestriasOpen = !maestriasOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isMaestriasActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-university mr-2"></i> Maestrías</span>
                        <svg :class="{'rotate-180': maestriasOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="maestriasOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeMaestria): ?>
                        <li><a href="<?php echo BASE_URL; ?>maestria" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'maestria') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-graduation-cap mr-2"></i> Maestrías</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeMaestriaAbierto): ?>
                        <li><a href="<?php echo BASE_URL; ?>maestria_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'maestria_abierto') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeInscripcionMaestria): ?>
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_maestria" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'inscripcion_maestria') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fas fa-user-check mr-2"></i> Inscripción</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Pagos -->
                <?php if ($showPagos): ?>
                <li x-data="{ pagosOpen: <?php echo $isPagosActive ? 'true' : 'false'; ?> }">
                    <button @click="pagosOpen = !pagosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isPagosActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-dollar-sign mr-2"></i> Pagos</span>
                        <svg :class="{'rotate-180': pagosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="pagosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeCuota): ?>
                        <li><a href="<?php echo BASE_URL; ?>cuota" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'cuota') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-file-invoice-dollar mr-2"></i> Cuotas</a></li>
                        <?php endif; ?>
                        <?php if ($canSeePago): ?>
                        <li><a href="<?php echo BASE_URL; ?>pago" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'pago') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-cash-register mr-2"></i> Pagos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCompensar): ?>
                        <li><a href="<?php echo BASE_URL; ?>compensar" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'compensar') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-exchange-alt mr-2"></i> Compensar</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCronograma): ?>
                        <li><a href="<?php echo BASE_URL; ?>cronograma" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'cronograma') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-calendar-alt mr-2"></i> Cronograma</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Mensajes -->
                <?php if ($showMensajes): ?>
                <li x-data="{ mensajesOpen: <?php echo $isMensajesActive ? 'true' : 'false'; ?> }">
                    <button @click="mensajesOpen = !mensajesOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isMensajesActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa fa-envelope mr-2"></i> Mensajes</span>
                        <svg :class="{'rotate-180': mensajesOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="mensajesOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeListaCorreo): ?>
                        <li><a href="<?php echo BASE_URL; ?>listacorreo" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'listacorreo' || $module == 'correo') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-users-rectangle mr-2"></i> Listas Correo</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeMensajes): ?>
                        <li><a href="<?php echo BASE_URL; ?>mensajes" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'mensajes') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-paper-plane mr-2"></i> Mensajes</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeListaEnvio): ?>
                        <li><a href="<?php echo BASE_URL; ?>listaenvio" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'listaenvio' || $module == 'envios') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-list mr-2"></i> Listas Envío</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Mantenimiento -->
                <?php if ($showMantenimiento): ?>
                <li x-data="{ mantenimientoOpen: <?php echo $isMantenimientoActive ? 'true' : 'false'; ?> }">
                    <button @click="mantenimientoOpen = !mantenimientoOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isMantenimientoActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa-solid fa-gears mr-2"></i> Mantenimiento</span>
                        <svg :class="{'rotate-180': mantenimientoOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="mantenimientoOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeSede): ?>
                        <li><a href="<?php echo BASE_URL; ?>sede" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'sede') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-school mr-2"></i> Sedes</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeBanco): ?>
                        <li><a href="<?php echo BASE_URL; ?>banco" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'banco') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-building-columns mr-2"></i> Bancos</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeDuracion): ?>
                        <li><a href="<?php echo BASE_URL; ?>duracion" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'duracion') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-regular fa-calendar mr-2"></i> Duraciones</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeProfesion): ?>
                        <li><a href="<?php echo BASE_URL; ?>profesion_oficio" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'profesion_oficio') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-person-digging mr-2"></i> Profesiones</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeCiudad): ?>
                        <li><a href="<?php echo BASE_URL; ?>ciudad" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'ciudad') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-city mr-2"></i> Ciudades / Estados</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Seguridad -->
                <?php if ($showSeguridad): ?>
                <li x-data="{ seguridadOpen: <?php echo $isSeguridadActive ? 'true' : 'false'; ?> }">
                    <button @click="seguridadOpen = !seguridadOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200 <?php echo $isSeguridadActive ? 'text-blue-300 font-semibold' : ''; ?>">
                        <span><i class="fa-solid fa-shield-halved mr-2"></i> Seguridad</span>
                        <svg :class="{'rotate-180': seguridadOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="seguridadOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <?php if ($canSeeUsers): ?>
                        <li><a href="<?php echo BASE_URL; ?>users" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'users') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa fa-user-shield mr-2"></i> Usuarios</a></li>
                        <?php endif; ?>
                        <?php if ($canSeeGrupo): ?>
                        <li><a href="<?php echo BASE_URL; ?>grupo" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition <?php echo ($module == 'grupo') ? 'bg-gray-600 text-white font-bold' : ''; ?>"><i class="fa-solid fa-users-gear mr-2"></i> Grupo y Permisos</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Perfil de usuario y Logout -->
    <?php
    // User profile photo (fallback to default avatar)
    $profileImg = Auth::user('profile_image') ?? null;
    $avatarPath = $profileImg ? BASE_URL . 'uploads/avatars/' . $profileImg : BASE_URL . 'image/default-avatar.png';
    ?>
    <div class="mt-8 pt-4 border-t border-gray-700">
        <div class="flex flex-col items-center mb-4">
            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center flex-shrink-0">
                <img src="<?php echo $avatarPath; ?>" alt="User Avatar" class="w-full h-full object-cover" />
            </div>
            
            <div class="mt-2 text-center w-full px-2">
                <p class="text-sm font-medium text-white truncate"><?php echo Auth::user('user_name'); ?></p>
                <p class="text-xs text-gray-400 truncate"><?php echo Auth::user('nombre_grupo'); ?></p>
            </div>
        </div>
        
        <a href="<?php echo BASE_URL; ?>logout"
            class="block w-full py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-center text-white text-sm transition duration-200">
            <i class="fa fa-sign-out-alt mr-2"></i> Cerrar Sesión
        </a>
    </div>
</aside>
<main class="flex-1 p-8 overflow-y-auto">
    <div class="container mx-auto">