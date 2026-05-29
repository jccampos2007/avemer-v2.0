// app/Modules/InscripcionEvento/Views/js/inscripcion_evento.js
console.log('inscripcion_evento.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const inscripcionEventoTable = $('#inscripcionEventoTable');
    if (inscripcionEventoTable.length) {
        inscripcionEventoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Inscripciones de Eventos',
                    exportOptions: {
                        columns: [1, 2]
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Inscripciones de Eventos',
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
                "url": `${BASE_URL_JS}inscripcion_evento/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    showFlashMessage('error', 'Error al cargar los datos de inscripciones de evento. Por favor, revisa la consola para más detalles.');
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
                            <a href="inscripcion_evento/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="inscripcion_evento/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                        `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });
        setupAlumnoCopyHandler('#inscripcionEventoTable');

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        inscripcionEventoTable.on("click", ".btn-action-delete", function (e) {
            e.preventDefault();
            const urlEliminar = $(this).attr("href");
            const dataTableInstance = inscripcionEventoTable.DataTable();

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
                                dataTableInstance.ajax.reload(null, false);
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
    const formInscripcionEvento = $('#formInscripcionEvento'); // Asume que el ID de tu formulario es 'formInscripcionEvento'
    if (formInscripcionEvento.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('estatus_inscripcion_id', 'estatus_inscripcion', 'estatus_inscripcion_current');
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('evento_abierto_autocomplete', 'evento_abierto_id', 'evento_abierto', 3, {
                displayColumn: "CONCAT(numero, ' - ', (SELECT nombre FROM evento WHERE id = evento_abierto.evento_id))"
            });
        }

        // Validación del formulario antes de enviar
        formInscripcionEvento.on('submit', function (event) {
            const eventoAbiertoId = $('#evento_abierto_id').val();
            const alumnoId = $('#alumno_id').val();
            const estatusInscripcionId = $('#estatus_inscripcion_id').val();

            if (!eventoAbiertoId || !alumnoId || !estatusInscripcionId) {
                showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                event.preventDefault(); // Detiene el envío del formulario
            }
        });

        setupAutocomplete('alumno_autocomplete', 'alumno_id', 'alumno', 3, {
            displayColumn: 'CONCAT(primer_apellido, ", ", primer_nombre)'
        });
    }
});
