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
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Maestrías Abiertas',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8]
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Maestrías Abiertas',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8]
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
                    Swal.fire('Error', 'Error al cargar los datos de maestrías abiertas.', 'error');
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
                { "data": 7 }, // Costo
                { "data": 8 }, // Inicial
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": "1%",
                    "className": "actions-column",
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="maestria_abierto/edit/${id}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="maestria_abierto/delete/${id}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
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
        maestriaAbiertoTable.on("click", ".btn-action-delete", function (e) {
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
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('maestria_autocomplete', 'maestria_id', 'maestria', 3, {
                displayColumn: "CONCAT(numero, ' - ', nombre)"
            });
            setupAutocomplete('docente_autocomplete', 'docente_id', 'docente', 3, {
                displayColumn: "CONCAT(primer_apellido, ', ', primer_nombre)"
            });
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

            if (nombreCartaContent === '') {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo "Nombre Carta" es obligatorio. Por favor, complete la información.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            if (numero === '' || !maestriaId || !sedeId || !estatusId || !docenteId || fecha === '') {
                Swal.fire('Error', 'Por favor, complete todos los campos obligatorios.', 'error');
                event.preventDefault();
            }
        });
    }

});
