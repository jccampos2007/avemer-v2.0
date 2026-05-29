<?php
$page_title = 'Crear Nueva Cuota';
$nombre_val = '';
$monto_val = '';
$oferta_academica_id_val = $cuota_data['oferta_academica_id'] ?? '';
$tipo_oferta_academica_id_val = $cuota_data['tipo_oferta_academica_id'] ?? '1';
$fecha_vencimiento_val = '';
$diplomado_control_id_val = '';
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formCuota" action="<?php echo BASE_URL; ?>cuota/create" method="POST"
        data-oferta-academica-id="<?php echo $oferta_academica_id_val; ?>"
        data-tipo-oferta-academica-id="<?php echo $tipo_oferta_academica_id_val; ?>"
        data-diplomado-control-id="<?php echo $diplomado_control_id_val; ?>">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <input type="hidden" id="cuota_edit_id" name="cuota_edit_id" value="">

        <!-- SECCIÓN 1: TIPO DE OFERTA + APERTURA + INFO -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <h4 class="text-lg font-bold text-gray-700 mb-4">Seleccionar Oferta Académica</h4>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Oferta:</label>
                <div class="flex border-b border-gray-200">
                    <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="1">Curso / Taller</button>
                    <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="2">Diplomado</button>
                    <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="3">Evento</button>
                    <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="4">Maestría</button>
                </div>
                <input type="hidden" id="tipo_oferta_academica_id" name="tipo_oferta_academica_id" value="<?php echo $tipo_oferta_academica_id_val; ?>">
            </div>

            <div class="mb-4">
                <label for="oferta_academica_id" class="block text-gray-700 text-sm font-bold mb-2">Oferta Académica (Apertura):</label>
                <select id="oferta_academica_id" name="oferta_academica_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione una Oferta Académica</option>
                </select>
            </div>

            <div id="oferta-info-box" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-white rounded border border-blue-200">
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Costo de la Oferta</span>
                    <span id="info-costo" class="text-lg font-bold text-gray-800">$0.00</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Inicial</span>
                    <span id="info-inicial" class="text-lg font-bold text-gray-800">$0.00</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Oferta</span>
                    <span id="info-oferta-label" class="text-sm text-gray-600">—</span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: DIPLOMADO CONTROL (solo para Diplomados) -->
        <div id="diplomado-control-section" class="hidden bg-yellow-50 rounded-lg p-4 mb-6 border border-yellow-200">
            <h4 class="text-lg font-bold text-gray-700 mb-4">Control / Capítulo del Diplomado</h4>

            <div class="mb-4">
                <label for="diplomado_control_id" class="block text-gray-700 text-sm font-bold mb-2">Control / Capítulo:</label>
                <select id="diplomado_control_id" name="diplomado_control_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione un control</option>
                </select>
            </div>

            <div id="diplomado-control-warning" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-2" role="alert">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="font-bold">Este diplomado no tiene controles / capítulos cargados.</p>
                </div>
                <p class="text-sm ml-8 mb-3">Debe cargar el detalle de controles antes de crear una cuota para este diplomado.</p>
                <a id="btn-ir-controles" href="#" target="_blank" class="ml-8 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Ir a cargar controles
                </a>
            </div>

            <div id="control-info-box" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-white rounded border border-yellow-300">
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Capítulo</span>
                    <span id="info-capitulo" class="text-md font-bold text-gray-800">—</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Costo del Capítulo</span>
                    <span id="info-costo-capitulo" class="text-md font-bold text-gray-800">$0.00</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase">Fecha del Control</span>
                    <span id="info-control-fecha" class="text-md font-bold text-gray-800">—</span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: DATOS DE LA CUOTA -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-200">
            <h4 class="text-lg font-bold text-gray-700 mb-4">Datos de la Cuota</h4>

            <div class="flex gap-4 mb-4">
                <div class="w-1/2">
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre de la Cuota:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="32">
                </div>
                <div class="w-1/4">
                    <label for="monto" class="block text-gray-700 text-sm font-bold mb-2">Monto:</label>
                    <input type="number" step="0.01" id="monto" name="monto" value="<?php echo $monto_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
                </div>
                <div class="w-1/4">
                    <label for="fecha_vencimiento" class="block text-gray-700 text-sm font-bold mb-2">Fecha de Vencimiento:</label>
                    <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo $fecha_vencimiento_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <div class="flex gap-2">
                <button type="submit" id="btn-submit-cuota" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Guardar Cuota
                </button>
                <button type="button" id="cancel-edit-btn" class="hidden bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Cancelar edición
                </button>
            </div>
            <a href="<?php echo BASE_URL; ?>cuota" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>

    <!-- LISTA DE CUOTAS -->
    <div id="cuotas-list-container" class="mt-8 pt-6 border-t border-gray-200">
        <h4 class="text-xl font-bold text-gray-700 mb-4">Cuotas Creadas para esta Oferta</h4>
        <div id="cuotas-list-message" class="mb-4 text-sm text-gray-600 hidden"></div>
        <div id="cuotas-list-table-wrapper" class="overflow-x-auto">
            <table id="cuotasListTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Nombre</th>
                        <th class="py-3 px-6 text-left">Monto</th>
                        <th class="py-3 px-6 text-left">Capítulo</th>
                        <th class="py-3 px-6 text-left">F. Vencimiento</th>
                        <th class="py-3 px-6 text-left">F. Creación</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm font-light">
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $page_js = 'asset/js/Cuota/cuota.js'; ?>
