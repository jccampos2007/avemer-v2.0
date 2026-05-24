<?php
// php_mvc_app/app/Modules/Cursos/Views/list.php
use App\Core\Auth;

$canCreate = Auth::hasPermission('cursos', 'crear');
$canEdit = Auth::hasPermission('cursos', 'modificar');
$canDelete = Auth::hasPermission('cursos', 'eliminar');
?>


<div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white p-6 rounded-lg shadow-sm border border-gray-150 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Talleres / Cursos</h2>
        <p class="text-sm text-gray-500 mt-1">Gestión y listado de registros.</p>
    </div>
        <div class="mt-4 md:mt-0">
            <?php if ($canCreate): ?>
            <a href="<?php echo BASE_URL; ?>cursos/create" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear Nuevo Taller / Curso
            </a>
            <?php endif; ?>
        </div>
</div>




<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50 mb-4">
        <h3 class="text-lg font-bold text-gray-700">Gestión de Talleres / Cursos</h3>
    </div>

    <table id="cursosTable" class="w-full min-w-full leading-normal display responsive">
        <thead>
            <tr>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Número</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Horas</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Convenio</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Los datos serán cargados por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<script>
    const CURSOS_PERMISSIONS = {
        crear: <?php echo $canCreate ? 'true' : 'false'; ?>,
        modificar: <?php echo $canEdit ? 'true' : 'false'; ?>,
        eliminar: <?php echo $canDelete ? 'true' : 'false'; ?>
    };
</script>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Cursos/cursos.js'; ?>