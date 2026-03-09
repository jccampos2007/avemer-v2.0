// php_mvc_app/app/Modules/Diplomados/Views/js/diplomados.js
// Archivo JavaScript para el módulo de diplomados
$(document).ready(function () {
    console.log("diplomados.js cargado.");

    // Confirmación de eliminación con jQuery
    $('.delete-btn').on('click', function (e) {
        e.preventDefault(); // Prevenir el comportamiento por defecto del enlace
        var diplomadoId = $(this).data('id');
        var confirmDelete = confirm('¿Estás seguro de que quieres eliminar este diplomado?');

        if (confirmDelete) {
            // Si el usuario confirma, redirigir al enlace de eliminación
            window.location.href = $(this).attr('href');
        }
    });
});
