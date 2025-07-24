<?php
// php_mvc_app/app/Modules/Users/Views/list.php
// Se espera la variable $users
?>
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Usuarios</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>users/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Usuario
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cédula</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Apellido</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($user['usuario_id']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($user['usuario_cedula']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($user['usuario_nombre']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($user['usuario_apellido']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($user['usuario_user']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <?php echo ($user['tipo_usuario'] == TIPO_USUARIO_ADMIN) ? 'Administrador' : 'Alumno'; ?>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <a href="<?php echo BASE_URL; ?>users/edit/<?php echo $user['usuario_id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                        <a href="<?php echo BASE_URL; ?>users/delete/<?php echo $user['usuario_id']; ?>" class="text-red-600 hover:text-red-900 delete-btn" data-id="<?php echo $user['usuario_id']; ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<script src="<?php echo BASE_URL; ?>../app/Modules/Users/users.js"></script>