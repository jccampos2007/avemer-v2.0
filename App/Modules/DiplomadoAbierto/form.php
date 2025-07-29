<?php
// app/Modules/DiplomadoAbierto/Views/form.php

// Se espera la variable $diplomado_abierto_data (vacía para crear, con datos para editar)
$is_edit = isset($diplomado_abierto_data['id']) && !empty($diplomado_abierto_data['id']);
$form_action = $is_edit ? BASE_URL . 'diplomado_abierto/edit/' . htmlspecialchars($diplomado_abierto_data['id']) : BASE_URL . 'diplomado_abierto/create';
$page_title = $is_edit ? 'Editar Diplomado Abierto' : 'Crear Nuevo Diplomado Abierto';

// Datos para pre-llenar los selects y campos en JavaScript
$numero_val = htmlspecialchars($diplomado_abierto_data['numero'] ?? '');
$diplomado_id_val = $diplomado_abierto_data['diplomado_id'] ?? '';
$sede_id_val = $diplomado_abierto_data['sede_id'] ?? '';
$estatus_id_val = $diplomado_abierto_data['estatus_id'] ?? '';
$fecha_inicio_val = htmlspecialchars($diplomado_abierto_data['fecha_inicio'] ?? '');
$fecha_fin_val = htmlspecialchars($diplomado_abierto_data['fecha_fin'] ?? '');
$nombre_carta_val = htmlspecialchars($diplomado_abierto_data['nombre_carta'] ?? ''); // Contenido HTML de CKEditor
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Diplomado' : 'Crear Nuevo Diplomado'; ?></h3>
    <form id="formDiplomadoAbierto" action="<?php echo $form_action; ?>" method="POST"
        data-diplomado-id="<?php echo $diplomado_id_val; ?>"
        data-sede-id="<?php echo $sede_id_val; ?>"
        data-estatus-id="<?php echo $estatus_id_val; ?>"
        data-nombre-carta="<?php echo htmlspecialchars($diplomado_abierto_data['nombre_carta'] ?? ''); ?>">
        <!-- Usamos htmlspecialchars para nombre_carta en data-*, pero CKEditor lo manejará directamente -->

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $diplomado_abierto_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
            <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="50">
        </div>

        <div class="mb-4">
            <label for="diplomado_id" class="block text-gray-700 text-sm font-bold mb-2">Diplomado:</label>
            <select id="diplomado_id" name="diplomado_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Diplomado</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
            <input type="hidden" name="diplomado_current" id="diplomado_current" value="<?php echo $diplomado_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="sede_id" class="block text-gray-700 text-sm font-bold mb-2">Sede:</label>
            <select id="sede_id" name="sede_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Sede</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
            <input type="hidden" name="sede_current" id="sede_current" value="<?php echo $sede_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="estatus_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus:</label>
            <select id="estatus_id" name="estatus_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Estatus</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
            <input type="hidden" name="estatus_current" id="estatus_current" value="<?php echo $estatus_id_val; ?>">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="fecha_inicio" class="block text-gray-700 text-sm font-bold mb-2">Fecha Inicio:</label>
                <input type="text" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="fecha_fin" class="block text-gray-700 text-sm font-bold mb-2">Fecha Fin:</label>
                <input type="text" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
        </div>

        <div class="mb-6">
            <label for="nombre_carta" class="block text-gray-700 text-sm font-bold mb-2">Nombre Carta:</label>
            <textarea id="nombre_carta" name="nombre_carta" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="5"><?php echo $nombre_carta_val; ?></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Diplomado Abierto' : 'Guardar Diplomado Abierto'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>diplomado_abierto" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/DiplomadoAbierto/diplomado_abierto.js?2'; ?>