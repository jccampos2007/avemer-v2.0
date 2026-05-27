<?php
// php_mvc_app/App/Modules/Banco/form.php
$is_edit = isset($banco_data['id']) && !empty($banco_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Banco' : 'Crear Nuevo Banco'; ?></h3>
    <form id="form_banco" action="<?php echo BASE_URL; ?>banco/<?php echo ($is_edit) ? 'update/' . htmlspecialchars($banco_data['id']) : 'store'; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre del Banco:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($banco_data['nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Banco
            </button>
            <a href="<?php echo BASE_URL; ?>banco" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Banco/banco.js'; ?>
