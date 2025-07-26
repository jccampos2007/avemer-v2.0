<?php
// php_mvc_app/app/Modules/CursoAbierto/Views/list.php
?>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Taller Control</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>curso_control/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Taller Control
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table id="cursoControlTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Taller Control</th>
                <th class="py-3 px-6 text-left">Docente</th>
                <th class="py-3 px-6 text-left">Tema</th>
                <th class="py-3 px-6 text-left">Fecha</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/CursoControl/curso_control.js'; ?>