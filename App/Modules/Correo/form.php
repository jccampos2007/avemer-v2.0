<?php
// app/Modules/Cuota/Views/form.php

// Se espera la variable $correo_data (vacía para crear, con datos para editar)
$is_edit = isset($correo_data['id']) && !empty($correo_data['id']);
$page_title = 'Envio de Correos';

// Datos para pre-llenar los campos
$oferta_academica_id_val = $correo_data['oferta_academica_id'] ?? '';
$tipo_oferta_academica_id_val = $correo_data['tipo_oferta_academica_id'] ?? '1'; // Valor por defecto: Curso
$buscar_mensajes_val = $correo_data['buscar_mensajes_id'] ?? '';
// 'fecha' ya no se necesita aquí, se gestiona automáticamente en la DB
?>
<!-- DataTables CSS y JS para la tabla de cuotas -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h3>
    <form id="formCuota"  method="POST"
        data-oferta-academica-id="<?php echo $oferta_academica_id_val; ?>"
        data-buscar-mensajes="<?php echo $buscar_mensajes_val; ?>"
        data-tipo-oferta-academica-id="<?php echo $tipo_oferta_academica_id_val; ?>">

        <!-- Pestañas para tipo_oferta_academica_id -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Tipos:</label>
            <div class="flex border-b border-gray-200">
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="1">Curso</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="2">Diplomado</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="3">Evento</button>
                <button type="button" class="tab-button px-4 py-2 text-sm font-medium focus:outline-none" data-tab-id="4">Maestría</button>
            </div>
            <input type="hidden" id="tipo_oferta_academica_id" name="tipo_oferta_academica_id" value="<?php echo $tipo_oferta_academica_id_val; ?>">
        </div>

        <div class="mb-4">
            <label for="oferta_academica_id" class="block text-gray-700 text-sm font-bold mb-2"></label>
            <select id="oferta_academica_id" name="oferta_academica_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione</option>
                <!-- Opciones se llenarán dinámicamente con JS -->
            </select>
        </div>
 
        <div class="mb-4">
            <label for="buscar_mensajes" class="block text-gray-700 text-sm font-bold mb-2">Mensaje</label>
            <select id="buscar_mensajes_id" name="buscar_mensajes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Seleccione</option>
                <!-- Opciones se llenarán dinámicamente con JS -->
            </select>
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar Cuota' : 'Guardar Cuota'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>correo/create" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
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
<?php $page_js = 'js/modules/correo.js'; ?>