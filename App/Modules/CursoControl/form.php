<?php
// php_mvc_app/app/Modules/CursoControl/Views/form.php
// Se espera la variable $curso_control_data (vacía para crear, con datos para editar)
$is_edit = isset($curso_control_data['id']) && !empty($curso_control_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Taller Abierto' : 'Crear Nuevo Taller Abierto'; ?></h3>
    <form id="formCursoControl" action="<?php echo BASE_URL; ?>cursos_abiertos/<?php echo ($is_edit) ? 'update/' . $curso_control_data['id'] : 'store'; ?>" method="POST"
        data-curso-abierto-id="<?php echo $curso_control_id_val; ?>"
        data-docente-id="<?php echo $docente_id_val; ?>"
        data-fecha="<?php echo $fecha_val; ?>">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $curso_control_data['id']; ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="curso_control_id" class="block text-gray-700 text-sm font-bold mb-2">Taller Control:</label>
            <select id="curso_control_id" name="curso_control_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Taller Control</option>
                <!-- Opciones se llenarán con JS -->
            </select>
        </div>

        <div class="mb-4">
            <label for="docente_id" class="block text-gray-700 text-sm font-bold mb-2">Docente:</label>
            <select id="docente_id" name="docente_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione un Docente</option>
                <!-- Opciones se llenarán con JS -->
            </select>
        </div>

        <div class="mb-4">
            <label for="tema" class="block text-gray-700 text-sm font-bold mb-2">Tema:</label>
            <input type="text" id="tema" name="tema" value="<?php echo $tema_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="32">
        </div>

        <div class="mb-6">
            <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">Fecha:</label>
            <input type="text" id="fecha" name="fecha" value="<?php echo $fecha_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="YYYY-MM-DD" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar' : 'Guardar'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>curso_control" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/CursoControl/curso_control.js'; ?>