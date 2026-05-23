<?php
// php_mvc_app/app/Modules/Coordinadores/list.php
// Se espera la variable $coordinadores (ya no se usa directamente para renderizar la tabla)
?>


<div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white p-6 rounded-lg shadow-sm border border-gray-150 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Coordinadores</h2>
        <p class="text-sm text-gray-500 mt-1">Gestión y listado de registros.</p>
    </div>
        <div class="mt-4 md:mt-0">
            <a href="<?php echo BASE_URL; ?>capitulo/create" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear Nuevo Capítulo
            </a>
        </div>
</div>


<div class="mb-4 flex justify-between items-center">
    <div class="max-w-2/3 pr-4">
        <label for="diplomado_filter_id" class="block text-gray-700 text-sm font-bold mb-2">Seleccionar Diplomado:</label>
        <select id="diplomado_filter_id" name="diplomado_filter_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">-- Seleccione un Diplomado --</option>
            <!-- Opciones se llenarán con JS -->
        </select>
    </div>
    
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50 mb-4">
        <h3 class="text-lg font-bold text-gray-700">Gestión de Coordinadores</h3>
    </div>
    <table id="capituloTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Número</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Activo</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
            <!-- Los datos se cargarán aquí por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/capitulo.js'; ?>