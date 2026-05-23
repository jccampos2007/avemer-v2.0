<?php
// php_mvc_app/app/Modules/InscripcionEvento/Views/form.php
// Se espera la variable $inscripcion_Evento_data (vacía para crear, con datos para editar)
$is_edit = isset($inscripcion_evento_data['id']) && !empty($inscripcion_evento_data['id']);

// La acción del formulario debe apuntar al controlador de inscripcion_evento
$form_action = BASE_URL . 'inscripcion_evento/' . (($is_edit) ? 'edit/' . $inscripcion_evento_data['id'] : 'create');
$page_title = ($is_edit) ? 'Editar Inscripción' : 'Crear Nueva Inscripción';

// Datos para pre-llenar los selects y campos en JavaScript
$evento_abierto_id_val = $inscripcion_evento_data['evento_abierto_id'] ?? '';
$alumno_id_val = $inscripcion_evento_data['alumno_id'] ?? '';
$estatus_inscripcion_id_val = $inscripcion_evento_data['estatus_inscripcion_id'] ?? '';
$estatus_inscripcion_id_val = $inscripcion_evento_data['estatus_inscripcion_id'] ?? '';
$alumno_nombre_current = $inscripcion_evento_data['alumno_nombre_completo'] ?? '';
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formInscripcionEvento" action="<?php echo $form_action; ?>" method="POST">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $inscripcion_evento_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="evento_abierto_id" class="label-form">Evento Abierto:</label>
                <select id="evento_abierto_id" name="evento_abierto_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Evento Abierto</option>
                    <!-- Opciones se llenarán con JS -->
                </select>
                <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
                <input type="hidden" name="evento_abierto_current" id="evento_abierto_current" value="<?php echo $evento_abierto_id_val; ?>">
            </div>

            <div>
                <label for="alumno_id" class="label-form">Alumno:</label>
                <input id="alumno_autocomplete" value="<?php echo $alumno_nombre_current; ?>" class="input-form focus:outline-none focus:shadow-outline">
                <input type="hidden" id="alumno_id" name="alumno_id" value="<?php echo $alumno_id_val; ?>">
            </div>

            <div>
                <label for="estatus_inscripcion_id" class="label-form">Estatus de Inscripción:</label>
                <select id="estatus_inscripcion_id" name="estatus_inscripcion_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Estatus</option>
                    <!-- Opciones se llenarán con JS -->
                </select>
                <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
                <input type="hidden" name="estatus_inscripcion_current" id="estatus_inscripcion_current" value="<?php echo $estatus_inscripcion_id_val; ?>">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Inscripción' : 'Guardar Inscripción'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>inscripcion_evento" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/inscripcion_evento.js'; ?>