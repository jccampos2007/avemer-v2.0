<?php
$is_edit = isset($ciudad['id']) && !empty($ciudad['id']);
?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo ($is_edit) ? 'Editar Ciudad' : 'Crear Nueva Ciudad'; ?></h3>
    <form id="formCiudad" action="<?php echo BASE_URL; ?>ciudad/<?php echo ($is_edit) ? 'update/' . $ciudad['id'] : 'store'; ?>" method="POST">
        
        <div class="mb-4">
            <label for="nombre" class="label-form">Nombre de la Ciudad / Estado:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($ciudad['nombre'] ?? ''); ?>" class="input-form focus:outline-none focus:shadow-outline" required maxlength="32">
        </div>

        <div class="mb-6">
            <label for="pais_id" class="label-form">País:</label>
            <select id="pais_id" name="pais_id" class="input-form focus:outline-none focus:shadow-outline" required>
                <?php foreach ($paises ?? [] as $pais): ?>
                    <option value="<?php echo htmlspecialchars($pais['id']); ?>" <?php echo (isset($ciudad['pais_id']) && $ciudad['pais_id'] == $pais['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pais['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo ($is_edit) ? 'Actualizar' : 'Guardar'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>ciudad" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>
