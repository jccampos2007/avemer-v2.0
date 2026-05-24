<?php
// php_mvc_app/app/Modules/DiplomadoControl/Views/list.php
?>
<div class="max-w-6xl mx-auto space-y-6">

    <!-- CABECERA Y ACCIÓN PRINCIPAL -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white p-6 rounded-lg shadow-sm border border-gray-150">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Control de Diplomados</h2>
            <p class="text-sm text-gray-500 mt-1">Gestión y control de capítulos, docentes y mensualidades asignadas a las ofertas de diplomados.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="<?php echo BASE_URL; ?>diplomadocontrol/create" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Asignar Nuevo Control
            </a>
        </div>
    </div>

    <!-- TARJETA PRINCIPAL CON LA TABLA -->
    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-700">Aperturas de Diplomados</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Número de Oferta</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre del Diplomado</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado Oferta</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado Control</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (isset($diplomados) && !empty($diplomados)): ?>
                        <?php foreach ($diplomados as $dip): ?>
                            <tr class="hover:bg-gray-50/80 transition duration-100">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($dip['oferta_numero']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($dip['diplomado_nombre']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php 
                                        $statusClass = 'bg-gray-100 text-gray-700';
                                        if (strtolower($dip['estatus_oferta']) == 'activo') {
                                            $statusClass = 'bg-green-100 text-green-800';
                                        } elseif (strtolower($dip['estatus_oferta']) == 'inactivo') {
                                            $statusClass = 'bg-red-100 text-red-800';
                                        }
                                    ?>
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($dip['estatus_oferta']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($dip['total_controles'] > 0): ?>
                                        <div class="flex flex-col space-y-1">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 self-start">
                                                <svg class="w-3.5 h-3.5 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l5-5z" clip-rule="evenodd"></path></svg>
                                                Configurado (<?php echo $dip['total_controles']; ?> Capítulos)
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Generados: <?php echo $dip['controles_generados']; ?> / <?php echo $dip['total_controles']; ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            <svg class="w-3.5 h-3.5 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Sin Detalle / Pendiente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm actions-column">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="<?php echo BASE_URL; ?>diplomadocontrol/edit/<?php echo $dip['diplomado_abierto_id']; ?>" 
                                           class="btn-action btn-action-edit"
                                           title="Gestionar Detalle de Control">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 bg-gray-50/50">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-semibold">No se encontraron ofertas de diplomados registrados.</p>
                                <p class="text-xs text-gray-400 mt-1">Por favor registre una apertura de diplomado antes de configurar su control.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>