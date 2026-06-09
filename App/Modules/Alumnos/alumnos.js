// php_mvc_app/app/Modules/Alumnos/Views/js/alumnos.js
// Archivo JavaScript para el módulo de alumnos
$(document).ready(function () {
    console.log("alumnos.js cargado.");

    const formAlumnos = $('#form_alumnos');
    if (formAlumnos.length > 0) {

        // Localizar flatpickr en español ANTES de crear la instancia
        if (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.es) {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        flatpickr("#fecha_nacimiento", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F, Y",
            locale: "es",
        });

        if (typeof fillSelect === 'function') {
            fillSelect('estatus_activo_id', 'estatus_activo', 'estatus_activo_current');
        }

        setupAutocomplete('profesion_oficio_autocomplete', 'profesion_oficio_id', 'profesion_oficio');
        setupAutocomplete('estado_autocomplete', 'estado_id', 'estado');
        setupAutocomplete('nacionalidad_autocomplete', 'nacionalidad_id', 'nacionalidad');

    } else {

        console.log(BASE_URL_JS + "alumnos/data");

        var alumnosTable = $('#alumnosTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
                    },
                    action: newExportAction // Interceptar acción por defecto (Llamada global)
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
                    },
                    action: newExportAction, // Interceptar acción por defecto (Llamada global)
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['25%', '45%', '30%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "alumnos/data", // Endpoint para obtener los datos
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (row[1] === null || row[1] === '')
                            return `
                                <img src="${BASE_URL_JS}image/default-avatar.png" alt="Foto de Alumno" class="img-thumbnail" style="width: 50px; height: 50px;">
                            `;
                        else
                            return `
                                <img src="data:${row[1]}" alt="Foto de Alumno" class="img-thumbnail" style="width: 50px; height: 50px;">
                            `;
                    }
                }, // Columna 1: Foto
                { "data": 2 }, // Columna 2: C.I./Pasaporte
                { "data": 3 }, // Columna 3: Nombre Completo
                { "data": 4 }, // Columna 4: Correo
                {
                    data: 6,
                    visible: false,
                    searchable: false
                }, // Columna 5: Auth (oculta)
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    "width": "1%",
                    "className": "actions-column",
                    render: function (data, type, row) {
                        let actions = '<div class="flex gap-2 justify-center">';
                        const hasAuth = row[6];
                        
                        if (typeof ALUMNO_PERMISSIONS !== 'undefined') {
                            if (ALUMNO_PERMISSIONS.modificar) {
                                actions += `<a href="alumnos/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>`;
                            }
                            if (ALUMNO_PERMISSIONS.eliminar) {
                                actions += `<a href="alumnos/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>`;
                            }
                        } else {
                            actions += `
                                <a href="alumnos/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="alumnos/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                            `;
                        }

                        if (hasAuth > 0) {
                            actions += `<button onclick="enviarCredenciales(${row[0]})" class="btn-action btn-action-email" title="Enviar Credenciales"><i class="fas fa-envelope"></i></button>`;
                        } else {
                            actions += `<button onclick="crearUsuarioApp(${row[0]})" class="btn-action btn-action-user" title="Crear Usuario App"><i class="fas fa-user-plus"></i></button>`;
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
        $('#alumnosTable').on('click', '.btn-action-delete', function (e) {
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
                                Swal.fire('¡Eliminado!', res.message, 'success');
                                alumnosTable.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                        }
                    });
                }
            });
        });

    }

});

function crearUsuarioApp(alumnoId) {
    $.post(`${BASE_URL_JS}alumnos/createUserApp/${alumnoId}`, function (res) {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Usuario Creado',
                html: `Contraseña: <strong style="font-size:1.5em;letter-spacing:2px">${res.password}</strong>
                       <p class="mt-2 text-sm text-gray-500">Guarda esta contraseña. No se mostrará de nuevo.</p>`,
                confirmButtonText: 'Copiar y Cerrar',
            }).then(() => {
                navigator.clipboard.writeText(res.password);
                $('#alumnosTable').DataTable().ajax.reload(null, false);
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
    }, 'json');
}

function enviarCredenciales(alumnoId) {
    Swal.fire({
        title: '¿Enviar credenciales?',
        text: 'Se enviará un correo al alumno con sus datos de acceso.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL_JS}alumnos/sendCredentials/${alumnoId}`, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Enviado', text: res.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            }, 'json');
        }
    });
}