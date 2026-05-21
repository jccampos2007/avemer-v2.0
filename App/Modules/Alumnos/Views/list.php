<?php
// php_mvc_app/app/Modules/Alumnos/Views/list.php
use App\Core\Auth;

$canCreate = Auth::hasPermission('alumnos', 'crear');
$canEdit = Auth::hasPermission('alumnos', 'modificar');
$canDelete = Auth::hasPermission('alumnos', 'eliminar');
?>

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Alumnos</h2>

<div class="flex justify-end mb-4">
    <?php if ($canCreate): ?>
    <a href="<?php echo BASE_URL; ?>alumnos/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Alumno
    </a>
    <?php endif; ?>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">

    <table id="alumnosTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Foto</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">C.I./Pasaporte</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre Completo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Correo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
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