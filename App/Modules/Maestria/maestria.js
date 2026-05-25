// app/Modules/Maestria/Views/js/maestria.js
console.log('maestria.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const maestriaTable = $('#maestriaTable');
    if (maestriaTable.length) {
        maestriaTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Maestrías',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Número, Duración y Convenio
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Maestrías',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Número, Duración y Convenio
                    },
                    action: newExportAction,
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['25%', '45%', '30%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": `${BASE_URL_JS}maestria/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías. Por favor, revisa la consola para más detalles.');
                    } else {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // ID
                { "data": 1 }, // Nombre
                { "data": 2 }, // Número
                { "data": 3 }, // Duracion
                { "data": 4 }, // Convenio
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": "1%",
                    "className": "actions-column",
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="maestria/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="maestria/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                        `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        maestriaTable.on("click", ".btn-action-delete", function (e) {
            e.preventDefault();
            const urlEliminar = $(this).attr("href");

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: urlEliminar,
                        type: "GET",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        success: function (response) {
                            let res = typeof response === "string" ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire(
                                    "¡Eliminado!",
                                    res.message,
                                    "success"
                                );
                                maestriaTable.DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire(
                                    "Error",
                                    res.message,
                                    "error"
                                );
                            }
                        },
                        error: function (xhr) {
                            Swal.fire(
                                "Error",
                                "Ocurrió un error al procesar la solicitud.",
                                "error"
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
        // Manejador para el botón de eliminar
        maestriaTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            // Usar showConfirmationDialog si está disponible, de lo contrario confirm
            if (typeof showConfirmationDialog === 'function') {
                showConfirmationDialog(
                    '¿Estás seguro de que quieres eliminar este registro de Maestría?',
                    function () { // Callback de Confirmar
                        $.ajax({
                            url: `${BASE_URL_JS}maestria/delete/${id}`,
                            method: 'POST', // Usar POST para la eliminación AJAX
                            success: function (response) {
                                if (response.success) {
                                    showFlashMessage('success', response.message);
                                    maestriaTable.DataTable().ajax.reload(); // Recargar la tabla
                                } else {
                                    showFlashMessage('error', response.message || 'Error desconocido al eliminar el registro.');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error al eliminar el registro:', status, error);
                                showFlashMessage('error', 'Error de conexión al eliminar el registro.');
                            }
                        });
                    },
                    function () { // Callback de Cancelar (opcional)
                        showFlashMessage('info', 'Eliminación de Maestría cancelada.');
                    }
                );
            } else {
                // Fallback a confirm si showConfirmationDialog no está definido
                if (confirm('¿Estás seguro de que quieres eliminar este registro de Maestría?')) {
                    $.ajax({
                        url: `${BASE_URL_JS}maestria/delete/${id}`,
                        method: 'POST',
                        success: function (response) {
                            if (response.success) {
                                showFlashMessage('error', response.message);
                                maestriaTable.DataTable().ajax.reload();
                            } else {
                                showFlashMessage('error', response.message || 'Error desconocido al eliminar el registro.');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error al eliminar el registro:', status, error);
                            showFlashMessage('error', 'Error al eliminar el registro.');
                        }
                    });
                }
            }
        });
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formMaestria = $('#formMaestria'); // Asume que el ID de tu formulario es 'formMaestria'
    if (formMaestria.length) {

        if (typeof fillSelect === 'function') {
            fillSelect('duracion_id', 'duracion', 'duracion_current');
        }

        // Validación del formulario antes de enviar
        formMaestria.on('submit', function (event) {
            const nombre = $('#nombre').val().trim();
            const horas = $('#horas').val().trim();

            if (nombre === '' || horas === '') {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete los campos obligatorios (Nombre y Horas).');
                } else {
                    showFlashMessage('error', 'Por favor, complete los campos obligatorios (Nombre y Horas).');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
