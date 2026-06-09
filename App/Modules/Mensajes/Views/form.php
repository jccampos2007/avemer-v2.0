<?php
// app/Modules/mensajes/Views/form.php

// Se espera la variable $mensajes_data (vacía para crear, con datos para editar)
$is_edit = isset($mensajes_data['id']) && !empty($mensajes_data['id']);
$form_action = $is_edit ? BASE_URL . 'mensajes/edit/' . htmlspecialchars($mensajes_data['id']) : BASE_URL . 'mensajes/create';
$page_title = $is_edit ? 'Editar Mensaje' : 'Crear Nuevo Mensaje';

// Datos para pre-llenar los campos
$titulo_val = htmlspecialchars($mensajes_data['titulo'] ?? '');
$mensaje_val = htmlspecialchars($mensajes_data['mensaje'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formmensajes" action="<?php echo $form_action; ?>" method="POST" data-nombre-carta="<?= $mensaje_val ?>">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $mensajes_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">titulo:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo $titulo_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="128">
            </div>

            <div class="lg:col-span-4 md:col-span-2">
                <label for="nombre_carta" class="block text-gray-700 text-sm font-bold mb-2">Mensaje:
                    <button type="button" class="toggle-html-btn text-xs ml-2 text-blue-600 hover:text-blue-800 font-medium">Ver HTML</button>
                </label>
                <div class="mb-2">
                    <div class="flex flex-wrap gap-1 placeholder-container">
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{alumnoName}}">{{alumnoName}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{ci}}">{{ci}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{correo}}">{{correo}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{tlf}}">{{tlf}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{programType}}">{{programType}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{programaNumero}}">{{programaNumero}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{programaNombre}}">{{programaNombre}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{sede}}">{{sede}}</span>
                        <span class="placeholder-badge cursor-pointer bg-gray-200 hover:bg-blue-200 text-gray-700 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded transition" data-placeholder="{{year}}">{{year}}</span>
                    </div>
                </div>
                <textarea id="nombre_carta" name="mensaje" class="input-form focus:outline-none focus:shadow-outline" rows="10"><?= isset($mensajes_data['mensaje']) ? htmlspecialchars($mensajes_data['mensaje']) : '' ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar Mensaje' : 'Guardar Mensaje'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>mensajes" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Mensajes/mensajes.js'; ?>

