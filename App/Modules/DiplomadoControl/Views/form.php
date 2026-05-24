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
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Selección de Diplomado Abierto -->
                <div>
                    <label for="diplomado_abierto_id" class="block text-gray-700 text-sm font-bold mb-2">Diplomado Abierto (Oferta):</label>
                    <?php if ($is_edit): ?>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md font-semibold text-gray-800">
                            <?php echo htmlspecialchars($diplomadoAbierto['diplomado_nombre'] . " [" . $diplomadoAbierto['numero'] . "]"); ?>
                        </div>
                        <input type="hidden" name="diplomado_abierto_id" id="diplomado_abierto_id" value="<?php echo $diplomadoAbierto['id']; ?>">
                    <?php else: ?>
                        <select id="diplomado_abierto_id" name="diplomado_abierto_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">Seleccione una oferta abierta...</option>
                            <?php foreach ($diplomadosAbiertos as $dip): ?>
                                <option value="<?php echo $dip['id']; ?>">
                                    <?php echo htmlspecialchars($dip['diplomado_nombre'] . " [" . $dip['numero'] . "]"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Parámetros aplicables por defecto para rellenar rápido la tabla inferior -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Asignación Masiva (Opcional):</label>
                    <div class="flex space-x-2">
                        <select id="bulk_docente" class="w-1/2 px-3 py-2 border border-gray-200 rounded-md text-sm">
                            <option value="">Elegir Instructor...</option>
                            <?php foreach ($docentes as $doc): ?>
                                <option value="<?php echo $doc['id']; ?>">
                                    <?php echo htmlspecialchars($doc['primer_apellido'] . ", " . $doc['primer_nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="bulk_mensualidad" placeholder="Mensualidad" class="w-1/3 px-3 py-2 border border-gray-200 rounded-md text-sm" min="0">
                        <button type="button" id="btnApplyBulk" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md border transition">
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>

            <!-- TARJETA DE CAPÍTULOS Y DETALLES (Se genera dinámicamente o se renderiza si es edición) -->
            <div class="mt-8 border-t pt-6">
                <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Distribución de Capítulos, Docentes y Tarifas
                </h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-150" id="tablaCapitulosControl">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Capítulo</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Ejecución</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Instructor Asignado</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mensualidad</th>
                                <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus</th>
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
                                            <input type="date" name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][fecha]" value="<?php echo $ctrl['fecha']; ?>" class="px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                                        </td>
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                            <select name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][docente_id]" class="docente-select px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none w-full" required>
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
                                                <input type="number" step="0.01" name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][mensualidad]" value="<?php echo $ctrl['mensualidad']; ?>" class="mensualidad-input pl-5 pr-2 py-1.5 w-full border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required min="0">
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                            <select name="capitulos[<?php echo $ctrl['capitulo_id']; ?>][generado]" class="px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                                                <option value="1" <?php echo ($ctrl['generado'] == 1) ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="2" <?php echo ($ctrl['generado'] == 2) ? 'selected' : ''; ?>>Generado</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="rowPlaceholder">
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-500">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const diplomadoSelect = document.getElementById('diplomado_abierto_id');
    const tbodyCapitulos = document.getElementById('tbodyCapitulos');
    const rowPlaceholder = document.getElementById('rowPlaceholder');
    const bulkDocente = document.getElementById('bulk_docente');
    const bulkMensualidad = document.getElementById('bulk_mensualidad');
    const btnApplyBulk = document.getElementById('btnApplyBulk');

    // Lista de docentes cargada dinámicamente desde PHP para usar en JS
    const docentesList = <?php echo json_encode($docentes); ?>;

    if (diplomadoSelect) {
        diplomadoSelect.addEventListener('change', function() {
            const id = this.value;
            if (!id) {
                tbodyCapitulos.innerHTML = `
                    <tr id="rowPlaceholder">
                        <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                            Por favor, seleccione una oferta de diplomado abierto arriba para desplegar sus capítulos asociados.
                        </td>
                    </tr>
                `;
                return;
            }

            // Llamada AJAX para obtener capítulos
            fetch(`<?php echo BASE_URL; ?>diplomadocontrol/getCapitulosAjax?diplomado_abierto_id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(capitulos => {
                if (capitulos.length === 0) {
                    tbodyCapitulos.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-amber-600 font-semibold">
                                Este diplomado no posee capítulos asociados o cargados en la base de datos base.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbodyCapitulos.innerHTML = '';

                // Obtener fecha por defecto (hoy) solo si no viene del servidor
                const today = new Date().toISOString().split('T')[0];

                capitulos.forEach(cap => {
                    let docenteOptions = '<option value="">Seleccione...</option>';
                    docentesList.forEach(doc => {
                        docenteOptions += `<option value="${doc.id}">${doc.primer_apellido}, ${doc.primer_nombre}</option>`;
                    });

                    // Si el servidor devolvió docente_id (de controles existentes), preseleccionarlo
                    var selectedDocente = cap.docente_id || '';
                    if (selectedDocente) {
                        docenteOptions = docenteOptions.replace(
                            `value="${selectedDocente}"`,
                            `value="${selectedDocente}" selected`
                        );
                    }

                    var fechaVal = cap.fecha || today;
                    var mensualidadVal = cap.mensualidad !== undefined ? parseFloat(cap.mensualidad).toFixed(2) : '0.00';
                    var generadoVal = cap.generado || 1;

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50/50 transition';
                    tr.innerHTML = `
                        <td class="px-5 py-4 border-b border-gray-200 text-sm font-semibold text-gray-800">
                            Capítulo ${cap.numero}: 
                            <span class="text-xs font-normal text-gray-500 block">${cap.nombre}</span>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <input type="date" name="capitulos[${cap.id}][fecha]" value="${fechaVal}" class="px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <select name="capitulos[${cap.id}][docente_id]" class="docente-select px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none w-full" required>
                                ${docenteOptions}
                            </select>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <div class="relative rounded-md shadow-sm max-w-[120px]">
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-xs">$</span>
                                </div>
                                <input type="number" step="0.01" name="capitulos[${cap.id}][mensualidad]" value="${mensualidadVal}" class="mensualidad-input pl-5 pr-2 py-1.5 w-full border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required min="0">
                            </div>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <select name="capitulos[${cap.id}][generado]" class="px-2.5 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                                <option value="1" ${generadoVal == 1 ? 'selected' : ''}>Pendiente</option>
                                <option value="2" ${generadoVal == 2 ? 'selected' : ''}>Generado</option>
                            </select>
                        </td>
                    `;
                    tbodyCapitulos.appendChild(tr);
                });
            });
        });
    }

    // Lógica para aplicar asignación masiva/rápida en los inputs inferiores
    btnApplyBulk.addEventListener('click', function() {
        const selectedDocente = bulkDocente.value;
        const valMensualidad = bulkMensualidad.value;

        if (selectedDocente) {
            const selectDocentes = document.querySelectorAll('.docente-select');
            selectDocentes.forEach(select => {
                select.value = selectedDocente;
            });
        }

        if (valMensualidad !== '') {
            const inputsMensualidad = document.querySelectorAll('.mensualidad-input');
            inputsMensualidad.forEach(input => {
                input.value = parseFloat(valMensualidad).toFixed(2);
            });
        }
    });
});
</script>