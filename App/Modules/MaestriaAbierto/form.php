<?php
// app/Modules/MaestriaAbierto/Views/form.php

// Se espera la variable $maestria_abierto_data (vacía para crear, con datos para editar)
$is_edit = isset($maestria_abierto_data['id']) && !empty($maestria_abierto_data['id']);
$form_action = $is_edit ? BASE_URL . 'maestria_abierto/edit/' . htmlspecialchars($maestria_abierto_data['id']) : BASE_URL . 'maestria_abierto/create';
$page_title = $is_edit ? 'Editar Maestría Abierta' : 'Crear Nueva Maestría Abierta';

// Datos para pre-llenar los campos
$numero_val = htmlspecialchars($maestria_abierto_data['numero'] ?? '');
$maestria_id_val = htmlspecialchars($maestria_abierto_data['maestria_id'] ?? '');
$sede_id_val = htmlspecialchars($maestria_abierto_data['sede_id'] ?? '');
$estatus_id_val = htmlspecialchars($maestria_abierto_data['estatus_id'] ?? '');
$docente_id_val = htmlspecialchars($maestria_abierto_data['docente_id'] ?? '');
$fecha_val = htmlspecialchars($maestria_abierto_data['fecha'] ?? '');
$nombre_carta_val = htmlspecialchars($maestria_abierto_data['nombre_carta'] ?? ''); // Contenido HTML de CKEditor
$convenio_val = htmlspecialchars($maestria_abierto_data['convenio'] ?? '');
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formMaestriaAbierto" action="<?php echo $form_action; ?>" method="POST"
        data-maestria-id="<?php echo $maestria_id_val; ?>"
        data-sede-id="<?php echo $sede_id_val; ?>"
        data-estatus-id="<?php echo $estatus_id_val; ?>"
        data-docente-id="<?php echo $docente_id_val; ?>"
        data-nombre-carta="<?php echo $nombre_carta_val; ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $maestria_abierto_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
            <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="16">
        </div>

        <div class="mb-4">
            <label for="maestria_id" class="block text-gray-700 text-sm font-bold mb-2">Maestría:</label>
            <select id="maestria_id" name="maestria_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Maestría</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="maestria_current" id="maestria_current" value="<?php echo $maestria_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="sede_id" class="block text-gray-700 text-sm font-bold mb-2">Sede:</label>
            <select id="sede_id" name="sede_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Sede</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="sede_current" id="sede_current" value="<?php echo $sede_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="estatus_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus:</label>
            <select id="estatus_id" name="estatus_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Estatus</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="estatus_current" id="estatus_current" value="<?php echo $estatus_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="docente_id" class="block text-gray-700 text-sm font-bold mb-2">Docente:</label>
            <select id="docente_id" name="docente_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Docente</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="docente_current" id="docente_current" value="<?php echo $docente_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">Fecha:</label>
            <input type="text" id="fecha" name="fecha" value="<?php echo $fecha_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="mb-4">
            <label for="convenio" class="block text-gray-700 text-sm font-bold mb-2">Convenio:</label>
            <input type="text" id="convenio" name="convenio" value="<?php echo $convenio_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="32">
        </div>

        <div class="mb-6">
            <label for="nombre_carta" class="block text-gray-700 text-sm font-bold mb-2">Nombre Carta:</label>
            <textarea id="nombre_carta" name="nombre_carta" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="5" required><?php echo $nombre_carta_val; ?></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar Maestría Abierta' : 'Guardar Maestría Abierta'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>maestria_abierto" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/MaestriaAbierto/maestria_abierto.js'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>