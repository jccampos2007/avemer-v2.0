<?php
// php_mvc_app/App/Modules/Sede/form.php
$is_edit = isset($sede_data['id']) && !empty($sede_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Sede' : 'Crear Nueva Sede'; ?></h3>
    <form id="form_sede" action="<?php echo BASE_URL; ?>sede/<?php echo ($is_edit) ? 'update/' . htmlspecialchars($sede_data['id']) : 'store'; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="md:col-span-2 lg:col-span-4">
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre de la Sede:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($sede_data['nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="tlf_sede" class="block text-gray-700 text-sm font-bold mb-2">Teléfono:</label>
                <input type="text" id="tlf_sede" name="tlf_sede" value="<?php echo htmlspecialchars($sede_data['tlf_sede'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($sede_data['correo'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="estado_id" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                <select id="estado_id" name="estado_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un estado</option>
                    <!-- Aquí se cargarán los estados -->
                </select>
                <input type="hidden" name="estado_current" id="estado_current" value="<?php echo $sede_data['estado_id'] ?? ''; ?>">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Sede
            </button>
            <a href="<?php echo BASE_URL; ?>sede" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Sede/sede.js'; ?>
