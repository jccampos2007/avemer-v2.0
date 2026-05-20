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
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'Bfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-2"></i> Exportar a Excel',
                    className: 'buttons-excel',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-2"></i> Exportar a PDF',
                    className: 'buttons-pdf',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
                    },
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['25%', '45%', '30%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "envios/data",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    showFlashMessage('error', 'Error al cargar los datos de envios.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // ID
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