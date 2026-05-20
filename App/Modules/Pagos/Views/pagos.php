<?php
/**
 * Vista temporal para el módulo de Pagos (Coming Soon)
 * Ubicación sugerida: app/Modules/Pagos/Views/pago.php
 */
?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto min-h-[60vh] flex flex-col items-center justify-center border border-gray-100">

    <!-- Icono de Próximamente (Reloj de arena) -->
    <div class="mb-6 flex justify-center items-center" style="width: 96px; height: 96px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 22h14"></path>
            <path d="M5 2h14"></path>
            <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
            <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
        </svg>
    </div>

    <h2 class="text-4xl font-bold text-gray-800 mb-2 text-center">Módulo de Pagos</h2>
    <p class="text-xl text-gray-500 mb-10 text-center max-w-md">
        Estamos trabajando para ofrecerte la mejor experiencia en la gestión de tus transacciones. ¡Próximamente disponible!
    </p>

    <div class="flex space-x-4 mt-12">
        <!-- Botón con el padding exacto de la imagen: py-2 px-4 -->
        <a href="<?php echo BASE_URL; ?>dashboard" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow focus:outline-none focus:shadow-outline transition duration-200">
            Volver al Inicio
        </a>
    </div>

    <div class="mt-12 text-center border-t border-gray-100 pt-6 w-full">
        <p class="text-sm text-gray-400 italic">
            "Estamos preparando algo increíble para ti."
        </p>
    </div>
</div>