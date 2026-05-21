<?php
// app/Modules/mensajes/Views/list.php
?>

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Mensajes</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>mensajes/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Mensaje
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">

    <table id="mensajesTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Título</th>
                <th class="py-3 px-6 text-left">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
            <!-- Los datos se cargarán aquí por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/mensajes.js'; ?>