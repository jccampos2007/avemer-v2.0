<?php
// app/Modules/PreinscripcionDiplomado/Views/list.php

// Este módulo no tiene una vista de lista tradicional, redirige al formulario de creación.
// Puedes añadir un mensaje o un botón si lo deseas.
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-inscripción de Diplomado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-4 font-sans antialiased flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Redirigiendo a la Pre-inscripción...</h1>
        <p class="text-gray-600">Si no es redirigido automáticamente, haga clic en el botón.</p>
        <a href="<?php echo BASE_URL; ?>preinscripcion_diplomado/create" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Ir al Formulario de Pre-inscripción
        </a>
    </div>
    <script>
        // Redirigir automáticamente después de un breve retraso
        setTimeout(function() {
            window.location.href = "<?php echo BASE_URL; ?>preinscripcion_diplomado/create";
        }, 1000); // 1 segundo
    </script>
</body>

</html>