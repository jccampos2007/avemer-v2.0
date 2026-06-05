<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 md:p-10 rounded-lg shadow-md w-full max-w-lg mx-4">
        <div class="flex justify-center mb-4">
            <img src="<?php echo BASE_URL; ?>image/logo-grupo-avemer.webp" alt="Grupo Avemer" class="h-16">
        </div>
        <h2 class="text-2xl font-bold text-center mb-2 text-gray-800">Restablecer Contraseña</h2>
        <p class="text-center text-gray-500 text-sm mb-6">Ingresa tu nueva contraseña.</p>
        <form action="<?php echo BASE_URL; ?>reset-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Nueva Contraseña:</label>
                <input type="password" id="password" name="password" class="input-form focus:outline-none focus:shadow-outline" required minlength="6" autofocus>
            </div>
            <div class="mb-6">
                <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirmar Contraseña:</label>
                <input type="password" id="password_confirm" name="password_confirm" class="input-form focus:outline-none focus:shadow-outline" required minlength="6">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>