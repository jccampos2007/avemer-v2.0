<?php
// app/Modules/InscripcionDiplomado/Views/form.php

// Se espera la variable $inscripcion_diplomado_data (vacía para crear, con datos para editar)
$is_edit = isset($inscripcion_diplomado_data['id']) && !empty($inscripcion_diplomado_data['id']);
$form_action = $is_edit ? BASE_URL . 'inscripcion_diplomado/edit/' . htmlspecialchars($inscripcion_diplomado_data['id']) : BASE_URL . 'inscripcion_diplomado/create';
$page_title = $is_edit ? 'Editar Inscripción de Diplomado' : 'Crear Nueva Inscripción de Diplomado';

// Datos para pre-llenar los selects
$diplomado_abierto_id_val = $inscripcion_diplomado_data['diplomado_abierto_id'] ?? '';
$alumno_id_val = $inscripcion_diplomado_data['alumno_id'] ?? '';
$alumno_nombre_current = $inscripcion_diplomado_data['alumno_nombre_completo'] ?? '';
$estatus_inscripcion_id_val = $inscripcion_diplomado_data['estatus_inscripcion_id'] ?? '';
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formInscripcionDiplomado" action="<?php echo $form_action; ?>" method="POST"
        data-diplomado-abierto-id="<?php echo $diplomado_abierto_id_val; ?>"
        data-alumno-id="<?php echo $alumno_id_val; ?>"
        data-estatus-inscripcion-id="<?php echo $estatus_inscripcion_id_val; ?>">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $inscripcion_diplomado_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="diplomado_abierto_id" class="block text-gray-700 text-sm font-bold mb-2">Diplomado Abierto:</label>
            <select id="diplomado_abierto_id" name="diplomado_abierto_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Diplomado Abierto</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="diplomado_abierto_current" id="diplomado_abierto_current" value="<?php echo $diplomado_abierto_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="alumno_id" class="block text-gray-700 text-sm font-bold mb-2">Alumno:</label>
            <input id="alumno_autocomplete" value="<?php echo $alumno_nombre_current; ?>" class="input-form focus:outline-none focus:shadow-outline">
            <input type="hidden" id="alumno_id" name="alumno_id" value="<?php echo $alumno_id_val; ?>">
        </div>

        <div class="mb-6">
            <label for="estatus_inscripcion_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus de Inscripción:</label>
            <select id="estatus_inscripcion_id" name="estatus_inscripcion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Estatus</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <input type="hidden" name="estatus_inscripcion_current" id="estatus_inscripcion_current" value="<?php echo $estatus_inscripcion_id_val; ?>">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Inscripción' : 'Guardar Inscripción'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>inscripcion_diplomado" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/InscripcionDiplomado/inscripcion_diplomado.js?2'; ?>