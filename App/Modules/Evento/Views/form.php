<?php
// app/Modules/Evento/Views/form.php

// Se espera la variable $evento_data (vacía para crear, con datos para editar)
$is_edit = isset($evento_data['id']) && !empty($evento_data['id']);
$form_action = $is_edit ? BASE_URL . 'evento/edit/' . $evento_data['id'] : BASE_URL . 'evento/create';

// Datos para pre-llenar los selects y campos
$duracion_id_val = $evento_data['duracion_id'] ?? '';
$nombre_val = htmlspecialchars($evento_data['nombre'] ?? '');
$descripcion_val = htmlspecialchars($evento_data['descripcion'] ?? '');
$siglas_val = htmlspecialchars($evento_data['siglas'] ?? '');
$costo_val = htmlspecialchars($evento_data['costo'] ?? '0.00');
$inicial_val = htmlspecialchars($evento_data['inicial'] ?? '0.00');
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- TARJETA 1: Formulario Principal de Registro/Edición -->
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
            <?php echo ($is_edit) ? 'Editar Evento' : 'Crear Nuevo Evento'; ?>
        </h3>
        
        <form id="formEvento" action="<?php echo $form_action; ?>" method="POST" data-duracion-id="<?php echo $duracion_id_val; ?>">

            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $evento_data['id']; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="duracion_id" class="block text-gray-700 text-sm font-bold mb-2">Duración:</label>
                    <select id="duracion_id" name="duracion_id" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Seleccione una Duración</option>
                        <!-- Opciones se llenarán con JS -->
                    </select>
                    <!-- Campo oculto para pasar el valor actual al JS para pre-selección -->
                    <input type="hidden" name="duracion_current" id="duracion_current" value="<?php echo $duracion_id_val; ?>">
                </div>

                <div>
                    <label for="siglas" class="block text-gray-700 text-sm font-bold mb-2">Siglas:</label>
                    <input type="text" id="siglas" name="siglas" value="<?php echo $siglas_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required maxlength="8">
                </div>
            </div>

            <div class="mb-6">
                <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required maxlength="64">
            </div>

            <div class="mb-6">
                <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="4" required><?php echo $descripcion_val; ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label for="costo" class="block text-gray-700 text-sm font-bold mb-2">Costo:</label>
                    <input type="number" step="0.01" id="costo" name="costo" value="<?php echo $costo_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required min="0">
                </div>
                <div>
                    <label for="inicial" class="block text-gray-700 text-sm font-bold mb-2">Inicial:</label>
                    <input type="number" step="0.01" id="inicial" name="inicial" value="<?php echo $inicial_val; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required min="0">
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    <?php echo ($is_edit) ? 'Actualizar Evento' : 'Guardar Evento'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>evento" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- TARJETA 2: Eventos Aperturados (Solo visible en modo Edición) -->
    <?php if ($is_edit): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Eventos Aperturados</h4>
            
            <?php if (isset($eventos_abiertos) && !empty($eventos_abiertos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número </th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sede</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fechas</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Inscritos</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus Oferta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventos_abiertos as $abierto): ?>
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
                    Este evento no tiene ofertas ni convocatorias (Eventos Aperturados) registradas.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/evento.js?1'; ?>