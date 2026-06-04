<?php
// php_mvc_app/app/Modules/Users/Views/form.php
// Se espera la variable $user_data (vacía para crear, con datos para editar)
$is_edit = isset($user_data['usuario_id']) && !empty($user_data['usuario_id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?></h3>
    <form id="form_users" action="<?php echo BASE_URL; ?>users/<?php echo ($is_edit) ? 'update/' . htmlspecialchars($user_data['usuario_id']) : 'store'; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="usuario_cedula" class="block text-gray-700 text-sm font-bold mb-2">Cédula:</label>
                <input type="text" id="usuario_cedula" name="usuario_cedula" value="<?php echo htmlspecialchars($user_data['usuario_cedula'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="usuario_nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                <input type="text" id="usuario_nombre" name="usuario_nombre" value="<?php echo htmlspecialchars($user_data['usuario_nombre'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="usuario_apellido" class="block text-gray-700 text-sm font-bold mb-2">Apellido:</label>
                <input type="text" id="usuario_apellido" name="usuario_apellido" value="<?php echo htmlspecialchars($user_data['usuario_apellido'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="usuario_user" class="block text-gray-700 text-sm font-bold mb-2">Usuario (Login):</label>
                <input type="text" id="usuario_user" name="usuario_user" value="<?php echo htmlspecialchars($user_data['usuario_user'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($user_data['correo'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="lg:col-span-4 md:col-span-2">
                <label for="usuario_pws" class="block text-gray-700 text-sm font-bold mb-2">Contraseña: <?php echo ($is_edit) ? '(Dejar en blanco para no cambiar)' : ''; ?></label>
                <input type="password" id="usuario_pws" name="usuario_pws" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" <?php echo ($is_edit) ? '' : 'required'; ?>>
            </div>
            <div>
                <label for="estatus_activo_id" class="block text-gray-700 text-sm font-bold mb-2">Estatus Activo:</label>
                <select id="estatus_activo_id" name="estatus_activo_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una opción</option>
                </select>
                <input type="hidden" name="estatus_activo_current" id="estatus_activo_current" value="<?php echo $user_data['estatus_activo_id'] ?? ''; ?>">
            </div>
            <div>
                <label for="grupo_id" class="block text-gray-700 text-sm font-bold mb-2">Grupo:</label>
                <select id="grupo_id" name="grupo_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Seleccione un grupo</option>
                    <?php if (isset($grupos) && is_array($grupos)): ?>
                        <?php foreach ($grupos as $grupo): ?>
                            <option value="<?php echo htmlspecialchars($grupo['grupo_id']); ?>" <?php echo (isset($user_data['grupo_id']) && $user_data['grupo_id'] == $grupo['grupo_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Guardar Usuario
            </button>
            <a href="<?php echo BASE_URL; ?>users" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'asset/js/Users/users.js'; ?>