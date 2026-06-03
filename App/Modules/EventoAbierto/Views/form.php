<?php
// app/Modules/EventoAbierto/Views/form.php

// Se espera la variable $evento_abierto_data (vacía para crear, con datos para editar)
$is_edit = isset($evento_abierto_data['id']) && !empty($evento_abierto_data['id']);
$form_action = $is_edit ? BASE_URL . 'evento_abierto/edit/' . htmlspecialchars($evento_abierto_data['id']) : BASE_URL . 'evento_abierto/create';

// Datos para pre-llenar los selects y campos en JavaScript
$numero_val = htmlspecialchars($evento_abierto_data['numero'] ?? '');
$evento_id_val = $evento_abierto_data['evento_id'] ?? '';
$evento_nombre_val = htmlspecialchars($evento_abierto_data['evento_nombre'] ?? '');
$sede_id_val = $evento_abierto_data['sede_id'] ?? '';
$estatus_id_val = $evento_abierto_data['estatus_id'] ?? '';
$fecha_inicio_val = htmlspecialchars($evento_abierto_data['fecha_inicio'] ?? '');
$fecha_fin_val = htmlspecialchars($evento_abierto_data['fecha_fin'] ?? '');
$nombre_carta_val = htmlspecialchars($evento_abierto_data['nombre_carta'] ?? ''); // Contenido HTML de CKEditor
$docente_id_val = $evento_abierto_data['docente_id'] ?? '';
$docente_nombre_val = htmlspecialchars($evento_abierto_data['docente_nombre'] ?? '');
$costo_val = htmlspecialchars($evento_abierto_data['costo'] ?? '0.00');
$inicial_val = htmlspecialchars($evento_abierto_data['inicial'] ?? '0.00');
?>
<div class="w-full space-y-6">

    <!-- TARJETA 1: Formulario Principal de Registro/Edición -->
    <div id="form_main_card" class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div id="form_header_row" class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">
                <?php echo ($is_edit) ? 'Editar Evento Abierto' : 'Crear Nuevo Evento Abierto'; ?>
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

        <form id="formEventoAbierto" action="<?php echo $form_action; ?>" method="POST"
            data-evento-id="<?php echo $evento_id_val; ?>"
            data-sede-id="<?php echo $sede_id_val; ?>"
            data-estatus-id="<?php echo $estatus_id_val; ?>"
            data-nombre-carta="<?php echo htmlspecialchars($evento_abierto_data['nombre_carta'] ?? ''); ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
            <div id="form_collapsible_wrapper" class="grid transition-all duration-300" style="grid-template-rows: 1fr;">
                <div id="form_collapsible_content" class="min-h-0" style="overflow: visible;">

            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $evento_abierto_data['id']; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
                    <input type="text" id="numero" name="numero" value="<?php echo $numero_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required maxlength="10">
                </div>

                <div>
                    <label for="evento_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Evento:</label>
                    <input type="text" id="evento_autocomplete" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Buscar evento..." value="<?php echo $evento_nombre_val; ?>" required>
                    <input type="hidden" name="evento_id" id="evento_id" value="<?php echo $evento_id_val; ?>">
                </div>

                <div>
                    <label for="sede_id" class="block text-gray-700 text-sm font-bold mb-2">Sede:</label>
                    <select id="sede_id" name="sede_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione una Sede</option>
                        <!-- Opciones se llenarán con JS -->
                    </select>
                    <input type="hidden" name="sede_current" id="sede_current" value="<?php echo $sede_id_val; ?>">
                </div>

                <div>
                    <label for="estatus_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus:</label>
                    <select id="estatus_id" name="estatus_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione un Estatus</option>
                        <!-- Opciones se llenarán con JS -->
                    </select>
                    <input type="hidden" name="estatus_current" id="estatus_current" value="<?php echo $estatus_id_val; ?>">
                </div>

                <div>
                    <label for="docente_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Instructor:</label>
                    <input type="text" id="docente_autocomplete" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Buscar instructor..." value="<?php echo $docente_nombre_val; ?>">
                    <input type="hidden" name="docente_id" id="docente_id" value="<?php echo $docente_id_val; ?>">
                </div>

                <div>
                    <label for="fecha_inicio" class="block text-gray-700 text-sm font-bold mb-2">Fecha Inicio:</label>
                    <input type="text" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required placeholder="YYYY-MM-DD">
                </div>
                <div>
                    <label for="fecha_fin" class="block text-gray-700 text-sm font-bold mb-2">Fecha Fin:</label>
                    <input type="text" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required placeholder="YYYY-MM-DD">
                </div>
                <div>
                    <label for="costo" class="block text-gray-700 text-sm font-bold mb-2">Costo:</label>
                    <input type="number" step="0.01" id="costo" name="costo" value="<?php echo $costo_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required min="0">
                </div>
                <div>
                    <label for="inicial" class="block text-gray-700 text-sm font-bold mb-2">Inicial:</label>
                    <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo $inicial_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required min="0">
                </div>

                <div class="lg:col-span-4 md:col-span-2">
                    <label for="nombre_carta" class="block text-gray-700 text-sm font-bold mb-2">Nombre Carta:</label>
                    <textarea id="nombre_carta" name="nombre_carta" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="5"><?php echo $nombre_carta_val; ?></textarea>
                    <!-- NOTA: No uses 'required' en el textarea si usas CKEditor.
                             La validación de contenido vacío debe hacerse en JS, como en curso_abierto.js -->
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    <?php echo ($is_edit) ? 'Actualizar Evento Abierto' : 'Guardar Evento Abierto'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>evento_abierto" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">
                    Cancelar
                </a>
            </div>
                </div> <!-- End of form_collapsible_content -->
            </div> <!-- End of form_collapsible_wrapper -->
        </form>
    </div>

    <!-- TARJETA 2: Alumnos Inscritos en este Evento Abierto (Solo visible en modo Edición) -->
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
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscritos as $ins): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($ins['tipo_documento'] ?? '') . $ins['ci_pasaporte']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($ins['alumno_nombre']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 text-xs"><?php echo htmlspecialchars($ins['correo']); ?></p>
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
                                            if ($estatusLower == 'activo' || $estatusLower == 'inscrito' || $estatusLower == 'aprobado' || $estatusLower == 'confirmado') {
                                                $statusClass = 'bg-green-100 text-green-800';
                                            } elseif ($estatusLower == 'pendiente' || $estatusLower == 'en espera' || $estatusLower == 'espera') {
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
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <a href="<?php echo BASE_URL; ?>inscripcion_evento/edit/<?php echo $ins['inscripcion_id']; ?>" class="text-blue-600 hover:text-blue-800" title="Editar inscripción">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
                    No hay alumnos inscritos en esta apertura de evento todavía.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/EventoAbierto/evento_abierto.js?1'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>