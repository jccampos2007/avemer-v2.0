<?php
// php_mvc_app/App/Modules/Sede/list.php
use App\Core\Auth;

$canCreate = Auth::hasPermission('sede', 'crear');
$canEdit = Auth::hasPermission('sede', 'modificar');
$canDelete = Auth::hasPermission('sede', 'eliminar');
?>
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Sedes</h2>

<div class="flex justify-end mb-4">
    <?php if ($canCreate): ?>
    <a href="<?php echo BASE_URL; ?>sede/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nueva Sede
    </a>
    <?php endif; ?>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table id="sedeTable" class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Teléfono</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Correo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    const SEDE_PERMISSIONS = {
        crear: <?php echo $canCreate ? 'true' : 'false'; ?>,
        modificar: <?php echo $canEdit ? 'true' : 'false'; ?>,
        eliminar: <?php echo $canDelete ? 'true' : 'false'; ?>
    };
</script>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/sede.js'; ?>
