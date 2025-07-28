<?php
// php_mvc_app/app/Modules/Diplomado/list.php
// Se espera la variable $diplomado (ya no se usa directamente para renderizar la tabla)
?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Diplomado</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>diplomado/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Diplomado
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table id="diplomadoTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Siglas</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
            <!-- Los datos se cargarán aquí por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/diplomado/diplomado.js'; ?>