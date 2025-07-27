<?php
// app/Modules/Evento/Views/form.php

// Se espera la variable $evento_data (vacía para crear, con datos para editar)
$is_edit = isset($evento_data['id']) && !empty($evento_data['id']);
$form_action = $is_edit ? BASE_URL . 'evento/edit/' . $evento_data['id'] : BASE_URL . 'evento/create';
$page_title = $is_edit ? 'Editar Evento' : 'Crear Nuevo Evento';

// Datos para pre-llenar los selects y campos en JavaScript
$duracion_id_val = $evento_data['duracion_id'] ?? '';
$nombre_val = htmlspecialchars($evento_data['nombre'] ?? '');
$descripcion_val = htmlspecialchars($evento_data['descripcion'] ?? '');
$siglas_val = htmlspecialchars($evento_data['siglas'] ?? '');
$costo_val = htmlspecialchars($evento_data['costo'] ?? '0.00'); // Valor por defecto para float
$inicial_val = htmlspecialchars($evento_data['inicial'] ?? '0.00'); // Valor por defecto para float
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Evento' : 'Crear Nuevo Evento'; ?></h3>
    <form id="formEvento" action="<?php echo $form_action; ?>" method="POST"
        data-duracion-id="<?php echo $duracion_id_val; ?>">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $evento_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="duracion_id" class="label-form">Duración:</label>
            <select id="duracion_id" name="duracion_id" class="input-form focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Duración</option>
                <!-- Opciones se llenarán con JS -->
            </select>
            <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
            <input type="hidden" name="duracion_current" id="duracion_current" value="<?php echo $duracion_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="nombre" class="label-form">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="64">
        </div>

        <div class="mb-4">
            <label for="descripcion" class="label-form">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="input-form focus:outline-none focus:shadow-outline" rows="4" required><?php echo $descripcion_val; ?></textarea>
        </div>

        <div class="mb-4">
            <label for="siglas" class="label-form">Siglas:</label>
            <input type="text" id="siglas" name="siglas" value="<?php echo $siglas_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="8">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="costo" class="label-form">Costo:</label>
                <input type="number" step="0.01" id="costo" name="costo" value="<?php echo $costo_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required min="0">
            </div>
            <div>
                <label for="inicial" class="label-form">Inicial:</label>
                <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo $inicial_val; ?>" class="input-form focus:outline-none focus:shadow-outline" required min="0">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Evento' : 'Guardar Evento'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>evento" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo (si es necesario) -->
<?php $page_js = '../app/Modules/Evento/evento.js?1'; ?>