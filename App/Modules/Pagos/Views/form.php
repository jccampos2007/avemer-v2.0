<?php
$is_edit = isset($pago_data['id']) && !empty($pago_data['id']);
$cuotas = $cuotas ?? [];
?>
<div class="bg-white p-8 rounded-lg shadow-md w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $is_edit ? 'Editar Pago' : 'Registrar Nuevo Pago'; ?></h3>

    <form id="formPago" method="POST" class="ajax-form">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="alumno_autocomplete" class="label-form">Alumno:</label>
                <input type="text" id="alumno_autocomplete" value="<?php echo $alumno_nombre_current ?? ''; ?>" class="input-form" autocomplete="off" placeholder="Busque un alumno por nombre o cédula...">
                <input type="hidden" id="alumno_id" name="alumno_id" value="<?php echo $pago_data['alumno_id'] ?? ''; ?>">
            </div>

            <div>
                <label for="cuota_id" class="label-form">Cuota:</label>
                <select id="cuota_id" name="cuota_id" class="input-form" required>
                    <option value="">Seleccione una cuota...</option>
                    <?php if ($is_edit): ?>
                        <?php foreach ($cuotas as $cuota): ?>
                            <option value="<?php echo $cuota['id']; ?>" data-saldo="<?php echo $cuota['saldo_pendiente']; ?>" <?php echo ($pago_data['cuota_id'] == $cuota['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cuota['nombre'] . ' (' . $cuota['tipo_oferta_nombre'] . ') - $' . number_format($cuota['saldo_pendiente'], 2)); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label for="monto" class="label-form">Monto ($):</label>
                <input type="number" id="monto" name="monto" value="<?php echo htmlspecialchars($pago_data['monto'] ?? ''); ?>" class="input-form" required step="0.01" min="0.01">
            </div>

            <div>
                <label for="fecha" class="label-form">Fecha de Pago:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($pago_data['fecha'] ?? date('Y-m-d')); ?>" class="input-form" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div>
                <label for="forma_pago_id" class="label-form">Forma de Pago:</label>
                <select id="forma_pago_id" name="forma_pago_id" class="input-form" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($formaPagos ?? [] as $fp): ?>
                        <option value="<?php echo $fp['id']; ?>" <?php echo (isset($pago_data['forma_pago_id']) && $pago_data['forma_pago_id'] == $fp['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($fp['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="banco_id" class="label-form">Banco:</label>
                <select id="banco_id" name="banco_id" class="input-form">
                    <option value="">Seleccione...</option>
                    <?php foreach ($bancos ?? [] as $banco): ?>
                        <option value="<?php echo $banco['id']; ?>" <?php echo (isset($pago_data['banco_id']) && $pago_data['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($banco['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="numero_control" class="label-form">Nro. Referencia:</label>
                <input type="text" id="numero_control" name="numero_control" value="<?php echo htmlspecialchars($pago_data['numero_control'] ?? ''); ?>" class="input-form" maxlength="32">
            </div>

            <div>
                <label for="estatus_pago_id" class="label-form">Estatus:</label>
                <select id="estatus_pago_id" name="estatus_pago_id" class="input-form">
                    <?php foreach ($estatusPagos ?? [] as $ep): ?>
                        <option value="<?php echo $ep['id']; ?>" <?php echo (isset($pago_data['estatus_pago_id']) && $pago_data['estatus_pago_id'] == $ep['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ep['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Actualizar Pago' : 'Guardar Pago'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>pago" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php $page_js = 'asset/js/Pagos/pagos.js'; ?>
