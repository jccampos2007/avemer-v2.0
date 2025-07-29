<?php
// app/Modules/Maestria/Views/form.php

// Se espera la variable $maestria_data (vacía para crear, con datos para editar)
$is_edit = isset($maestria_data['id']) && !empty($maestria_data['id']);
$form_action = $is_edit ? BASE_URL . 'maestria/edit/' . htmlspecialchars($maestria_data['id']) : BASE_URL . 'maestria/create';
$page_title = $is_edit ? 'Editar Maestría' : 'Crear Nueva Maestría';

// Datos para pre-llenar los campos
$nombre_val = htmlspecialchars($maestria_data['nombre'] ?? '');
$numero_val = htmlspecialchars($maestria_data['numero'] ?? '');
$duracion_id_val = htmlspecialchars($maestria_data['duracion_id'] ?? '');
$convenio_val = htmlspecialchars($maestria_data['convenio'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formMaestria" action="<?php echo $form_action; ?>" method="POST">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $maestria_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="128">
        </div>

        <div class="mb-4">
            <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
            <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="16">
        </div>

        <div class="mb-4">
            <label for="duracion_id" class="block text-gray-700 text-sm font-bold mb-2">Duración:</label>
            <select id="duracion_id" name="duracion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Duración</option>
            </select>
            <input type="hidden" name="duracion_current" id="duracion_current" value="<?php echo $duracion_id_val; ?>">
        </div>

        <div class="mb-6">
            <label for="convenio" class="block text-gray-700 text-sm font-bold mb-2">Convenio:</label>
            <input type="text" id="convenio" name="convenio" value="<?php echo $convenio_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="16">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar Maestría' : 'Guardar Maestría'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>maestria" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Maestria/maestria.js?1'; ?>