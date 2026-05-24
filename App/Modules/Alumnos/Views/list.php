<?php
// php_mvc_app/app/Modules/Alumnos/Views/list.php
use App\Core\Auth;

$canCreate = Auth::hasPermission('alumnos', 'crear');
$canEdit = Auth::hasPermission('alumnos', 'modificar');
$canDelete = Auth::hasPermission('alumnos', 'eliminar');
?>


<div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white p-6 rounded-lg shadow-sm border border-gray-150 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Alumnos</h2>
        <p class="text-sm text-gray-500 mt-1">Gestión y listado de registros.</p>
    </div>
        <div class="mt-4 md:mt-0">
            <?php if ($canCreate): ?>
            <a href="<?php echo BASE_URL; ?>alumnos/create" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear Nuevo Alumno
            </a>
            <?php endif; ?>
        </div>
</div>




<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50 mb-4">
        <h3 class="text-lg font-bold text-gray-700">Gestión de Alumnos</h3>
    </div>

    <table id="alumnosTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Foto</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">C.I./Pasaporte</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Los datos serán cargados por DataTables vía AJAX -->
        </tbody>
    </table>
</div>

<script>
    const ALUMNO_PERMISSIONS = {
        crear: <?php echo $canCreate ? 'true' : 'false'; ?>,
        modificar: <?php echo $canEdit ? 'true' : 'false'; ?>,
        eliminar: <?php echo $canDelete ? 'true' : 'false'; ?>
    };
</script>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/alumnos.js'; ?>