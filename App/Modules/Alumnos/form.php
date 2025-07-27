<?php
// php_mvc_app/app/Modules/Alumnos/Views/form.php
// Se espera la variable $alumno_data (vacía para crear, con datos para editar)
$is_edit = isset($alumno_data['id']) && !empty($alumno_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Alumno' : 'Crear Nuevo Alumno'; ?></h3>
    <form id="form_alumnos" action="<?php echo BASE_URL; ?>alumnos/<?php echo ($is_edit) ? 'edit/' . $alumno_data['id'] : 'create'; ?>" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="ci_pasapote" class="block text-gray-700 text-sm font-bold mb-2">C.I. / Pasaporte:</label>
                <input type="text" id="ci_pasapote" name="ci_pasapote" value="<?php echo htmlspecialchars($alumno_data['ci_pasapote'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="primer_nombre" class="block text-gray-700 text-sm font-bold mb-2">Primer Nombre:</label>
                <input type="text" id="primer_nombre" name="primer_nombre" value="<?php echo htmlspecialchars($alumno_data['primer_nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_nombre" class="block text-gray-700 text-sm font-bold mb-2">Segundo Nombre:</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo htmlspecialchars($alumno_data['segundo_nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="primer_apellido" class="block text-gray-700 text-sm font-bold mb-2">Primer Apellido:</label>
                <input type="text" id="primer_apellido" name="primer_apellido" value="<?php echo htmlspecialchars($alumno_data['primer_apellido'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_apellido" class="block text-gray-700 text-sm font-bold mb-2">Segundo Apellido:</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo htmlspecialchars($alumno_data['segundo_apellido'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($alumno_data['correo'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_celular" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Celular:</label>
                <input type="text" id="tlf_celular" name="tlf_celular" value="<?php echo htmlspecialchars($alumno_data['tlf_celular'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="fecha_nacimiento" class="block text-gray-700 text-sm font-bold mb-2">Fecha Nacimiento:</label>
                <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($alumno_data['fecha_nacimiento'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" placeholder="YYYY-MM-DD">
            </div>
        </div>

        <div class="mb-4">
            <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección:</label>
            <textarea id="direccion" name="direccion" class="input-form focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($alumno_data['direccion'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="profesion_oficio_id" class="block text-gray-700 text-sm font-bold mb-2">Profesión/Oficio:</label>
                <select id="profesion_oficio_id" name="profesion_oficio_id" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="profesion_oficio_current" id="profesion_oficio_current" value="<?php echo $alumno_data['profesion_oficio_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estado_id" class="block text-gray-700 text-sm font-bold mb-2">Estado ID:</label>
                <select id="estado_id" name="estado_id" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estado_current" id="estado_current" value="<?php echo $alumno_data['estado_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="nacionalidad_id" class="block text-gray-700 text-sm font-bold mb-2">Nacionalidad ID:</label>
                <select id="nacionalidad_id" name="nacionalidad_id" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="nacionalidad_current" id="nacionalidad_current" value="<?php echo $alumno_data['nacionalidad_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estatus_activo_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus Activo ID:</label>
                <select id="estatus_activo_id" name="estatus_activo_id" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estatus_activo_current" id="estatus_activo_current" value="<?php echo $alumno_data['estatus_activo_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="tlf_habitacion" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Habitación:</label>
                <input type="text" id="tlf_habitacion" name="tlf_habitacion" value="<?php echo htmlspecialchars($alumno_data['tlf_habitacion'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_trabajo" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Trabajo:</label>
                <input type="text" id="tlf_trabajo" name="tlf_trabajo" value="<?php echo htmlspecialchars($alumno_data['tlf_trabajo'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="calle_avenida" class="block text-gray-700 text-sm font-bold mb-2">Calle/Avenida:</label>
                <input type="text" id="calle_avenida" name="calle_avenida" value="<?php echo htmlspecialchars($alumno_data['calle_avenida'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="casa_apartamento" class="block text-gray-700 text-sm font-bold mb-2">Casa/Apartamento:</label>
                <input type="text" id="casa_apartamento" name="casa_apartamento" value="<?php echo htmlspecialchars($alumno_data['casa_apartamento'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="nombre_universidad" class="block text-gray-700 text-sm font-bold mb-2">Nombre Universidad:</label>
                <input type="text" id="nombre_universidad" name="nombre_universidad" value="<?php echo htmlspecialchars($alumno_data['nombre_universidad'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="nombre_especialidad" class="block text-gray-700 text-sm font-bold mb-2">Nombre Especialidad:</label>
                <input type="text" id="nombre_especialidad" name="nombre_especialidad" value="<?php echo htmlspecialchars($alumno_data['nombre_especialidad'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Documentos Entregados:</label>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="chk_planilla" class="form-checkbox h-5 w-5 text-blue-600 rounded" <?php echo (isset($alumno_data['chk_planilla']) && $alumno_data['chk_planilla'] == 1) ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">Planilla</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="chk_cedula" class="form-checkbox h-5 w-5 text-blue-600 rounded" <?php echo (isset($alumno_data['chk_cedula']) && $alumno_data['chk_cedula'] == 1) ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">Cédula</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="chk_notas" class="form-checkbox h-5 w-5 text-blue-600 rounded" <?php echo (isset($alumno_data['chk_notas']) && $alumno_data['chk_notas'] == 1) ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">Notas</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="chk_titulo" class="form-checkbox h-5 w-5 text-blue-600 rounded" <?php echo (isset($alumno_data['chk_titulo']) && $alumno_data['chk_titulo'] == 1) ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">Título</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="chk_partida" class="form-checkbox h-5 w-5 text-blue-600 rounded" <?php echo (isset($alumno_data['chk_partida']) && $alumno_data['chk_partida'] == 1) ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">Partida de Nacimiento</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="foto" class="block text-gray-700 text-sm font-bold mb-2">Foto:</label>
                <input type="file" id="foto" name="foto" class="input-form focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($alumno_data['foto'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Foto actual presente.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="imagen" class="block text-gray-700 text-sm font-bold mb-2">Imagen (Adicional):</label>
                <input type="file" id="imagen" name="imagen" class="input-form focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($alumno_data['imagen'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Imagen actual presente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Alumno
            </button>
            <a href="<?php echo BASE_URL; ?>alumnos" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo (si el formulario lo necesita, por ejemplo, para validación en cliente) -->
<?php $page_js = '../app/Modules/Alumnos/alumnos.js'; ?>