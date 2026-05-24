// php_mvc_app/app/Modules/Cursos/Views/js/cursos.js
// Archivo JavaScript para el módulo de cursos
$(document).ready(function () {
    console.log("cursos.js cargado.");

    const formCursos = $('#form_cursos');
    if (formCursos.length == 0) {
        console.log(BASE_URL_JS + "cursos/data");

        var cursosTable = $('#cursosTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Cursos',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Número, Horas y Convenio
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Cursos',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Número, Horas y Convenio
                    },
                    action: newExportAction,
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['25%', '25%', '25%', '25%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "cursos/data", // Endpoint para obtener los datos
                "type": "POST",
                "data": function (d) {
                    // Puedes añadir datos adicionales si es necesario
                    d.custom_param = 'some_value';
                },
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                    // Aquí puedes mostrar un mensaje de error al usuario
                    showFlashMessage('error', 'Error al cargar los datos de cursos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // Columna 0: ID
                { "data": 1, responsivePriority: 1 }, // Columna 1: Nombre (siempre visible)
                { "data": 2, responsivePriority: 3 }, // Columna 2: Número
                { "data": 3, responsivePriority: 4 }, // Columna 3: Horas
                { "data": 4, responsivePriority: 5 }, // Columna 4: Convenio
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 10000,
                    "width": "1%",
                    "className": "actions-column",
                    render: function (data, type, row) {
                        let actions = '<div class="flex gap-2 justify-center">';
                        
                        if (typeof CURSOS_PERMISSIONS !== 'undefined') {
                            if (CURSOS_PERMISSIONS.modificar) {
                                actions += `<a href="cursos/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>`;
                            }
                            if (CURSOS_PERMISSIONS.eliminar) {
                                actions += `<a href="cursos/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>`;
                            }
                        } else {
                            actions += `
                                <a href="cursos/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="cursos/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                            `;
                        }
                        
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        $('#cursosTable').on('click', '.btn-action-delete', function (e) {
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
                                cursosTable.ajax.reload(null, false);
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
