<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">Pre-inscripción - Sistema de Registro</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .radio-inner { transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .workshop-card { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .modal-scale { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    </style>
    <script type="text/javascript">
        const BASE_URL_JS = "<?php echo BASE_URL; ?>";
    </script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">

        <!-- Encabezado -->
        <header class="border-b border-slate-100 p-6 md:p-8 bg-gradient-to-r from-slate-50 to-white">
            <div class="flex justify-between items-start">
                <div>
                    <h1 id="oferta" class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Pre-inscribir</h1>
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 bg-indigo-600 rounded-full animate-pulse"></span>
                        Periodo <?php echo date('Y'); ?> — Sistema de Registro Integrado
                    </p>
                </div>
                <div id="step-badge" class="hidden sm:block bg-indigo-50 text-indigo-700 text-base font-bold px-6 py-3 rounded-full">
                    Paso 1 de 2
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6 space-y-4">

            <!-- Estado vacío: sin oferta seleccionada -->
            <div id="empty-state" class="text-center py-8 md:py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="graduation-cap" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">Selecciona una oferta académica</h2>
                <p class="text-sm text-slate-400 mb-6 max-w-xs mx-auto">Elige entre los programas disponibles para comenzar tu pre-inscripción.</p>
            </div>

            <!-- PASO 1: OFERTAS -->
            <section id="step1-section" class="space-y-4 hidden">
                <div class="flex justify-between items-center">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <span class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-600 text-2xl font-bold">1</span>
                        Selecciona un <span id="tipo-label">Taller</span> disponible
                    </h2>
                    <span class="text-xs text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded">Selección única</span>
                </div>

                <div id="ofertas-list" class="space-y-3">
                    <div class="text-center py-8 text-slate-400">
                        <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Cargando ofertas disponibles...</p>
                    </div>
                </div>
            </section>

            <!-- PASO 2: ALUMNO -->
            <section id="step2-section" class="space-y-4 hidden">
                <div class="flex justify-between items-center">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <span class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-600 text-2xl font-bold">2</span>
                        Información del Alumno
                    </h2>
                    <span id="alumno-verified-badge" class="flex flex-col items-center text-emerald-600 font-medium bg-emerald-50 px-3 py-1.5 rounded-lg hidden">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span class="text-[10px] leading-tight">Verificado</span>
                    </span>
                </div>

                <div id="student-card" class="bg-emerald-50/40 border border-emerald-100 rounded-2xl p-5 md:p-6 transition-all duration-300 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 font-medium">Nombre Completo</span>
                            <div class="flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4 text-emerald-600"></i>
                                <p id="student-name" class="font-semibold text-slate-800 text-base">—</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 font-medium">Documento / Pasaporte</span>
                            <div class="flex items-center gap-2">
                                <i data-lucide="credit-card" class="w-4 h-4 text-emerald-600"></i>
                                <p id="student-ci" class="font-medium text-slate-700 text-sm">—</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 font-medium">Correo Electrónico</span>
                            <div class="flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4 text-emerald-600"></i>
                                <p id="student-email" class="font-medium text-slate-700 text-sm break-all">—</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 font-medium">Celular de Contacto</span>
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="w-4 h-4 text-emerald-600"></i>
                                <p id="student-phone" class="font-medium text-slate-500 text-sm">No registrado</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-emerald-100/60 flex justify-end">
                        <button onclick="toggleChangeStudentModal(true)" class="flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 hover:border-indigo-100 shadow-sm">
                            <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                            Cambiar de Alumno
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-100 p-6 md:p-8 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-xs text-slate-400 max-w-xs leading-normal">
                Al finalizar el proceso, te llegará un correo electrónico con los detalles y método de validación.
            </div>
            <div>
                <button id="btn-submit" onclick="submitPreinscripcion()" disabled class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-slate-300 text-slate-500 font-bold text-sm rounded-xl cursor-not-allowed transition-all duration-300">
                    <span>Finalizar Pre-inscripción</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </footer>
    </div>

    <!-- MODAL: Buscar Alumno -->
    <div id="modal-search-student" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 md:p-8 shadow-2xl border border-slate-100 modal-scale scale-95">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Buscar Alumno</h3>
                    <p class="text-xs text-slate-400 mt-1">Ingresa el CI / Pasaporte para comenzar</p>
                </div>
                <button onclick="toggleChangeStudentModal(false)" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="form-search-student" class="space-y-4" onsubmit="return searchStudent(event)">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">CI / Pasaporte</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-slate-400 pointer-events-none"></i>
                        <input type="text" id="search-ci-input" placeholder="Ej: 30429336" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all" required>
                    </div>
                    <p id="search-error" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Buscar alumno
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: Crear Alumno -->
    <div id="modal-create-student" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-3xl w-full max-w-lg p-6 md:p-8 shadow-2xl border border-slate-100 modal-scale scale-95">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Registrar Nuevo Alumno</h3>
                    <p class="text-xs text-slate-400 mt-1">Completa los datos del alumno para continuar</p>
                </div>
                <button onclick="toggleCreateStudentModal(false)" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="form-create-student" class="space-y-4" onsubmit="return createStudent(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">CI / Pasaporte *</label>
                        <input type="text" id="new-ci" name="new_ci_pasapote" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Primer Nombre *</label>
                        <input type="text" id="new-primer-nombre" name="new_primer_nombre" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Segundo Nombre</label>
                        <input type="text" id="new-segundo-nombre" name="new_segundo_nombre" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Primer Apellido *</label>
                        <input type="text" id="new-primer-apellido" name="new_primer_apellido" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Segundo Apellido</label>
                        <input type="text" id="new-segundo-apellido" name="new_segundo_apellido" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                        <input type="email" id="new-correo" name="new_correo" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Teléfono Celular</label>
                        <input type="text" id="new-tlf-celular" name="new_tlf_celular" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Teléfono Local</label>
                        <input type="text" id="new-tlf-habitacion" name="new_tlf_habitacion" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="toggleCreateStudentModal(false)" class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="py-3 px-6 bg-emerald-600 text-white font-semibold text-sm rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-100 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Guardar y Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Pre-inscripción Exitosa -->
    <div id="modal-success" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 md:p-8 shadow-2xl border border-slate-100 text-center modal-scale scale-95">
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                <i data-lucide="check" class="w-8 h-8 text-emerald-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-1">¡Pre-inscripción Exitosa!</h3>
            <p class="text-sm text-slate-500 mb-6">Hemos registrado tu solicitud.</p>

            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 text-left space-y-3.5 mb-6 text-xs relative overflow-hidden">
                <div class="absolute right-3 top-3">
                    <span class="bg-emerald-100 text-emerald-800 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded">VÁLIDO</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Programa Registrado</span>
                    <span id="ticket-program" class="font-bold text-slate-800 text-sm">—</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-dashed border-slate-200">
                    <div>
                        <span class="text-slate-400 block font-medium">Pre-Inscrito</span>
                        <span id="ticket-student" class="font-semibold text-slate-700">—</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">ID de Registro</span>
                        <span id="ticket-id" class="font-semibold text-indigo-600">—</span>
                    </div>
                </div>
            </div>

            <button onclick="resetFlow()" class="w-full py-3 bg-slate-900 text-white font-semibold text-sm rounded-xl hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                Aceptar y cerrar
            </button>
        </div>
    </div>

    <!-- MODAL: Confirmar Pre-inscripción -->
    <div id="modal-confirm" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 md:p-8 shadow-2xl border border-slate-100 modal-scale scale-95">
            <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-100">
                <i data-lucide="alert-triangle" class="w-7 h-7 text-amber-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2 text-center">Confirmar Pre-inscripción</h3>
            <p id="confirm-message" class="text-sm text-slate-500 text-center mb-6">¿Estás seguro de pre-inscribir al alumno seleccionado?</p>
            <div class="flex items-center gap-3">
                <button onclick="closeModal('modal-confirm')" class="flex-1 py-3 bg-slate-100 text-slate-700 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                    Cancelar
                </button>
                <button id="btn-confirm-submit" onclick="executePreinscripcion()" class="flex-1 py-3 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 opacity-0 translate-y-2 pointer-events-none transition-all duration-300">
        <div class="flex items-center gap-2.5 px-5 py-3 rounded-2xl shadow-xl text-white text-sm font-medium">
            <i id="toast-icon" data-lucide="info" class="w-4 h-4"></i>
            <span id="toast-message">Mensaje</span>
        </div>
    </div>

    <script src="asset/js/PreinscripcionLanding/preinscripcion_landing.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
