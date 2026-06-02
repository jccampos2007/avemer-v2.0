// php_mvc_app/app/Modules/CursoAbierto/Views/js/curso_abierto.js
// Archivo JavaScript para el módulo de cursos abiertos
$(document).ready(function () {
    console.log("curso_abierto.js cargado.");

    const formCursosAbiertos = $('#form_cursos_abiertos');
    if (formCursosAbiertos.length > 0) {

        flatpickr("#fecha", {
            dateFormat: "Y-m-d", // Formato de fecha deseado (YYYY-MM-DD)
            altInput: true, // Muestra una entrada alternativa formateada para el usuario
            altFormat: "d F, Y", // Formato amigable para el usuario (ej. 23 Julio, 2025)
            locale: "es", // Establece el idioma a español
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('curso_autocomplete', 'curso_id', 'curso', 3, {
                displayColumn: "CONCAT(numero, ' - ', nombre)"
            });
            setupAutocomplete('docente_autocomplete', 'docente_id', 'docente', 3, {
                displayColumn: "CONCAT(primer_apellido, ', ', primer_nombre)"
            });
        }

        if (typeof fillSelect === 'function') {
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
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

        formCursosAbiertos.on('submit', function (event) {
            const nombreCartaContent = window.nombreCartaEditor ? window.nombreCartaEditor.getData() : '';

            if (nombreCartaContent.trim() === '') {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo "Nombre Carta" es obligatorio. Por favor, complete la información.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

    } else {
        console.log(BASE_URL_JS + "cursos_abiertos/data");

        var cursosAbiertosTable = $('#cursosAbiertosTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Cursos Abiertos',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8] // Exportar Número, Curso, Sede, Estatus, Docente, Fecha, Costo e Inicial
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Cursos Abiertos',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8] // Exportar Número, Curso, Sede, Estatus, Docente, Fecha, Costo e Inicial
                    },
                    action: newExportAction,
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['5%', '15%', '15%', '12%', '12%', '15%', '10%', '8%', '8%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "cursos_abiertos/data", // Endpoint para obtener los datos
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                    // Aquí puedes mostrar un mensaje de error al usuario
                    showFlashMessage('error', 'Error al cargar los datos de cursos abiertos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // Columna 0: ID
                { "data": 1 }, // Columna 1: Número
                { "data": 2 }, // Columna 2: Nombre del curso/taller
                { "data": 3 }, // Columna 3: Sede
                { "data": 4 }, // Columna 4: Estatus
                { "data": 5 }, // Columna 5: Instructor
                { "data": 6 }, // Columna 6: Fecha
                { "data": 7, className: "text-right font-medium" }, // Columna 7: Costo
                { "data": 8, className: "text-right font-medium" }, // Columna 8: Inicial
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    "width": "1%",
                    "className": "actions-column",
                    render: function (data, type, row) {
                        return `
                        <a href="cursos_abiertos/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                        <a href="cursos_abiertos/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        $('#cursosAbiertosTable').on('click', '.btn-action-delete', function (e) {
            e.preventDefault();
            const urlEliminar = $(this).attr('href');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: urlEliminar,
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (response) {
                            let res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire(
                                    '¡Eliminado!',
                                    res.message,
                                    'success'
                                );
                                cursosAbiertosTable.ajax.reload(null, false);
                            } else {
                                Swal.fire(
                                    'Error',
                                    res.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Error',
                                'Ocurrió un error al procesar la solicitud.',
                                'error'
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    }


});
