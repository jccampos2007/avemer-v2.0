<?php
/**
 * preinscripcion_landing.php
 * Vista principal de preinscripción.
 * El manejo de la URL de la API y variables de entorno se ha movido al backend o al script JS.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-inscripción de Diplomados</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        #custom-confirm-overlay { background-color: rgba(0, 0, 0, 0.5); }
        #custom-confirm-dialog { transform: scale(0.95); opacity: 0; }
        #custom-confirm-overlay.opacity-100 #custom-confirm-dialog { transform: scale(1); opacity: 1; }
    </style>
</head>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />

<body class="bg-gray-100 p-4 font-sans antialiased">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <header class="mb-8 border-b pb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Pre-inscripción
            </h1>
            <p class="text-gray-600"><?php echo date('Y'); ?> - Sistema de Registro</p>
        </header>

        <!-- Sección 1: Búsqueda de Alumno -->
        <div id="searchAlumnoSection" class="mb-8 p-6 border rounded-lg bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Paso 1: Seleccionar o Crear Alumno</h2>
            
            <form id="searchAlumnoForm" class="mb-4">
                <label for="ci_pasapote_search" class="block text-gray-700 text-sm font-bold mb-2">Buscar Alumno por CI/Pasaporte:</label>
                <div class="flex">
                    <input type="text" id="ci_pasapote_search" name="ci_pasapote_search" class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Ej. V-12345678" required>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">Buscar</button>
                </div>
                <p id="search_result_message" class="mt-2 text-sm"></p>
            </form>

            <!-- Detalles del Alumno Encontrado -->
            <div id="alumnoDetails" class="hidden p-4 border border-green-300 bg-green-50 rounded-lg">
                <h3 class="text-lg font-bold text-green-800 mb-2">Alumno Seleccionado:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                    <p><strong>CI/Pasaporte:</strong> <span id="alumno_ci_pasapote"></span></p>
                    <p><strong>Nombre:</strong> <span id="alumno_nombre_completo"></span></p>
                    <p><strong>Correo:</strong> <span id="alumno_correo"></span></p>
                    <p><strong>Celular:</strong> <span id="alumno_celular"></span></p>
                </div>
                <button type="button" id="changeAlumnoBtn" class="mt-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded text-sm">Cambiar Alumno</button>
            </div>

            <!-- Formulario para Crear Nuevo Alumno -->
            <form id="createAlumnoForm" class="hidden p-4 border border-yellow-300 bg-yellow-50 rounded-lg">
                <h3 class="text-lg font-bold text-yellow-800 mb-2">Nuevo Registro:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-xs font-bold mb-1">CI/Pasaporte *</label>
                        <input type="text" id="new_ci_pasapote" name="new_ci_pasapote" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Primer Nombre *</label>
                        <input type="text" id="new_primer_nombre" name="new_primer_nombre" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Segundo Nombre</label>
                        <input type="text" id="new_segundo_nombre" name="new_segundo_nombre" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Primer Apellido *</label>
                        <input type="text" id="new_primer_apellido" name="new_primer_apellido" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Segundo Apellido</label>
                        <input type="text" id="new_segundo_apellido" name="new_segundo_apellido" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-xs font-bold mb-1">Correo Electrónico</label>
                        <input type="email" id="new_correo" name="new_correo" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Teléfono Celular</label>
                        <input type="text" id="new_tlf_celular" name="new_tlf_celular" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-1">Teléfono Local</label>
                        <input type="text" id="new_tlf_habitacion" name="new_tlf_habitacion" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none">Guardar y Continuar</button>
                    <button type="button" id="cancelCreateAlumnoBtn" class="text-gray-500 hover:underline">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Sección 2: Selección de Diplomado Abierto -->
        <div id="diplomadosAbiertosSection" class="hidden p-6 border rounded-lg bg-gray-50">
            
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Paso 2: Seleccionar</h2>
            
            <input type="hidden" id="selectedAlumnoId" name="alumno_id">
            <input type="hidden" id="selectedDiplomadoAbiertoId" name="diplomado_abierto_id">

            <div id="ofertasAbiertasList" class="space-y-3 max-h-96 overflow-y-auto border p-3 rounded-md bg-white">
                <!-- Se carga vía AJAX mediante preinscripcion_landing.js -->
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" id="preinscribirBtn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" disabled>
                    Finalizar Pre-inscripción
                </button>
            </div>
        </div>
    </div>

    <!-- Carga el script con las funcionalidades AJAX -->
    <script src="preinscripcion_landing.js"></script>
</body>
</html>