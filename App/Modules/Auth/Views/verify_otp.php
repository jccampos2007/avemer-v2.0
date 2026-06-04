<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 md:p-10 rounded-lg shadow-md w-full max-w-lg mx-4">
        <div class="flex justify-center mb-4">
            <img src="<?php echo BASE_URL; ?>image/logo-grupo-avemer.webp" alt="Grupo Avemer" class="h-16">
        </div>
        <h2 class="text-2xl font-bold text-center mb-2 text-gray-800">Verificar Código</h2>
        <p class="text-center text-gray-500 text-sm mb-6">Ingresa el código de 6 dígitos que enviamos a tu correo.</p>
        <form action="<?php echo BASE_URL; ?>verify-otp" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">
            <div class="mb-4">
                <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">Código de verificación:</label>
                <input type="text" id="otp" name="otp" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code"
                    class="shadow appearance-none border rounded w-full py-3 px-3 text-gray-700 text-center text-2xl tracking-widest leading-tight focus:outline-none focus:shadow-outline" required autofocus>
            </div>
            <div class="flex items-center justify-between mb-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Verificar Código
                </button>
            </div>
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>forgot-password" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Solicitar un nuevo código</a>
            </div>
        </form>
    </div>
</div>