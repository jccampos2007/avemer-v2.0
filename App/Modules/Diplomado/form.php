<?php
// php_mvc_app/app/Modules/CursoAbierto/Views/form.php
// Se espera la variable $diplomado_data (vacía para crear, con datos para editar)
$is_edit = isset($diplomado_data['id']) && !empty($diplomado_data['id']);
$form_action = $is_edit ? BASE_URL . 'diplomado/edit/' . $diplomado_data['id'] : BASE_URL . 'diplomado/create';
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Diplomado' : 'Crear Nuevo Diplomado'; ?></h3>
    <form id="formDiplomado" action="<?php echo $form_action; ?>" method="POST">
        <!-- Usamos htmlspecialchars para descripcion en data-*, pero CKEditor lo manejará directamente -->

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $diplomado_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="duracion_id" class="block text-gray-700 text-sm font-bold mb-2">Duración:</label>
            <select id="duracion_id" name="duracion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Duración</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
            <input type="hidden" name="duracion_current" id="duracion_current" value="<?php echo $diplomado_data['duracion_id']; ?>">
        </div>

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $diplomado_data['nombre']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="128">
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="5"><?php echo $diplomado_data['descripcion']; ?></textarea>
        </div>

        <div class="mb-4">
            <label for="siglas" class="block text-gray-700 text-sm font-bold mb-2">Siglas:</label>
            <input type="text" id="siglas" name="siglas" value="<?php echo $diplomado_data['siglas']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="8">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="costo" class="block text-gray-700 text-sm font-bold mb-2">Costo:</label>
                <input type="number" step="0.01" id="costo" name="costo" value="<?php echo $diplomado_data['costo']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
            </div>
            <div>
                <label for="inicial" class="block text-gray-700 text-sm font-bold mb-2">Inicial:</label>
                <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo $diplomado_data['inicial']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Diplomado' : 'Guardar Diplomado'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>diplomado" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Diplomado/diplomado.js?4'; ?>