<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Control de Asistencia</h3>

    <form id="formAsistencia" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <input type="hidden" id="master_id" name="master_id">
        <input type="hidden" id="clase_id" name="clase_id" value="0">

        <!-- SECCIÓN 1: TIPO DE OFERTA + APERTURA -->
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
                <input type="hidden" id="tipo_oferta_academica_id" name="tipo_oferta_academica_id" value="1">
            </div>

            <div class="mb-4">
                <label for="oferta_academica_nombre" class="block text-gray-700 text-sm font-bold mb-2">Oferta Académica (Apertura):</label>
                <input type="text" id="oferta_academica_nombre" placeholder="Busque una oferta académica" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" autocomplete="off">
                <input type="hidden" id="oferta_academica_id" name="oferta_academica_id">
            </div>

            <div id="oferta-label-box" class="hidden p-3 bg-white rounded border border-blue-200">
                <span class="block text-xs font-bold text-gray-500 uppercase">Oferta seleccionada</span>
                <span id="info-oferta-label" class="text-sm text-gray-600">—</span>
            </div>
        </div>

        <!-- SECCIÓN 2: CLASES DE LA OFERTA -->
        <div id="clases-section" class="hidden bg-yellow-50 rounded-lg p-4 mb-6 border border-yellow-200">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-bold text-gray-700">Clases</h4>
            </div>
            <div id="clases-list" class="space-y-2"></div>
        </div>

        <!-- SECCIÓN 3: ESTUDIANTES -->
        <div id="students-section" class="hidden bg-green-50 rounded-lg p-4 mb-6 border border-green-200">
            <h4 class="text-lg font-bold text-gray-700 mb-2" id="clase-title">Estudiantes Inscritos</h4>
            <p class="text-sm text-gray-500 mb-4">Marque los estudiantes que asistieron.</p>

            <div id="students-message" class="mb-4 text-sm hidden"></div>

            <div id="students-table-wrapper" class="overflow-x-auto mb-4">
                <table id="studentsTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                            <th class="py-3 px-6 text-left w-10">
                                <input type="checkbox" id="selectAllStudents" class="rounded">
                            </th>
                            <th class="py-3 px-6 text-left">Nombre Completo</th>
                            <th class="py-3 px-6 text-left">Cédula</th>
                            <th class="py-3 px-6 text-center">Asistió</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-light"></tbody>
                </table>
            </div>

            <div class="mb-4">
                <label for="observacion" class="block text-gray-700 text-sm font-bold mb-2">Observación:</label>
                <textarea id="observacion" name="observacion" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="255" placeholder="Opcional"></textarea>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" id="btnSaveAsistencia" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-save mr-1"></i> Guardar Asistencia
                </button>
                <span id="save-message" class="text-sm hidden"></span>
            </div>
        </div>
    </form>
</div>

<?php $page_js = 'asset/js/Asistencia/asistencia.js'; ?>
