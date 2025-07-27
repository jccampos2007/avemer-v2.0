<?php
// php_mvc_app/app/Modules/CursoAbierto/Views/form.php
// Se espera la variable $curso_abierto_data (vacía para crear, con datos para editar)
$is_edit = isset($curso_abierto_data['id']) && !empty($curso_abierto_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Taller Abierto' : 'Crear Nuevo Taller Abierto'; ?></h3>
    <form id="form_cursos_abiertos" action="<?php echo BASE_URL; ?>cursos_abiertos/<?php echo ($is_edit) ? 'edit/' . $curso_abierto_data['id'] : 'create'; ?>" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="numero" class="label-form">Número:</label>
                <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($curso_abierto_data['numero'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="curso_id" class="label-form">Taller:</label>
                <select id="curso_id" name="curso_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Curso</option>
                </select>
                <input type="hidden" name="curso_current" id="curso_current" value="<?php echo $curso_abierto_data['curso_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="sede_id" class="label-form">Sede:</label>
                <select id="sede_id" name="sede_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione una Sede</option>
                </select>
                <input type="hidden" name="sede_current" id="sede_current" value="<?php echo $curso_abierto_data['sede_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estatus_id" class="label-form">Estatus:</label>
                <select id="estatus_id" name="estatus_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Estatus</option>
                </select>
                <input type="hidden" name="estatus_current" id="estatus_current" value="<?php echo $curso_abierto_data['estatus_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="docente_id" class="label-form">Docente/Coordinador:</label>
                <select id="docente_id" name="docente_id" class="input-form focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un Docente/Coordinador</option>
                </select>
                <input type="hidden" name="docente_current" id="docente_current" value="<?php echo $curso_abierto_data['docente_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="fecha" class="label-form">Fecha:</label>
                <input type="text" id="fecha" name="fecha" value="<?php echo htmlspecialchars($curso_abierto_data['fecha'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" placeholder="YYYY-MM-DD" required>
            </div>
            <div>
                <label for="convenio" class="label-form">Convenio:</label>
                <input type="text" id="convenio" name="convenio" value="<?php echo htmlspecialchars($curso_abierto_data['convenio'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="mb-4">
            <label for="nombre_carta" class="label-form">Nombre Carta:</label>
            <textarea id="nombre_carta" name="nombre_carta" class="input-form focus:outline-none focus:shadow-outline" rows="3"><?php echo htmlspecialchars($curso_abierto_data['nombre_carta'] ?? ''); ?></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Taller Abierto
            </button>
            <a href="<?php echo BASE_URL; ?>cursos_abiertos" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/CursoAbierto/curso_abierto.js'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>