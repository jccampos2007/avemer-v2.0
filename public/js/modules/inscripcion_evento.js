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
                }, // ID
                { "data": 1 }, // Evento Abierto (número)
                { "data": 2 }, // Alumno (nombre completo)
                { "data": 3 }, // Estatus de Inscripción
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="inscripcion_evento/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>
                            <a href="inscripcion_evento/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>
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
        inscripcionEventoTable.on("click", ".btn-delete", function (e) {
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
            fillSelect('evento_abierto_id', 'evento_abierto', 'evento_abierto_current', 'CONCAT(numero)', 'estatus_id'); // TODO select con union de Tabla
            fillSelect('estatus_inscripcion_id', 'estatus_inscripcion', 'estatus_inscripcion_current');
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

        $("#alumno_autocomplete").autocomplete({
            minLength: 3, // Iniciar la búsqueda después de 3 caracteres
            source: function (request, response) {
                // Realizar la solicitud AJAX al endpoint de búsqueda de alumnos
                $.ajax({
                    url: `${BASE_URL_JS}api/search/alumno`, // La URL de tu nuevo endpoint PHP
                    dataType: "json",
                    data: {
                        term: request.term,
                        displayColumn: 'CONCAT(primer_apellido, ", ", primer_nombre)'
                    },
                    success: function (data) {
                        response(data); // Pasar los datos al autocompletado de jQuery UI
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la búsqueda de alumnos:", status, error);
                        response([]); // Devolver un array vacío en caso de error
                    }
                });
            },
            select: function (event, ui) {
                // Cuando se selecciona un elemento de la lista
                // ui.item.id contiene el ID real del alumno
                // ui.item.value contiene el texto que se muestra en el input (nombre completo)
                $("#alumno_id").val(ui.item.id); // Guardar el ID en el campo oculto
                // El campo visible ya se actualiza automáticamente con ui.item.value
                console.log("alumno seleccionado:", ui.item.label, "ID:", ui.item.id);
            },
            change: function (event, ui) {
                console.log('ln: 101 >>> ' + event)
                if (ui.item === null) { // No se seleccionó ningún item de la lista
                    $("#alumno_id").val(""); // Limpiar el ID oculto
                    console.log("Campo de alumno limpiado o valor no válido.");
                }
            }
        });
    }
});
