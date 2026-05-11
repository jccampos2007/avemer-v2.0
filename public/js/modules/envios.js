// app/Modules/Envios/Views/js/envios.js
// Archivo JavaScript para el módulo de envios
$(document).ready(function () {
    console.log("envios.js cargado.");

        // ---------------------------------------------------
        // Lógica para la vista de LISTADO (DataTables)
        // ---------------------------------------------------
        const tableElement = $('#enviosTable');
        
        var enviosTable = tableElement.DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ajax": {
                "url": BASE_URL_JS + "envios/data",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    alert('Error al cargar los datos de envios.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Correo
                { "data": 2 }, // Mensaje
                { "data": 3 } // respose

            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN
        // Usamos delegación de eventos porque los botones se crean dinámicamente
        tableElement.on('click', '.btn-delete', function (e) {
            e.preventDefault(); // Evita que el enlace se abra inmediatamente
            
            const urlEliminar = $(this).attr('href');
            
            // Usamos confirmación nativa. Si prefieres SweetAlert2 o un modal personalizado, 
            // solo debes cambiar esta lógica.
            if (confirm('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')) {
                window.location.href = urlEliminar;
            }
        });
    }
);