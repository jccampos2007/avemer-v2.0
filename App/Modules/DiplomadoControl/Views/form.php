<?php
// php_mvc_app/app/Modules/DiplomadoControl/Views/form.php
// $is_edit indica si viene con un diplomado abierto ya seleccionado y estructurado
?>
<div class="w-full space-y-6">

    <!-- TARJETA 1: Configuración de Parámetros Globales -->
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
            <?php echo ($is_edit) ? 'Modificar Detalle de Control' : 'Nuevo Control de Diplomado'; ?>
        </h3>

        <form id="formDiplomadoControl" action="<?php echo BASE_URL; ?>diplomadocontrol/<?php echo ($is_edit) ? 'edit/' . $diplomadoAbierto['id'] : 'create'; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Selección de Diplomado Abierto -->
                <div class="md:col-span-2">
                    <label for="diplomado_abierto_autocomplete" class="block text-gray-700 text-sm font-bold mb-2">Diplomado Abierto (Oferta):</label>
                    <?php if ($is_edit): ?>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md font-semibold text-gray-800">
                            <?php echo htmlspecialchars($diplomadoAbierto['diplomado_nombre'] . " [" . $diplomadoAbierto['numero'] . "]"); ?>
                        </div>
                        <input type="hidden" name="diplomado_abierto_id" id="diplomado_abierto_id" value="<?php echo $diplomadoAbierto['id']; ?>">
                    <?php else: ?>
                        <input type="text" id="diplomado_abierto_autocomplete" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Buscar oferta de diplomado..." required>
                        <input type="hidden" name="diplomado_abierto_id" id="diplomado_abierto_id" value="">
                    <?php endif; ?>
                </div>

                <!-- Parámetros aplicables por defecto para rellenar rápido la tabla inferior -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Asignación Masiva (Opcional):</label>
                    <div class="flex space-x-2">
                        <input type="text" id="bulk_docente_autocomplete" class="w-1/2 px-3 py-2 border border-gray-200 rounded-md text-sm" placeholder="Elegir Instructor...">
                        <input type="hidden" id="bulk_docente">
                        <input type="number" id="bulk_mensualidad" placeholder="Mensualidad" class="w-1/3 px-3 py-2 border border-gray-200 rounded-md text-sm" min="0">
                        <button type="button" id="btnApplyBulk" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md border transition">
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN DE COSTO E INICIAL -->
            <div id="infoCostoInicial" class="<?php echo $is_edit ? '' : 'hidden'; ?> mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Costo del Diplomado</span>
                        <p class="text-lg font-bold text-gray-800">
                            <span class="costo-value">$<?php echo $is_edit ? number_format((float)$diplomadoAbierto['costo'], 2) : '0.00'; ?></span>
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Inicial</span>
                        <p class="text-lg font-bold text-gray-800">
                            <span class="inicial-value">$<?php echo $is_edit ? number_format((float)$diplomadoAbierto['inicial'], 2) : '0.00'; ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <?php if ($is_edit && empty($controles)): ?>
            <script>
            $(document).ready(function () {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin cap\u00edtulos',
                    text: 'Este diplomado no tiene cap\u00edtulos cargados.',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ir a cargar cap\u00edtulos',
                    cancelButtonText: 'Cerrar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('<?php echo BASE_URL; ?>capitulo/create', '_blank');
                    }
                });
            });
            </script>
            <?php endif; ?>

            <!-- TARJETA DE CAPÍTULOS Y DETALLES -->
            <div id="seccionTablaCapitulos" class="mt-8 border-t pt-6">
                <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Distribución de Capítulos, Instructores y Tarifas
                </h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-150" id="tablaCapitulosControl">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Capítulo</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Ejecución</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Instructor Asignado</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mensualidad</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCapitulos">
                            <?php if ($is_edit && isset($controles)): ?>
                                <?php foreach ($controles as $ctrl): ?>
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm font-semibold text-gray-800">
                                            Capítulo <?php echo htmlspecialchars($ctrl['capitulo_numero']); ?>: 
                                            <span class="text-xs font-normal text-gray-500 block"><?php echo htmlspecialchars($ctrl['capitulo_nombre']); ?></span>
                                        </td>
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                            <input type="text" name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][fecha]" value="<?php echo $ctrl['fecha']; ?>" class="fecha-input px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                        </td>
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                            <select name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][docente_id]" class="docente-select px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none w-full">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($docentes as $doc): ?>
                                                    <option value="<?php echo $doc['id']; ?>" <?php echo ($ctrl['docente_id'] == $doc['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($doc['primer_apellido'] . ", " . $doc['primer_nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                            <div class="relative rounded-md shadow-sm max-w-[120px]">
                                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-xs">$</span>
                                                </div>
                                                <input type="number" step="0.01" name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][mensualidad]" value="<?php echo $ctrl['mensualidad']; ?>" class="mensualidad-input pl-5 pr-2 py-1.5 w-full border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" min="0">
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="rowPlaceholder">
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                        Por favor, seleccione una oferta de diplomado abierto arriba para desplegar sus capítulos asociados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Botoneras de salida -->
            <div class="flex items-center justify-between border-t pt-6 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-150">
                    <?php echo ($is_edit) ? 'Actualizar Control' : 'Generar Control de Diplomado'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>diplomadocontrol" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>const docentesList = <?php echo json_encode($docentes); ?>;</script>
<?php $page_js = 'asset/js/DiplomadoControl/diplomadocontrol.js'; ?>