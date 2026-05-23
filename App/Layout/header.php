<?php
// php_mvc_app/app/Layout/header.php
$randomValue = rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Académica</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>image/favico.png" type="image/png">
    <!-- Tailwind CSS CDN -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/output.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/base.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpinejs (Movido al head para asegurar la carga inmediata del estado del menú responsivo) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            font-family: "Inter", sans-serif;
        }
    </style>
    <!-- jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="<?php echo BASE_URL . 'js/main.js?' . $randomValue; ?>"></script>
</head>

<body class="h-screen bg-gray-100 overflow-hidden <?php echo ($isLogin ?? false) ? 'flex items-center justify-center' : ''; ?>">
<?php if (!($isLogin ?? false)): ?>
    <!-- Contenedor general del Layout con Alpine.js para controlar la barra lateral -->
    <div x-data="{ sidebarOpen: false }" class="flex h-screen w-full overflow-hidden bg-gray-100">
<?php endif; ?>