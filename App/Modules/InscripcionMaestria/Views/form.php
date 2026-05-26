<?php
// app/Modules/InscripcionMaestria/Views/form.php
// Se espera la variable $inscripcion_maestria_data (vacía para crear, con datos para editar)
$is_edit = isset($inscripcion_maestria_data['id']) && !empty($inscripcion_maestria_data['id']);

// La acción del formulario debe apuntar al controlador de inscripcion_maestria
$form_action = BASE_URL . 'inscripcion_maestria/' . (($is_edit) ? 'edit/' . htmlspecialchars($inscripcion_maestria_data['id']) : 'create');
$page_title = ($is_edit) ? 'Editar Inscripción de Maestría' : 'Crear Nueva Inscripción de Maestría';

// Datos para pre-llenar los selects y campos en JavaScript
$maestria_abierto_id_val = $inscripcion_maestria_data['maestria_abierto_id'] ?? '';
$maestria_abierto_nombre_val = htmlspecialchars($inscripcion_maestria_data['maestria_abierto_nombre'] ?? '');
$alumno_id_val = $inscripcion_maestria_data['alumno_id'] ?? '';
$alumno_nombre_current = $inscripcion_maestria_data['alumno_nombre_completo'] ?? '';
$estatus_inscripcion_id_val = $inscripcion_maestria_data['estatus_inscripcion_id'] ?? '';
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formInscripcionMaestria" action="<?php echo $form_action; ?>" method="POST"
        data-maestria-abierto-id="<?php echo $maestria_abierto_id_val; ?>"
        data-alumno-id="<?php echo $alumno_id_val; ?>"
        data-estatus-inscripcion-id="<?php echo $estatus_inscripcion_id_val; ?>">
        <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $inscripcion_maestria_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="lg:col-span-2">
                <label for="maestria_abierto_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Maestría Abierta:</label>
                <input type="text" id="maestria_abierto_autocomplete" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Buscar maestría abierta..." value="<?php echo $maestria_abierto_nombre_val; ?>" required>
                <input type="hidden" name="maestria_abierto_id" id="maestria_abierto_id" value="<?php echo $maestria_abierto_id_val; ?>">
            </div>

            <div>
                <label for="alumno_id" class="block text-gray-700 text-sm font-bold mb-2">Alumno:</label>
                <input id="alumno_autocomplete" value="<?php echo $alumno_nombre_current; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <input type="hidden" id="alumno_id" name="alumno_id" value="<?php echo $alumno_id_val; ?>">
            </div>

            <div>
                <label for="estatus_inscripcion_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus de Inscripción:</label>
                <select id="estatus_inscripcion_id" name="estatus_inscripcion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Estatus</option>
                </select>
                <input type="hidden" name="estatus_inscripcion_current" id="estatus_inscripcion_current" value="<?php echo $estatus_inscripcion_id_val; ?>">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Inscripción' : 'Guardar Inscripción'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>inscripcion_maestria" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/InscripcionMaestria/inscripcion_maestria.js'; ?>