<?php
// app/Modules/Diplomado/Views/form.php
// Se espera la variable $diplomado_data (vacía para crear, con datos para editar)
$is_edit = isset($diplomado_data['id']) && !empty($diplomado_data['id']);
$form_action = $is_edit ? BASE_URL . 'diplomado/edit/' . $diplomado_data['id'] : BASE_URL . 'diplomado/create';
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- TARJETA 1: Formulario Principal de Registro/Edición -->
    <div id="form_main_card" class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div id="form_header_row" class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">
                <?php echo ($is_edit) ? 'Editar Diplomado' : 'Crear Nuevo Diplomado'; ?>
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

        <form id="formDiplomado" action="<?php echo $form_action; ?>" method="POST">
            <div id="form_collapsible_wrapper" class="grid transition-all duration-300" style="grid-template-rows: 1fr;">
                <div id="form_collapsible_content" class="min-h-0" style="overflow: visible;">

            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $diplomado_data['id'] ?? ''; ?>">
            <?php endif; ?>

            <div class="mb-4">
                <label for="duracion_id" class="block text-gray-700 text-sm font-bold mb-2">Duración:</label>
                <select id="duracion_id" name="duracion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione una Duración</option>
                    <!-- Opciones se llenarán con JS -->
                </select>
                <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
                <input type="hidden" name="duracion_current" id="duracion_current" value="<?php echo $diplomado_data['duracion_id'] ?? ''; ?>">
            </div>

            <div class="mb-4">
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($diplomado_data['nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="128">
            </div>

            <div class="mb-4">
                <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="5"><?php echo $diplomado_data['descripcion'] ?? ''; ?></textarea>
            </div>

            <div class="mb-4">
                <label for="siglas" class="block text-gray-700 text-sm font-bold mb-2">Siglas:</label>
                <input type="text" id="siglas" name="siglas" value="<?php echo htmlspecialchars($diplomado_data['siglas'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="8">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="costo" class="block text-gray-700 text-sm font-bold mb-2">Costo:</label>
                    <input type="number" step="0.01" id="costo" name="costo" value="<?php echo htmlspecialchars($diplomado_data['costo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
                </div>
                <div>
                    <label for="inicial" class="block text-gray-700 text-sm font-bold mb-2">Inicial:</label>
                    <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo htmlspecialchars($diplomado_data['inicial'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo ($is_edit) ? 'Actualizar Diplomado' : 'Guardar Diplomado'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>diplomado" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                    Cancelar
                </a>
            </div>
                </div> <!-- End of form_collapsible_content -->
            </div> <!-- End of form_collapsible_wrapper -->
        </form>
    </div>

    <!-- TARJETA 3: Capítulos Asociados (Solo visible en modo Edición) -->
    <?php if ($is_edit): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Capítulos del Diplomado</h4>
            
            <?php if (isset($capitulos) && !empty($capitulos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Orden</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nro.</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre / Descripción</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capitulos as $capitulo): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-500 font-medium text-xs"><?php echo htmlspecialchars($capitulo['orden']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($capitulo['numero']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($capitulo['nombre']); ?></p>
                                        <p class="text-gray-500 text-xs mt-1 max-w-lg leading-relaxed">
                                            <?php echo htmlspecialchars($capitulo['descripcion']); ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php 
                                            $activo = (int)$capitulo['activo'];
                                            $statusClass = $activo === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                            $statusLabel = $activo === 1 ? 'Activo' : 'Inactivo';
                                        ?>
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                            <span aria-hidden class="absolute inset-0 <?php echo $statusClass; ?> opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo $statusLabel; ?></span>
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
                    Este diplomado no posee capítulos asociados registrados.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- TARJETA 2: Aperturas Asociadas (Solo visible en modo Edición) -->
    <?php if ($is_edit): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Diplomados Aperturados</h4>
            
            <?php if (isset($diplomados_abiertos) && !empty($diplomados_abiertos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sede</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fechas</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Inscritos</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diplomados_abiertos as $abierto): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($abierto['oferta_numero']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($abierto['sede_nombre'] ?? 'N/A'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-500 text-xs">
                                            Desde: <?php echo htmlspecialchars(date('d-m-Y', strtotime($abierto['fecha_inicio']))); ?><br>
                                            Hasta: <?php echo htmlspecialchars(date('d-m-Y', strtotime($abierto['fecha_fin']))); ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <span class="relative inline-block px-3 py-1 font-semibold text-blue-900 leading-tight">
                                            <span aria-hidden class="absolute inset-0 bg-blue-100 opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($abierto['total_inscritos']); ?> Inscritos</span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <?php 
                                            $statusClass = 'bg-gray-100 text-gray-700'; // Default
                                            if (strtolower($abierto['estatus_oferta']) == 'activo') {
                                                $statusClass = 'bg-green-100 text-green-800';
                                            } elseif (strtolower($abierto['estatus_oferta']) == 'inactivo') {
                                                $statusClass = 'bg-red-100 text-red-800';
                                            }
                                        ?>
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                            <span aria-hidden class="absolute inset-0 <?php echo $statusClass; ?> opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($abierto['estatus_oferta'] ?? 'N/A'); ?></span>
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
                    Este diplomado no tiene ofertas ni convocatorias (Diplomados Aperturados) registradas.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/diplomado.js?#1'; ?>