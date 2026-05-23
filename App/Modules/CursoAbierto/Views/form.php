<?php
// php_mvc_app/app/Modules/CursoAbierto/Views/form.php
// Se espera la variable $curso_abierto_data (vacía para crear, con datos para editar)
$is_edit = isset($curso_abierto_data['id']) && !empty($curso_abierto_data['id']);
?>
<div class="w-full space-y-6">

    <!-- TARJETA 1: Formulario Principal de Registro/Edición -->
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
            <?php echo ($is_edit) ? 'Editar Taller / Curso Abierto' : 'Crear Nuevo Taller / Curso Abierto'; ?>
        </h3>

        <form id="form_cursos_abiertos" action="<?php echo BASE_URL; ?>cursos_abiertos/<?php echo ($is_edit) ? 'edit/' . $curso_abierto_data['id'] : 'create'; ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
                    <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($curso_abierto_data['numero'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="curso_id" class="block text-gray-700 text-sm font-bold mb-2">Taller / Curso:</label>
                    <select id="curso_id" name="curso_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione un Curso</option>
                    </select>
                    <input type="hidden" name="curso_current" id="curso_current" value="<?php echo $curso_abierto_data['curso_id'] ?? ''; ?>">
                </div>
                <div>
                    <label for="sede_id" class="block text-gray-700 text-sm font-bold mb-2">Sede:</label>
                    <select id="sede_id" name="sede_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione una Sede</option>
                    </select>
                    <input type="hidden" name="sede_current" id="sede_current" value="<?php echo $curso_abierto_data['sede_id'] ?? ''; ?>">
                </div>
                <div>
                    <label for="estatus_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus:</label>
                    <select id="estatus_id" name="estatus_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione un Estatus</option>
                    </select>
                    <input type="hidden" name="estatus_current" id="estatus_current" value="<?php echo $curso_abierto_data['estatus_id'] ?? ''; ?>">
                </div>
                <div>
                    <label for="docente_id" class="block text-gray-700 text-sm font-bold mb-2">Docente/Coordinador:</label>
                    <select id="docente_id" name="docente_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione un Docente/Coordinador</option>
                    </select>
                    <input type="hidden" name="docente_current" id="docente_current" value="<?php echo $curso_abierto_data['docente_id'] ?? ''; ?>">
                </div>
                <div>
                    <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">Fecha:</label>
                    <input type="text" id="fecha" name="fecha" value="<?php echo htmlspecialchars($curso_abierto_data['fecha'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="YYYY-MM-DD" required>
                </div>
                <div>
                    <label for="convenio" class="block text-gray-700 text-sm font-bold mb-2">Convenio:</label>
                    <input type="text" id="convenio" name="convenio" value="<?php echo htmlspecialchars($curso_abierto_data['convenio'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="lg:col-span-4 md:col-span-2">
                    <label for="nombre_carta" class="block text-gray-700 text-sm font-bold mb-2">Nombre Carta:</label>
                    <textarea id="nombre_carta" name="nombre_carta" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"><?php echo htmlspecialchars($curso_abierto_data['nombre_carta'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    Guardar Taller / Curso Abierto
                </button>
                <a href="<?php echo BASE_URL; ?>cursos_abiertos" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- TARJETA 2: Alumnos Inscritos en la Apertura (Solo visible en edición) -->
    <?php if ($is_edit): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Alumnos Inscritos</h4>
            
            <?php if (isset($inscritos) && !empty($inscritos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cédula / Pasaporte</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre del Alumno</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Correo</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Inscripción</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus Inscripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscritos as $ins): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($ins['ci_pasapote']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($ins['alumno_nombre']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($ins['correo']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-500 text-xs">
                                            <?php echo !empty($ins['fecha_inscripcion']) ? htmlspecialchars(date('d-m-Y', strtotime($ins['fecha_inscripcion']))) : 'N/A'; ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php 
                                            $statusClass = 'bg-gray-100 text-gray-700'; // Default
                                            $estatusLower = strtolower($ins['estatus_inscripcion'] ?? '');
                                            if ($estatusLower == 'activo' || $estatusLower == 'inscrito' || $estatusLower == 'aprobado') {
                                                $statusClass = 'bg-green-100 text-green-800';
                                            } elseif ($estatusLower == 'pendiente' || $estatusLower == 'en espera') {
                                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                            } elseif ($estatusLower == 'retirado' || $estatusLower == 'inactivo') {
                                                $statusClass = 'bg-red-100 text-red-800';
                                            }
                                        ?>
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                            <span aria-hidden class="absolute inset-0 <?php echo $statusClass; ?> opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($ins['estatus_inscripcion'] ?? 'N/A'); ?></span>
                                        </span>
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
                    No hay alumnos inscritos en este taller abierto.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/curso_abierto.js'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>