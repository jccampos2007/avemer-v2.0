<?php
// app/Modules/Capitulo/Views/form.php

// Se espera la variable $capitulo_data (vacía para crear, con datos para editar)
$is_edit = isset($capitulo_data['id']) && !empty($capitulo_data['id']);
$form_action = $is_edit ? BASE_URL . 'capitulo/edit/' . $capitulo_data['id'] : BASE_URL . 'capitulo/create/';
$page_title = $is_edit ? 'Editar Capítulo' : 'Crear Nuevo Capítulo';

// Datos para pre-llenar los campos
$diplomado_id_val = $capitulo_data['diplomado_id'] ?? '';
$diplomado_nombre_val = htmlspecialchars($capitulo_data['diplomado_nombre'] ?? 'Seleccione un Diplomado'); // Para mostrar el nombre del diplomado
$numero_val = htmlspecialchars($capitulo_data['numero'] ?? '');
$nombre_val = htmlspecialchars($capitulo_data['nombre'] ?? '');
$descripcion_val = htmlspecialchars($capitulo_data['descripcion'] ?? ''); // Contenido HTML de CKEditor
$activo_val = $capitulo_data['activo'] ?? 1; // Por defecto activo
$orden_val = htmlspecialchars($capitulo_data['orden'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title ?></h3>
    <form id="formCapitulo" action="<?php echo $form_action; ?>" method="POST">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $capitulo_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="diplomado_id" class="block text-gray-700 text-sm font-bold mb-2">Diplomado:</label>
            <!-- Campo de diplomado: oculto y pre-llenado, o deshabilitado para mostrar -->
            <input type="text" id="diplomado_display_name" value="<?php echo $diplomado_nombre_val; ?>"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" readonly>
            <input type="hidden" id="diplomado_id" name="diplomado_id" value="<?php echo $diplomado_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
            <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="10">
        </div>

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="64">
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="5"><?php echo $descripcion_val; ?></textarea>
            <!-- NOTA: No uses 'required' en el textarea si usas CKEditor.
                     La validación de contenido vacío debe hacerse en JS. -->
        </div>

        <div class="mb-4">
            <label for="orden" class="block text-gray-700 text-sm font-bold mb-2">Orden:</label>
            <input type="number" id="orden" name="orden" value="<?php echo $orden_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
        </div>

        <div class="mb-6 flex items-center">
            <input type="checkbox" id="activo" name="activo" value="1" class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($activo_val == 1) ? 'checked' : ''; ?>>
            <label for="activo" class="text-gray-700 text-sm font-bold">Activo</label>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Capítulo' : 'Guardar Capítulo'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>capitulo<?php echo $diplomado_id_val ? '?diplomado_id=' . $diplomado_id_val : ''; ?>" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Capitulo/capitulo.js'; ?>