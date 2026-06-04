<?php
$greeting = $greeting_user ?? null;
?>
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-10 rounded-lg shadow-md w-full max-w-lg">
        <div class="flex justify-center mb-4">
            <img src="<?php echo BASE_URL; ?>image/logo-grupo-avemer.webp" alt="Grupo Avemer" class="h-16">
        </div>
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Iniciar Sesión</h2>
        <form action="<?php echo BASE_URL; ?>login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">

            <?php if ($greeting): ?>
            <div class="mb-4 text-center">
                <p class="text-gray-700 text-lg font-semibold">Hola, <?php echo htmlspecialchars($greeting['name']); ?></p>
            </div>
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($greeting['username']); ?>">
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña:</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
            </div>
            <div class="flex items-center mb-4">
                <input type="checkbox" id="remember_user" name="remember_user" class="mr-2 rounded border-gray-300" checked>
                <label for="remember_user" class="text-sm text-gray-600">Recordar usuario</label>
            </div>
            <?php else: ?>
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Usuario:</label>
                <input type="text" id="username" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña:</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center mb-4">
                <input type="checkbox" id="remember_user" name="remember_user" class="mr-2 rounded border-gray-300">
                <label for="remember_user" class="text-sm text-gray-600">Recordar usuario</label>
            </div>
            <?php endif; ?>

            <div class="flex items-center justify-between mb-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Ingresar
                </button>
            </div>

            <?php if ($greeting): ?>
            <div class="text-center mb-2">
                <a href="<?php echo BASE_URL; ?>login?forget-user=1" class="text-sm text-gray-500 hover:text-gray-700">¿No eres <?php echo htmlspecialchars($greeting['name']); ?>?</a>
            </div>
            <?php endif; ?>

            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>forgot-password" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</div>