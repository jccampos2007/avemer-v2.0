<?php
// php_mvc_app/app/Modules/Coordinadores/list.php
// Se espera la variable $coordinadores (ya no se usa directamente para renderizar la tabla)
?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Apertura Evento</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>evento_abierto/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Apertura
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">

    <table id="eventoAbiertoTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Número</th>
                <th class="py-3 px-6 text-left">Evento</th>
                <th class="py-3 px-6 text-left">Sede</th>
                <th class="py-3 px-6 text-left">Estatus</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Los datos serán cargados por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/EventoAbierto/evento_abierto.js'; ?>