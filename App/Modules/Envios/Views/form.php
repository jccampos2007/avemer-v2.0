<?php
// app/Modules/envios/Views/form.php

// Se espera la variable $envios_data (vacía para crear, con datos para editar)
$is_edit = isset($envios_data['id']) && !empty($envios_data['id']);
$form_action = $is_edit ? BASE_URL . 'envios/edit/' . htmlspecialchars($envios_data['id']) : BASE_URL . 'envios/create';
$page_title = $is_edit ? 'Editar Mensaje' : 'Crear Nuevo Mensaje';

// Datos para pre-llenar los campos
$titulo_val = htmlspecialchars($envios_data['titulo'] ?? '');
$mensaje_val = htmlspecialchars($envios_data['mensaje'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formenvios" action="<?php echo $form_action; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $envios_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">titulo:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo $titulo_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="128">
            </div>

            <div class="lg:col-span-4 md:col-span-2">
                <label for="mensaje" class="block text-gray-700 text-sm font-bold mb-2">Mensaje:</label>
                <!-- <textarea id="mensaje" name="mensaje" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="256" src="../../../index.php"></textarea> -->
                <textarea id="mensaje" name="mensaje" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="256"><?= isset($envios_data['mensaje']) ? htmlspecialchars($envios_data['mensaje']) : '' ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar Mensaje' : 'Guardar Mensaje'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>envios" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Envios/envios.js'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>