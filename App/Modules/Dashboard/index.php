<!-- // php_mvc_app/app/Modules/Dashboard/Views/index.php -->
<h2 class="text-3xl font-semibold text-gray-800 mb-6">Bienvenido al Dashboard</h2>
<p class="text-lg text-gray-700">Aquí puedes gestionar los usuarios, alumnos y diplomados del sistema.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Gestión de Usuarios</h3>
        <p class="text-gray-600">Crea, edita y elimina cuentas de usuario.</p>
        <a href="<?php echo BASE_URL; ?>users" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition duration-300">Ir a Usuarios</a>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Gestión de Alumnos</h3>
        <p class="text-gray-600">Administra la información de los alumnos.</p>
        <a href="<?php echo BASE_URL; ?>alumnos" class="mt-4 inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md transition duration-300">Ir a Alumnos</a>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Gestión de Diplomados</h3>
        <p class="text-gray-600">Define y organiza los diferentes diplomados.</p>
        <a href="<?php echo BASE_URL; ?>diplomados" class="mt-4 inline-block bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-md transition duration-300">Ir a Diplomados</a>
    </div>
</div>