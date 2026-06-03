// app/Modules/InscripcionMaestria/Views/js/inscripcion_maestria.js
console.log('inscripcion_maestria.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const inscripcionMaestriaTable = $('#inscripcionMaestriaTable');
    if (inscripcionMaestriaTable.length) {
        inscripcionMaestriaTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Inscripciones de Maestrías',
                    exportOptions: {
                        columns: [1, 2]
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Inscripciones de Maestrías',
                    exportOptions: {
                        columns: [1, 2]
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
                "url": `${BASE_URL_JS}inscripcion_maestria/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de inscripciones de maestría. Por favor, revisa la consola para más detalles.');
                    } else {
                        showFlashMessage('error', 'Error al cargar los datos de inscripciones de maestría. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                },
                { "data": 1 },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return renderAlumnoColumn(type, row);
                    }
                },
                { "data": 6 },
                {
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": "1%",
                    "className": "actions-column",
                    "render": function (data, type, row) {
                        return `
                            <a href="inscripcion_maestria/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="inscripcion_maestria/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                        `;
                    }
                }
            ],
            "columnDefs": [
                {
                    "targets": [0], // Ocultar la primera columna (índice 0, que es el ID)
                    "visible": false,
                    "searchable": false
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });

        setupAlumnoCopyHandler('#inscripcionMaestriaTable');

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        inscripcionMaestriaTable.on("click", ".btn-action-delete", function (e) {
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
                                inscripcionMaestriaTable.DataTable().ajax.reload(null, false);
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
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formInscripcionMaestria = $('#formInscripcionMaestria'); // Asume que el ID de tu formulario es 'formInscripcionMaestria'
    if (formInscripcionMaestria.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('estatus_inscripcion_id', 'estatus_inscripcion', 'estatus_inscripcion_current');
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('maestria_abierto_autocomplete', 'maestria_abierto_id', 'maestria_abierto', 3, {
                displayColumn: "CONCAT(numero, ' - ', (SELECT nombre FROM maestria WHERE id = maestria_abierto.maestria_id))"
            });
        }

        // Validación del formulario antes de enviar
        formInscripcionMaestria.on('submit', function (event) {
            const maestriaAbiertoId = $('#maestria_abierto_id').val();
            const alumnoId = $('#alumno_id').val();
            const estatusInscripcionId = $('#estatus_inscripcion_id').val();

            if (!maestriaAbiertoId || !alumnoId || !estatusInscripcionId) {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                } else {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });

        setupAutocomplete('alumno_autocomplete', 'alumno_id', 'alumno', 3, {
            displayColumn: "CONCAT(primer_nombre, ' ', primer_apellido, ', CI:', COALESCE(tipo_documento,''), ci_pasaporte)"
        });
    }
});
