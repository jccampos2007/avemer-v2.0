<?php
// app/Modules/Cuota/Views/form.php

// Se espera la variable $cuota_data (vacía para crear, con datos para editar)
$is_edit = isset($cuota_data['id']) && !empty($cuota_data['id']);
$form_action = $is_edit ? BASE_URL . 'cuota/edit/' . htmlspecialchars($cuota_data['id']) : BASE_URL . 'cuota/create';
$page_title = $is_edit ? 'Editar Cuota' : 'Crear Nueva Cuota';

// Datos para pre-llenar los campos
$nombre_val = htmlspecialchars($cuota_data['nombre'] ?? '');
$monto_val = htmlspecialchars($cuota_data['monto'] ?? '');
$oferta_academica_id_val = $cuota_data['oferta_academica_id'] ?? '';
$tipo_oferta_academica_id_val = $cuota_data['tipo_oferta_academica_id'] ?? '1'; // Valor por defecto: Curso
$generado_val = $cuota_data['generado'] ?? '0';
$fecha_vencimiento_val = htmlspecialchars($cuota_data['fecha_vencimiento'] ?? '');
// 'fecha' ya no se necesita aquí, se gestiona automáticamente en la DB
?>
<!-- DataTables CSS y JS para la tabla de cuotas -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formCuota" action="<?php echo $form_action; ?>" method="POST"
        data-oferta-academica-id="<?php echo $oferta_academica_id_val; ?>"
        data-tipo-oferta-academica-id="<?php echo $tipo_oferta_academica_id_val; ?>">

        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $cuota_data['id']; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre de la Cuota:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="32">
            </div>

            <div>
                <label for="monto" class="block text-gray-700 text-sm font-bold mb-2">Monto:</label>
                <input type="number" step="0.01" id="monto" name="monto" value="<?php echo $monto_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required min="0">
            </div>
        </div>

        <!-- Pestañas para tipo_oferta_academica_id -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Oferta Académica:</label>
            <div class="flex border-b border-gray-200">
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="1">Curso</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="2">Diplomado</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="3">Evento</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="4">Maestría</button>
            </div>
            <input type="hidden" id="tipo_oferta_academica_id" name="tipo_oferta_academica_id" value="<?php echo $tipo_oferta_academica_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="oferta_academica_id" class="block text-gray-700 text-sm font-bold mb-2">Oferta Académica:</label>
            <select id="oferta_academica_id" name="oferta_academica_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione una Oferta Académica</option>
                <!-- Opciones se llenarán dinámicamente con JS -->
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="generado" class="block text-gray-700 text-sm font-bold mb-2">Generado:</label>
                <select id="generado" name="generado" disabled class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="0" <?php echo ($generado_val == '0') ? 'selected' : ''; ?>>No</option>
                    <option value="1" <?php echo ($generado_val == '1') ? 'selected' : ''; ?>>Sí</option>
                </select>
            </div>
            <div>
                <label for="fecha_vencimiento" class="block text-gray-700 text-sm font-bold mb-2">Fecha de Vencimiento:</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo $fecha_vencimiento_val; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Cuota' : 'Guardar Cuota'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>cuota" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>

    <div id="cuotas-list-container" class="mt-8 pt-6 border-t border-gray-200">
        <h4 class="text-xl font-bold text-gray-700 mb-4">Cuotas Generadas para esta Oferta</h4>
        <div id="cuotas-list-message" class="mb-4 text-sm text-gray-600 hidden"></div>
        <div id="cuotas-list-table-wrapper" class="overflow-x-auto">
            <table id="cuotasListTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Nombre Cuota</th>
                        <th class="py-3 px-6 text-left">Monto</th>
                        <th class="py-3 px-6 text-left">Generado</th>
                        <th class="py-3 px-6 text-left">F. Vencimiento</th>
                        <th class="py-3 px-6 text-left">F. Creación</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm font-light">
                    <!-- Los datos se cargarán aquí por JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Generar Deuda -->
<div id="generateDebtModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-2xl font-bold text-gray-800">Generar Deuda para Alumnos</h4>
            <button id="closeDebtModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <div id="students-list-message" class="mb-4 text-sm text-gray-600 hidden"></div>
        <div class="overflow-y-auto max-h-80 mb-6 border border-gray-200 rounded-lg p-2">
            <table id="studentsListTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                        <th class="py-3 px-6 text-left">
                            <input type="checkbox" id="selectAllStudents" class="form-checkbox h-4 w-4 text-blue-600 rounded">
                        </th>
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Nombre Alumno</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm font-light">
                    <!-- Los alumnos se cargarán aquí -->
                </tbody>
            </table>
        </div>
        <div class="flex justify-end">
            <button id="confirmGenerateDebtBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Generar Deuda Seleccionados
            </button>
        </div>
    </div>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = '../app/Modules/Cuota/cuota.js'; ?>