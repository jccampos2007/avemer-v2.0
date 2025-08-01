<?php
// app/Modules/Curso/form.php
// Se espera la variable $curso_data (vacía para crear, con datos para editar)
$is_edit = isset($curso_data['id']) && !empty($curso_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Taller' : 'Crear Nuevo Taller'; ?></h3>
    <form id="formCurso" action="<?php echo BASE_URL; ?>cursos/<?php echo ($is_edit) ? 'edit/' . $curso_data['id'] : 'create'; ?>" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="nombre" class="label-form">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($curso_data['nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="128">
            </div>
            <div>
                <label for="numero" class="label-form">Número:</label>
                <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($curso_data['numero'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" maxlength="16">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="horas" class="label-form">Horas:</label>
                <input type="number" id="horas" name="horas" value="<?php echo $curso_data['horas'] ?? '0'; ?>" class="input-form focus:outline-none focus:shadow-outline" required min="0">
            </div>
            <div>
                <label for="convenio" class="label-form">Convenio:</label>
                <input type="text" id="convenio" name="convenio" value="<?php echo htmlspecialchars($curso_data['convenio'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" maxlength="16">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Curso' : 'Guardar Curso'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>cursos" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo (si es necesario) -->
<?php $page_js = '../app/Modules/Curso/curso.js'; ?>