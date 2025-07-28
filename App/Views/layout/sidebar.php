<?php
// php_mvc_app/app/Views/layout/sidebar.php
use App\Core\Auth;
?>
<aside class="w-64 bg-gray-800 text-white h-auto min-h-screen max-h-screen overflow-y-auto p-4 flex flex-col justify-between rounded-r-lg shadow-lg">
    <div>
        <h1 class="mb-8 bg-sky-50 rounded-md">
            <img src="<?php echo BASE_URL; ?>../assets/images/logo.png" alt="">
        </h1>
        <nav>
            <ul class="text-white space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>dashboard"
                        class="block py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <i class="fa fa-home mr-2"></i> Dashboard
                    </a>
                </li>

                <li x-data="{ registroOpen: false }">
                    <button @click="registroOpen = !registroOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-users mr-2"></i> Registro</span>
                        <svg :class="{'rotate-180': registroOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="registroOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>alumnos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa-regular fa-user mr-2"></i> Alumnos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>docentes" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-user mr-2"></i> Instructores</a></li>
                        <li><a href="<?php echo BASE_URL; ?>coordinadores" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-user-tie mr-2"></i> Coordinadores</a></li>
                    </ul>
                </li>

                <li x-data="{ talleresOpen: false }">
                    <button @click="talleresOpen = !talleresOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-graduation-cap mr-2"></i> Talleres</span>
                        <svg :class="{'rotate-180': talleresOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="talleresOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>cursos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-graduation-cap mr-2"></i> Talleres</a></li>
                        <li><a href="<?php echo BASE_URL; ?>cursos_abiertos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <!-- <li><a href="<?php echo BASE_URL; ?>curso_control" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Detalle</a></li> -->
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_curso" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa-solid fa-pen-to-square mr-2"></i> Inscripción</a></li>
                    </ul>
                </li>

                <li x-data="{ eventosOpen: false }">
                    <button @click="eventosOpen = !eventosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fas fa-calendar-day mr-2"></i> Eventos</span>
                        <svg :class="{'rotate-180': eventosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="eventosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>evento" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-calendar-day mr-2"></i> Eventos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>evento_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Apertura Evento</a></li>
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_evento" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa-solid fa-pen-to-square mr-2"></i> Inscripción Evento</a></li>
                    </ul>
                </li>

                <li x-data="{ diplomadosOpen: false }">
                    <button @click="diplomadosOpen = !diplomadosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-book mr-2"></i> Diplomados</span>
                        <svg :class="{'rotate-180': diplomadosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="diplomadosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-graduation-cap mr-2"></i> Diplomados</a></li>
                        <li><a href="<?php echo BASE_URL; ?>capitulo" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-list-alt mr-2"></i> Capítulos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>diplomado_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Apertura</a></li>
                        <!-- <li><a href="<?php echo BASE_URL; ?>diplomado_control" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Detalle</a></li> -->
                        <li><a href="<?php echo BASE_URL; ?>inscripcion_diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa-solid fa-pen-to-square mr-2"></i> Inscripción</a></li>
                        <li><a href="<?php echo BASE_URL; ?>preinscripcion" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-money mr-2"></i> Pre-Inscripción</a></li>
                        <li><a href="<?php echo BASE_URL; ?>nota" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-money mr-2"></i> Notas</a></li>
                        <li><a href="<?php echo BASE_URL; ?>asistencia_diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-money mr-2"></i> Asistencia Diplomado</a></li>
                    </ul>
                </li>

                <li x-data="{ maestriasOpen: false }">
                    <button @click="maestriasOpen = !maestriasOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-university mr-2"></i> Maestrías</span>
                        <svg :class="{'rotate-180': maestriasOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="maestriasOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>maestria_abierto" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Apertura Maestría</a></li>
                        <li><a href="<?php echo BASE_URL; ?>maestria_control" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Detalle Maestría</a></li>
                        <li><a href="<?php echo BASE_URL; ?>maestria_inscripcion" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Inscripción Maestría</a></li>
                    </ul>
                </li>

                <li x-data="{ pagosOpen: false }">
                    <button @click="pagosOpen = !pagosOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-credit-card mr-2"></i> Pagos</span>
                        <svg :class="{'rotate-180': pagosOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="pagosOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>cuota" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Cuota</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pago" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Pagos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>compensar" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Compensar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>cronograma" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Cronograma</a></li>
                    </ul>
                </li>

                <li x-data="{ mensajesOpen: false }">
                    <button @click="mensajesOpen = !mensajesOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-envelope mr-2"></i> Mensajes</span>
                        <svg :class="{'rotate-180': mensajesOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="mensajesOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>listacorreo" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-list-ul mr-2"></i> Lista De Correo</a></li>
                        <li><a href="<?php echo BASE_URL; ?>mensajehtml" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-envelope mr-2"></i> Mensaje</a></li>
                        <li><a href="<?php echo BASE_URL; ?>listaenvio" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-envelope mr-2"></i> Lista De Envio</a></li>
                    </ul>
                </li>

                <li x-data="{ reportesOpen: false }">
                    <button @click="reportesOpen = !reportesOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-list-alt mr-2"></i> Reportes</span>
                        <svg :class="{'rotate-180': reportesOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="reportesOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>rpt_detalle_diplomado" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-unlock mr-2"></i> Diplomados</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_global_pago" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Pagos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_pagos_por_confirmar" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Pagos por Confirmar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_pagos_pendientes" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Pagos pendientes</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_morosos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Morosos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_eventos" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Eventos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>rpt_talleres" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-exchange mr-2"></i> Talleres</a></li>
                    </ul>
                </li>

                <li x-data="{ mantenimientoOpen: false }">
                    <button @click="mantenimientoOpen = !mantenimientoOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-wrench mr-2"></i> Mantenimiento</span>
                        <svg :class="{'rotate-180': mantenimientoOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="mantenimientoOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>maestria" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-graduation-cap mr-2"></i> Maestrías</a></li>
                        <li><a href="<?php echo BASE_URL; ?>sede" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-building mr-2"></i> Sedes</a></li>
                        <li><a href="<?php echo BASE_URL; ?>banco" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-institution mr-2"></i> Bancos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>duracion" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-clock-o mr-2"></i> Duración</a></li>
                        <li><a href="<?php echo BASE_URL; ?>profesion" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-money mr-2"></i> Profesión u Oficio</a></li>
                    </ul>
                </li>

                <li x-data="{ seguridadOpen: false }">
                    <button @click="seguridadOpen = !seguridadOpen"
                        class="w-full flex justify-between items-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        <span><i class="fa fa-user-shield mr-2"></i> Seguridad</span>
                        <svg :class="{'rotate-180': seguridadOpen}" class="w-4 h-4 transform transition-transform duration-200"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <ul x-show="seguridadOpen" x-transition.opacity class="pl-4 mt-1 space-y-1 text-sm text-gray-300">
                        <li><a href="<?php echo BASE_URL; ?>users/group" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-user-group mr-2"></i> Grupo</a></li>
                        <li><a href="<?php echo BASE_URL; ?>users" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-user-circle mr-2"></i> Usuario</a></li>
                        <li><a href="<?php echo BASE_URL; ?>users/permissions" class="block py-1.5 px-3 rounded hover:bg-gray-600 transition"><i class="fa fa-money mr-2"></i> Permisos Grupo</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <div class="mt-auto">
        <div class="text-sm text-gray-400 mb-4 text-center">
            Bienvenido, <span class="font-semibold text-blue-300"><?php echo htmlspecialchars(Auth::user('user_name')); ?></span>
        </div>
        <a href="<?php echo BASE_URL; ?>logout" class="block py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-center rounded transition duration-200">
            Cerrar Sesión
        </a>
    </div>
</aside>
<main class="flex-1 p-8">
    <div class="container mx-auto">