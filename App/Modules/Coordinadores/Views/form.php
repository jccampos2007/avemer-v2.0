<?php
// php_mvc_app/app/Modules/Coordinadores/Views/form.php
// Se espera la variable $coordinador_data (vacía para crear, con datos para editar)
$is_edit = isset($coordinador_data['id']) && !empty($coordinador_data['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Coordinador' : 'Crear Nuevo Coordinador'; ?></h3>
    <form id="form_coordinadores" action="<?php echo BASE_URL; ?>coordinadores/<?php echo ($is_edit) ? 'update/' . $coordinador_data['id'] : 'create'; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="ci_pasapote" class="label-form">C.I. / Pasaporte:</label>
                <input type="text" id="ci_pasapote" name="ci_pasapote" value="<?php echo htmlspecialchars($coordinador_data['ci_pasapote'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="primer_nombre" class="label-form">Primer Nombre:</label>
                <input type="text" id="primer_nombre" name="primer_nombre" value="<?php echo htmlspecialchars($coordinador_data['primer_nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_nombre" class="label-form">Segundo Nombre:</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo htmlspecialchars($coordinador_data['segundo_nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="primer_apellido" class="label-form">Primer Apellido:</label>
                <input type="text" id="primer_apellido" name="primer_apellido" value="<?php echo htmlspecialchars($coordinador_data['primer_apellido'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="segundo_apellido" class="label-form">Segundo Apellido:</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo htmlspecialchars($coordinador_data['segundo_apellido'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="correo" class="label-form">Correo:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($coordinador_data['correo'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_celular" class="label-form">Teléfono Celular:</label>
                <input type="text" id="tlf_celular" name="tlf_celular" value="<?php echo htmlspecialchars($coordinador_data['tlf_celular'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="fecha_nacimiento" class="label-form">Fecha Nacimiento:</label>
                <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($coordinador_data['fecha_nacimiento'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" placeholder="YYYY-MM-DD">
            </div>

            <div class="lg:col-span-4 md:col-span-2">
                <label for="direccion" class="label-form">Dirección:</label>
                <textarea id="direccion" name="direccion" class="input-form focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($coordinador_data['direccion'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="profesion_oficio_autocomplete" class="label-form">Profesión/Oficio:</label>
                <input type="text" id="profesion_oficio_autocomplete" class="input-form focus:outline-none focus:shadow-outline" placeholder="Escriba para buscar...">
                <input type="hidden" name="profesion_oficio_id" id="profesion_oficio_id" value="<?php echo $coordinador_data['profesion_oficio_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estado_autocomplete" class="label-form">Estado:</label>
                <input type="text" id="estado_autocomplete" class="input-form focus:outline-none focus:shadow-outline" placeholder="Escriba para buscar...">
                <input type="hidden" name="estado_id" id="estado_id" value="<?php echo $coordinador_data['estado_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="nacionalidad_autocomplete" class="label-form">Nacionalidad:</label>
                <input type="text" id="nacionalidad_autocomplete" class="input-form focus:outline-none focus:shadow-outline" placeholder="Escriba para buscar...">
                <input type="hidden" name="nacionalidad_id" id="nacionalidad_id" value="<?php echo $coordinador_data['nacionalidad_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="estatus_activo_id" class="label-form">Estatus Activo:</label>
                <select id="estatus_activo_id" name="estatus_activo_id" class="input-form focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estatus_activo_current" id="estatus_activo_current" value="<?php echo $coordinador_data['estatus_activo_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="tlf_habitacion" class="label-form">Teléfono Habitación:</label>
                <input type="text" id="tlf_habitacion" name="tlf_habitacion" value="<?php echo htmlspecialchars($coordinador_data['tlf_habitacion'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tlf_trabajo" class="label-form">Teléfono Trabajo:</label>
                <input type="text" id="tlf_trabajo" name="tlf_trabajo" value="<?php echo htmlspecialchars($coordinador_data['tlf_trabajo'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline">
            </div>

            <div>
                <label for="foto" class="label-form">Foto:</label>
                <input type="file" id="foto" name="foto" class="input-form focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($coordinador_data['foto'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Foto actual presente.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="imagen" class="label-form">Imagen (Adicional):</label>
                <input type="file" id="imagen" name="imagen" class="input-form focus:outline-none focus:shadow-outline">
                <?php if ($is_edit && !empty($coordinador_data['imagen'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Imagen actual presente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Coordinador
            </button>
            <a href="<?php echo BASE_URL; ?>coordinadores" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>


<?php $page_js = 'asset/js/Coordinadores/coordinadores.js'; ?>