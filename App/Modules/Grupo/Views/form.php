<?php
// app/Modules/Grupo/Views/form.php
$is_edit = isset($grupo_data['grupo_id']) && !empty($grupo_data['grupo_id']);
?>

<div class="bg-white p-8 rounded-lg shadow-md w-full mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Grupo y Permisos' : 'Crear Nuevo Grupo'; ?></h3>
    
    <form id="form_grupo_page">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <input type="hidden" name="grupo_id" id="grupo_id" value="<?php echo $grupo_data['grupo_id'] ?? ''; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div>
                <label for="nombre_grupo" class="block text-gray-700 text-sm font-bold mb-2">Nombre del Grupo:</label>
                <input type="text" id="nombre_grupo" name="nombre_grupo" value="<?php echo htmlspecialchars($grupo_data['nombre_grupo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="descripcion_grupo" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                <input type="text" id="descripcion_grupo" name="descripcion_grupo" value="<?php echo htmlspecialchars($grupo_data['descripcion_grupo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fa fa-save mr-2"></i> <?php echo $is_edit ? 'Actualizar Grupo' : 'Guardar y Continuar'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>grupo" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>

    <div id="permissions-section" class="<?php echo $is_edit ? '' : 'hidden'; ?> mt-8">
        <hr class="my-8">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-xl font-bold text-gray-800">Gestión de Permisos por Ventana</h4>
            <button id="btn-save-permissions" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fa fa-check-circle mr-2"></i> Guardar Permisos
            </button>
        </div>

        <div id="permissions-container" class="overflow-x-auto">
            <div id="loading-permissions" class="hidden py-10 text-center text-gray-500">
                <i class="fa fa-spinner fa-spin mr-2"></i> Cargando...
            </div>
            <div id="permissions-grid">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <input type="checkbox" id="check-all-global" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" title="Marcar/Desmarcar Todo">
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ventana / Módulo</th>
                            <th class="px-2 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Crear</th>
                            <th class="px-2 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Modificar</th>
                            <th class="px-2 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Eliminar</th>
                            <th class="px-2 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Listar</th>
                        </tr>
                    </thead>
                    <tbody id="permissions-body">
                        <!-- JS content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>asset/js/Grupo/grupo.js"></script>
