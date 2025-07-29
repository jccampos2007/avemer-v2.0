<?php
// php_mvc_app/app/Modules/Diplomados/Views/list.php
// Se espera la variable $diplomados
?>
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Diplomados</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>diplomados/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Diplomado
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Siglas</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Costo</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($diplomados as $diplomado): ?>
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($diplomado['id']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($diplomado['nombre']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($diplomado['siglas']); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars(number_format($diplomado['costo'], 2)); ?></td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <a href="<?php echo BASE_URL; ?>diplomados/edit/<?php echo $diplomado['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                        <a href="<?php echo BASE_URL; ?>diplomados/delete/<?php echo $diplomado['id']; ?>" class="text-red-600 hover:text-red-900 delete-btn" data-id="<?php echo $diplomado['id']; ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<script src="diplomados.js"></script>