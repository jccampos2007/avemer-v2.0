<?php
// app/Modules/Capitulo/Views/form.php

// Se espera la variable $capitulo_data (vacía para crear, con datos para editar)
$is_edit = isset($capitulo_data['id']) && !empty($capitulo_data['id']);
$form_action = $is_edit ? BASE_URL . 'capitulo/edit/' . $capitulo_data['id'] : BASE_URL . 'capitulo/create';
$page_title = $is_edit ? 'Editar Capítulo' : 'Crear Nuevo Capítulo';

// Datos para pre-llenar los campos
$diplomado_id_val = $capitulo_data['diplomado_id'] ?? '';
$diplomado_nombre_val = htmlspecialchars($capitulo_data['diplomado_nombre'] ?? 'Seleccione un Diplomado'); // Para mostrar el nombre del diplomado
$numero_val = htmlspecialchars($capitulo_data['numero'] ?? '');
$nombre_val = htmlspecialchars($capitulo_data['nombre'] ?? '');
$descripcion_val = htmlspecialchars($capitulo_data['descripcion'] ?? '');
$activo_val = $capitulo_data['activo'] ?? 1; // Por defecto activo
$orden_val = htmlspecialchars($capitulo_data['orden'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title ?></h3>
    <form id="formCapitulo" action="<?php echo $form_action; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $capitulo_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="lg:col-span-4 md:col-span-2">
                <label for="diplomado_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Diplomado:</label>
                <input type="text" id="diplomado_autocomplete" class="input-form focus:outline-none focus:shadow-outline" placeholder="Buscar diplomado..." value="<?php echo $diplomado_nombre_val; ?>" required>
                <input type="hidden" name="diplomado_id" id="diplomado_id" value="<?php echo $diplomado_id_val; ?>">
            </div>

            <div>
                <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
                <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="10">
            </div>

            <div class="lg:col-span-2">
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="64">
            </div>

            <div>
                <label for="orden" class="block text-gray-700 text-sm font-bold mb-2">Orden:</label>
                <input type="number" id="orden" name="orden" value="<?php echo $orden_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required min="0">
            </div>

            <div class="lg:col-span-4 md:col-span-2">
                <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="input-form focus:outline-none focus:shadow-outline" rows="5"><?php echo $descripcion_val; ?></textarea>
            </div>

            <div>
                <label for="activo" class="block text-gray-700 text-sm font-bold mb-2">Activo:</label>
                <select id="activo" name="activo" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="1" <?php echo ($activo_val == 1) ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo ($activo_val == 0) ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
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

<div class="bg-white p-8 rounded-lg shadow-md w-full mt-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Lista de Capítulos</h3>

    <table id="capituloTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Número</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Activo</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm font-light">
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Capitulo/capitulo.js'; ?>