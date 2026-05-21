// app/Modules/MaestriaAbierto/Views/js/maestria_abierto.js
console.log('maestria_abierto.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const maestriaAbiertoTable = $('#maestriaAbiertoTable');
    if (maestriaAbiertoTable.length) {
        maestriaAbiertoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-2"></i> Exportar a Excel',
                    className: 'buttons-excel',
                    title: 'Listado de Maestrías Abiertas',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6] // Exportar únicamente Número, Maestría, Sede, Estatus, Docente y Fecha
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-2"></i> Exportar a PDF',
                    className: 'buttons-pdf',
                    title: 'Listado de Maestrías Abiertas',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6] // Exportar únicamente Número, Maestría, Sede, Estatus, Docente y Fecha
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
                "url": `${BASE_URL_JS}maestria_abierto/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías abiertas. Por favor, revisa la consola para más detalles.');
                    } else {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías abiertas. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Maestría (nombre)
                { "data": 3 }, // Sede (nombre)
                { "data": 4 }, // Estatus (nombre)
                { "data": 5 }, // Docente (nombre completo)
                { "data": 6 }, // Fecha
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="maestria_abierto/edit/${id}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>
                            <a href="maestria_abierto/delete/${id}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>
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

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        maestriaAbiertoTable.on("click", ".btn-delete", function (e) {
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
                                maestriaAbiertoTable.DataTable().ajax.reload(null, false);
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
    const formMaestriaAbierto = $('#formMaestriaAbierto'); // Asume que el ID de tu formulario es 'formMaestriaAbierto'
    if (formMaestriaAbierto.length) {
        // Inicializar Flatpickr para el campo de fecha
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            altInput: true, // Muestra una entrada alternativa formateada para el usuario
            altFormat: "d F, Y", // Formato amigable para el usuario (ej. 23 Julio, 2025)
            locale: "es", // Establece el idioma a español
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        // Inicializar CKEditor para el campo nombre_carta
        ClassicEditor
            .create(document.querySelector('#nombre_carta'), {
                language: 'es'
            })
            .then(editor => {
                window.nombreCartaEditor = editor;
            })
            .catch(error => {
                console.error('Error al inicializar el editor de nombre_carta:', error);
            });

        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('maestria_id', 'maestria', 'maestria_current');
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
            fillSelect('docente_id', 'docente', 'docente_current', 'CONCAT(primer_apellido, ", ", primer_nombre)');
        }

        // Validación del formulario antes de enviar
        formMaestriaAbierto.on('submit', function (event) {
            const numero = $('#numero').val().trim();
            const maestriaId = $('#maestria_id').val();
            const sedeId = $('#sede_id').val();
            const estatusId = $('#estatus_id').val();
            const docenteId = $('#docente_id').val();
            const fecha = $('#fecha').val().trim();
            const nombreCartaContent = (typeof window.nombreCartaEditor !== 'undefined') ? window.nombreCartaEditor.getData().trim() : '';

            if (numero === '' || !maestriaId || !sedeId || !estatusId || !docenteId || fecha === '' || nombreCartaContent === '') {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                } else {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
