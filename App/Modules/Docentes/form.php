<?php
// php_mvc_app/app/Modules/Docentes/Views/form.php
// Se espera la variable $docente_data (vacía para crear, con datos para editar)
$is_edit = isset($docente_data['id']) && !empty($docente_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Docente' : 'Crear Nuevo Docente'; ?></h3>
    <form id="form_docentes" action="<?php echo BASE_URL; ?>docentes/<?php echo ($is_edit) ? 'update/' . htmlspecialchars($docente_data['id']) : 'store'; ?>" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="ci_pasapote" class="block text-gray-700 text-sm font-bold mb-2">C.I. / Pasaporte:</label>
                <input type="text" id="ci_pasapote" name="ci_pasapote" value="<?php echo htmlspecialchars($docente_data['ci_pasapote'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="primer_nombre" class="block text-gray-700 text-sm font-bold mb-2">Primer Nombre:</label>
                <input type="text" id="primer_nombre" name="primer_nombre" value="<?php echo htmlspecialchars($docente_data['primer_nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_nombre" class="block text-gray-700 text-sm font-bold mb-2">Segundo Nombre:</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo htmlspecialchars($docente_data['segundo_nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="primer_apellido" class="block text-gray-700 text-sm font-bold mb-2">Primer Apellido:</label>
                <input type="text" id="primer_apellido" name="primer_apellido" value="<?php echo htmlspecialchars($docente_data['primer_apellido'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_apellido" class="block text-gray-700 text-sm font-bold mb-2">Segundo Apellido:</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo htmlspecialchars($docente_data['segundo_apellido'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($docente_data['correo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_celular" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Celular:</label>
                <input type="text" id="tlf_celular" name="tlf_celular" value="<?php echo htmlspecialchars($docente_data['tlf_celular'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="fecha_nacimiento" class="block text-gray-700 text-sm font-bold mb-2">Fecha Nacimiento:</label>
                <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($docente_data['fecha_nacimiento'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="YYYY-MM-DD">
            </div>
        </div>

        <div class="mb-4">
            <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección:</label>
            <textarea id="direccion" name="direccion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($docente_data['direccion'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="profesion_oficio_id" class="block text-gray-700 text-sm font-bold mb-2">Profesión/Oficio:</label>
                <select id="profesion_oficio_id" name="profesion_oficio_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="profesion_oficio_current" id="profesion_oficio_current" value="<?php echo $docente_data['profesion_oficio_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estado_id" class="block text-gray-700 text-sm font-bold mb-2">Estado ID:</label>
                <select id="estado_id" name="estado_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estado_current" id="estado_current" value="<?php echo $docente_data['estado_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="nacionalidad_id" class="block text-gray-700 text-sm font-bold mb-2">Nacionalidad ID:</label>
                <select id="nacionalidad_id" name="nacionalidad_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="nacionalidad_current" id="nacionalidad_current" value="<?php echo $docente_data['nacionalidad_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estatus_activo_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus Activo ID:</label>
                <select id="estatus_activo_id" name="estatus_activo_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estatus_activo_current" id="estatus_activo_current" value="<?php echo $docente_data['estatus_activo_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="tlf_habitacion" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Habitación:</label>
                <input type="text" id="tlf_habitacion" name="tlf_habitacion" value="<?php echo htmlspecialchars($docente_data['tlf_habitacion'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_trabajo" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Trabajo:</label>
                <input type="text" id="tlf_trabajo" name="tlf_trabajo" value="<?php echo htmlspecialchars($docente_data['tlf_trabajo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="foto" class="block text-gray-700 text-sm font-bold mb-2">Foto:</label>
                <input type="file" id="foto" name="foto" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($docente_data['foto'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Foto actual presente.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="imagen" class="block text-gray-700 text-sm font-bold mb-2">Imagen (Adicional):</label>
                <input type="file" id="imagen" name="imagen" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($docente_data['imagen'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Imagen actual presente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Docente
            </button>
            <a href="<?php echo BASE_URL; ?>docentes" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Docentes/docentes.js'; ?>