<?php
// php_mvc_app/app/Modules/Alumnos/Views/form.php
// Se espera la variable $alumno_data (vacía para crear, con datos para editar)
$is_edit = isset($alumno_data['id']) && !empty($alumno_data['id']);
?>
<div class="w-full space-y-6">

    <!-- TARJETA 1: Formulario Principal de Registro/Edición -->
    <div id="form_main_card" class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div id="form_header_row" class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">
                <?php echo ($is_edit) ? 'Editar Alumno' : 'Crear Nuevo Alumno'; ?>
            </h3>
            <?php if ($is_edit): ?>
            <div class="flex items-center">
                <div class="flex items-center gap-3 select-none">
                    <button type="button" id="toggle_form_collapse_btn" class="relative inline-flex h-6 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 bg-green-200" role="switch" aria-checked="false">
                        <span class="sr-only">Ocultar / Mostrar</span>
                        <span aria-hidden="true" id="toggle_form_collapse_dot" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-300 ease-in-out translate-x-0"></span>
                    </button>
                    <span class="text-sm font-bold text-gray-700 cursor-pointer" id="toggle_form_text">Ocultar</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <form id="form_alumnos" action="<?php echo BASE_URL; ?>alumnos/<?php echo ($is_edit) ? 'edit/' . $alumno_data['id'] : 'create'; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
            <div id="form_collapsible_wrapper" class="grid transition-all duration-300" style="grid-template-rows: 1fr;">
                <div id="form_collapsible_content" class="min-h-0" style="overflow: visible;">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label for="ci_pasapote" class="block text-gray-700 text-sm font-bold mb-2">C.I. / Pasaporte:</label>
                            <input type="text" id="ci_pasapote" name="ci_pasapote" value="<?php echo htmlspecialchars($alumno_data['ci_pasapote'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="primer_nombre" class="block text-gray-700 text-sm font-bold mb-2">Primer Nombre:</label>
                            <input type="text" id="primer_nombre" name="primer_nombre" value="<?php echo htmlspecialchars($alumno_data['primer_nombre'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="segundo_nombre" class="block text-gray-700 text-sm font-bold mb-2">Segundo Nombre:</label>
                            <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo htmlspecialchars($alumno_data['segundo_nombre'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="primer_apellido" class="block text-gray-700 text-sm font-bold mb-2">Primer Apellido:</label>
                            <input type="text" id="primer_apellido" name="primer_apellido" value="<?php echo htmlspecialchars($alumno_data['primer_apellido'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="segundo_apellido" class="block text-gray-700 text-sm font-bold mb-2">Segundo Apellido:</label>
                            <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo htmlspecialchars($alumno_data['segundo_apellido'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo:</label>
                            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($alumno_data['correo'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="tlf_celular" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Celular:</label>
                            <input type="text" id="tlf_celular" name="tlf_celular" value="<?php echo htmlspecialchars($alumno_data['tlf_celular'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="fecha_nacimiento" class="block text-gray-700 text-sm font-bold mb-2">Fecha Nacimiento:</label>
                            <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($alumno_data['fecha_nacimiento'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="YYYY-MM-DD">
                        </div>

                        <div class="lg:col-span-4 md:col-span-2">
                            <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección:</label>
                            <textarea id="direccion" name="direccion" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($alumno_data['direccion'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label for="profesion_oficio_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Profesión/Oficio:</label>
                            <input type="text" id="profesion_oficio_autocomplete" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Escriba para buscar...">
                            <input type="hidden" name="profesion_oficio_id" id="profesion_oficio_id" value="<?php echo $alumno_data['profesion_oficio_id'] ?? ''; ?>">
                        </div>
                        <div>
                            <label for="estado_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                            <input type="text" id="estado_autocomplete" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Escriba para buscar...">
                            <input type="hidden" name="estado_id" id="estado_id" value="<?php echo $alumno_data['estado_id'] ?? ''; ?>">
                        </div>
                        <div>
                            <label for="nacionalidad_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Nacionalidad:</label>
                            <input type="text" id="nacionalidad_autocomplete" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Escriba para buscar...">
                            <input type="hidden" name="nacionalidad_id" id="nacionalidad_id" value="<?php echo $alumno_data['nacionalidad_id'] ?? ''; ?>">
                        </div>
                        <div>
                            <label for="estatus_activo_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus Activo:</label>
                            <select id="estatus_activo_id" name="estatus_activo_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Seleccione una opción</option>
                            </select>
                            <input type="hidden" name="estatus_activo_current" id="estatus_activo_current" value="<?php echo $alumno_data['estatus_activo_id'] ?? ''; ?>">
                        </div>
                        <div>
                            <label for="tlf_habitacion" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Habitación:</label>
                            <input type="text" id="tlf_habitacion" name="tlf_habitacion" value="<?php echo htmlspecialchars($alumno_data['tlf_habitacion'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="tlf_trabajo" class="block text-gray-700 text-sm font-bold mb-2">Teléfono Trabajo:</label>
                            <input type="text" id="tlf_trabajo" name="tlf_trabajo" value="<?php echo htmlspecialchars($alumno_data['tlf_trabajo'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="calle_avenida" class="block text-gray-700 text-sm font-bold mb-2">Calle/Avenida:</label>
                            <input type="text" id="calle_avenida" name="calle_avenida" value="<?php echo htmlspecialchars($alumno_data['calle_avenida'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="casa_apartamento" class="block text-gray-700 text-sm font-bold mb-2">Casa/Apartamento:</label>
                            <input type="text" id="casa_apartamento" name="casa_apartamento" value="<?php echo htmlspecialchars($alumno_data['casa_apartamento'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="nombre_universidad" class="block text-gray-700 text-sm font-bold mb-2">Nombre Universidad:</label>
                            <input type="text" id="nombre_universidad" name="nombre_universidad" value="<?php echo htmlspecialchars($alumno_data['nombre_universidad'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="nombre_especialidad" class="block text-gray-700 text-sm font-bold mb-2">Nombre Especialidad:</label>
                            <input type="text" id="nombre_especialidad" name="nombre_especialidad" value="<?php echo htmlspecialchars($alumno_data['nombre_especialidad'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="lg:col-span-4 md:col-span-2">
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <label class="block text-gray-700 text-sm font-bold mb-3">Documentos Entregados:</label>
                                <div class="flex flex-wrap gap-5">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="chk_planilla" class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150" <?php echo (isset($alumno_data['chk_planilla']) && $alumno_data['chk_planilla'] == 1) ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-gray-700 text-sm">Planilla</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="chk_cedula" class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150" <?php echo (isset($alumno_data['chk_cedula']) && $alumno_data['chk_cedula'] == 1) ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-gray-700 text-sm">Cédula</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="chk_notas" class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150" <?php echo (isset($alumno_data['chk_notas']) && $alumno_data['chk_notas'] == 1) ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-gray-700 text-sm">Notas</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="chk_titulo" class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150" <?php echo (isset($alumno_data['chk_titulo']) && $alumno_data['chk_titulo'] == 1) ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-gray-700 text-sm">Título</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="chk_partida" class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150" <?php echo (isset($alumno_data['chk_partida']) && $alumno_data['chk_partida'] == 1) ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-gray-700 text-sm">Partida de Nacimiento</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="foto" class="block text-gray-700 text-sm font-bold mb-2">Foto:</label>
                            <input type="file" id="foto" name="foto" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if ($is_edit && !empty($alumno_data['foto'])): ?>
                                <p class="text-xs text-green-600 mt-1.5 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Foto actual registrada en el sistema.
                                </p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="imagen" class="block text-gray-700 text-sm font-bold mb-2">Imagen (Adicional):</label>
                            <input type="file" id="imagen" name="imagen" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if ($is_edit && !empty($alumno_data['imagen'])): ?>
                                <p class="text-xs text-green-600 mt-1.5 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Imagen complementaria registrada.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-between border-t pt-5">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                            Guardar Alumno
                        </button>
                        <a href="<?php echo BASE_URL; ?>alumnos" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">
                            Cancelar
                        </a>
                    </div>
                </div> <!-- End of form_collapsible_content -->
            </div> <!-- End of form_collapsible_wrapper -->
        </form>
    </div>

    <!-- TARJETA 2: Listado de Inscripciones Activas (Solo en Edición) -->
    <?php if ($is_edit): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Inscripciones (Ofertas Académicas)</h4>
            
            <?php if (isset($inscripciones) && !empty($inscripciones)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Oferta</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus Inscripción</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus Oferta</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $inscripcion): 
                                $tipoRoute = match ($inscripcion['tipo']) {
                                    'Diplomado' => 'inscripcion_diplomado',
                                    'Curso/Taller' => 'inscripcion_curso',
                                    'Evento' => 'inscripcion_evento',
                                    'Maestría' => 'inscripcion_maestria',
                                    default => null,
                                };
                            ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($inscripcion['tipo']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($inscripcion['oferta_numero']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($inscripcion['oferta_nombre']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <span class="relative inline-block px-3 py-1 font-semibold text-blue-900 leading-tight">
                                            <span aria-hidden class="absolute inset-0 bg-blue-100 opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($inscripcion['estatus_inscripcion']); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php 
                                            $statusClass = 'bg-gray-100 text-gray-700'; // Default
                                            if (strtolower($inscripcion['estatus_oferta']) == 'activo') {
                                                $statusClass = 'bg-green-100 text-green-800';
                                            } elseif (strtolower($inscripcion['estatus_oferta']) == 'inactivo') {
                                                $statusClass = 'bg-red-100 text-red-800';
                                            }
                                        ?>
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                            <span aria-hidden class="absolute inset-0 <?php echo $statusClass; ?> opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($inscripcion['estatus_oferta']); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php if ($tipoRoute): ?>
                                        <a href="<?php echo BASE_URL . $tipoRoute . '/edit/' . $inscripcion['inscripcion_id']; ?>" class="text-blue-600 hover:text-blue-800" title="Editar inscripción">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 bg-gray-50 border border-gray-100 rounded text-gray-500 text-sm flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Este alumno no tiene inscripciones registradas.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php $page_js = 'asset/js/Alumnos/alumnos.js'; ?>