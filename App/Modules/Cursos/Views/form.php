<?php
// app/Modules/Curso/form.php
// Se espera la variable $curso_data (vacía para crear, con datos para editar)
$is_edit = isset($curso_data['id']) && !empty($curso_data['id']);
?>
<div class="w-full space-y-6">
    
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
            <?php echo ($is_edit) ? 'Editar Taller / Curso' : 'Crear Nuevo Taller / Curso'; ?>
        </h3>
        
        <form id="formCurso" action="<?php echo BASE_URL; ?>cursos/<?php echo ($is_edit) ? 'edit/' . $curso_data['id'] : 'create'; ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($curso_data['nombre'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required maxlength="128">
                </div>
                <div>
                    <label for="numero" class="block text-gray-700 text-sm font-bold mb-2">Número:</label>
                    <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($curso_data['numero'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" maxlength="16">
                </div>
                <div>
                    <label for="horas" class="block text-gray-700 text-sm font-bold mb-2">Horas:</label>
                    <input type="number" id="horas" name="horas" value="<?php echo $curso_data['horas'] ?? '0'; ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required min="0">
                </div>
                <div>
                    <label for="convenio" class="block text-gray-700 text-sm font-bold mb-2">Convenio:</label>
                    <input type="text" id="convenio" name="convenio" value="<?php echo htmlspecialchars($curso_data['convenio'] ?? ''); ?>" class="input-form w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" maxlength="16">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo ($is_edit) ? 'Actualizar Curso' : 'Guardar Curso'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>cursos" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <?php if ($is_edit): ?>
        <div class="bg-white p-8 mt-6 rounded-lg shadow-md border border-gray-100">
            <h4 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Taller / Curso Aperturados</h4>
            
            <?php if (isset($cursos_abiertos) && !empty($cursos_abiertos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal rounded-lg overflow-hidden border border-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número de Oferta</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Inscritos</th>
                                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estatus Oferta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos_abiertos as $abierto): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900"><?php echo htmlspecialchars($abierto['oferta_numero']); ?></p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                        <span class="relative inline-block px-3 py-1 font-semibold text-blue-900 leading-tight">
                                            <span aria-hidden class="absolute inset-0 bg-blue-100 opacity-60 rounded-full"></span>
                                            <span class="relative text-xs"><?php echo htmlspecialchars($abierto['total_inscritos']); ?> Alumnos</span>
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
                                            <span class="relative text-xs"><?php echo htmlspecialchars($abierto['estatus_oferta']); ?></span>
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
                    Este taller no tiene ofertas ni convocatorias (Cursos Abiertos) registradas.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Cursos/cursos.js'; ?>