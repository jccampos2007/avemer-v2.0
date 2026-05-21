<?php
// php_mvc_app/App/Modules/Banco/list.php
?>
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Bancos</h2>

<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>banco/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Crear Nuevo Banco
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-4">
    <table id="bancoTable" class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre del Banco</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- JavaScript específico para este módulo -->
<?php $page_js = 'js/modules/banco.js'; ?>
