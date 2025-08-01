<?php
// php_mvc_app/app/Modules/Coordinadores/list.php
// Se espera la variable $coordinadores (ya no se usa directamente para renderizar la tabla)
?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Coordinadores</h2>

<div class="mb-4 flex justify-between items-center">
    <div class="max-w-2/3 pr-4">
        <label for="diplomado_filter_id" class="block text-gray-700 text-sm font-bold mb-2">Seleccionar Diplomado:</label>
        <select id="diplomado_filter_id" name="diplomado_filter_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">-- Seleccione un Diplomado --</option>
            <!-- Opciones se llenarán con JS -->
        </select>
    </div>
    <div class="min-w-1/3 flex justify-end">
        <a href="#" id="createCapituloBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed">
            Crear Nuevo Capítulo
        </a>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table id="capituloTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Activo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
            <!-- Los datos se cargarán aquí por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Capitulo/capitulo.js'; ?>