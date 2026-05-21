<?php
// php_mvc_app/App/Modules/ProfesionOficio/list.php
?>
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Profesiones u Oficios</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>profesion_oficio/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nueva Profesión u Oficio
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table id="profesionTable" class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/profesion_oficio.js'; ?>
