$(document).ready(function () {
    console.log("users.js cargado.");

    // Confirmación de eliminación con jQuery
    $('.delete-btn').on('click', function (e) {
        e.preventDefault(); // Prevenir el comportamiento por defecto del enlace
        var userId = $(this).data('id');
        var confirmDelete = confirm('¿Estás seguro de que quieres eliminar este usuario?');

        if (confirmDelete) {
            // Si el usuario confirma, redirigir al enlace de eliminación
            window.location.href = $(this).attr('href');
        }
    });
});