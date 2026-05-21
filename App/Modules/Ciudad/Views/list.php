<?php
use App\Core\Auth;

$canCreate = Auth::hasPermission('estado', 'crear');
$canEdit = Auth::hasPermission('estado', 'modificar');
$canDelete = Auth::hasPermission('estado', 'eliminar');
?>

<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Ciudades / Estados</h2>

<div class="flex justify-end mb-4">
    <?php if ($canCreate): ?>
    <a href="<?php echo BASE_URL; ?>ciudad/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nueva Ciudad
    </a>
    <?php endif; ?>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table id="ciudadTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID País</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data loaded via DataTables AJAX -->
        </tbody>
    </table>
</div>

<script>
    const CIUDAD_PERMISSIONS = {
        crear: <?php echo $canCreate ? 'true' : 'false'; ?>,
        modificar: <?php echo $canEdit ? 'true' : 'false'; ?>,
        eliminar: <?php echo $canDelete ? 'true' : 'false'; ?>
    };
</script>

<?php $page_js = 'js/modules/ciudad.js'; ?>
