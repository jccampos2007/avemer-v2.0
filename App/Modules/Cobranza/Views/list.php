<?php
use App\Core\Auth;

$canCreate = Auth::hasPermission('cobranza', 'crear');
$canEdit = Auth::hasPermission('cobranza', 'modificar');
$canDelete = Auth::hasPermission('cobranza', 'eliminar');
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white p-6 rounded-lg shadow-sm border border-gray-150 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Cobranza</h2>
        <p class="text-sm text-gray-500 mt-1">Cuotas con saldo pendiente — las vencidas se muestran en rojo.</p>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50 mb-4">
        <h3 class="text-lg font-bold text-gray-700">Listado de Deudas Pendientes</h3>
    </div>
    <table id="cobranzaTable" class="min-w-full leading-normal display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alumno</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Oferta</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cuota</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Monto</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">F. Vencimiento</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Días Vencido</th>
                <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<?php $page_js = 'asset/js/Cobranza/cobranza.js'; ?>
