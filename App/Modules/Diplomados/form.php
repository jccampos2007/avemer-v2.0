<?php
// php_mvc_app/app/Modules/Diplomados/Views/form.php
// Se espera la variable $diplomado_data (vacía para crear, con datos para editar)
$is_edit = isset($diplomado_data['id']) && !empty($diplomado_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Diplomado' : 'Crear Nuevo Diplomado'; ?></h3>
    <form action="<?php echo BASE_URL; ?>diplomados/<?php echo ($is_edit) ? 'update/' . htmlspecialchars($diplomado_data['id']) : 'store'; ?>" method="POST">
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre del Diplomado:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($diplomado_data['nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="siglas" class="block text-gray-700 text-sm font-bold mb-2">Siglas:</label>
            <input type="text" id="siglas" name="siglas" value="<?php echo htmlspecialchars($diplomado_data['siglas'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="8">
        </div>
        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4" required><?php echo htmlspecialchars($diplomado_data['descripcion'] ?? ''); ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="duracion_id" class="block text-gray-700 text-sm font-bold mb-2">Duración ID:</label>
                <input type="number" id="duracion_id" name="duracion_id" value="<?php echo htmlspecialchars($diplomado_data['duracion_id'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="costo" class="block text-gray-700 text-sm font-bold mb-2">Costo:</label>
                <input type="number" step="0.01" id="costo" name="costo" value="<?php echo htmlspecialchars($diplomado_data['costo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="inicial" class="block text-gray-700 text-sm font-bold mb-2">Inicial:</label>
                <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo htmlspecialchars($diplomado_data['inicial'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Diplomado
            </button>
            <a href="<?php echo BASE_URL; ?>diplomados" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo (si el formulario lo necesita, por ejemplo, para validación en cliente) -->
<script src="diplomados.js"></script>