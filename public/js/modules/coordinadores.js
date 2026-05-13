// php_mvc_app/app/Modules/Coordinadores/Views/js/coordinadores.js
// Archivo JavaScript para el módulo de coordinadores
$(document).ready(function () {
    console.log("coordinadores.js cargado.");

    const formCoordinadores = $('#form_coordinadores');
    if (formCoordinadores.length > 0) {

        flatpickr("#fecha_nacimiento", {
            dateFormat: "Y-m-d", // Formato de fecha deseado (YYYY-MM-DD)
            altInput: true, // Muestra una entrada alternativa formateada para el usuario
            altFormat: "d F, Y", // Formato amigable para el usuario (ej. 23 Julio, 2025)
            locale: "es", // Establece el idioma a español
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        if (typeof fillSelect === 'function') {
            fillSelect('profesion_oficio_id', 'profesion_oficio', 'profesion_oficio_current');
            fillSelect('estado_id', 'estado', 'estado_current');
            fillSelect('nacionalidad_id', 'nacionalidad', 'nacionalidad_current');
            fillSelect('estatus_activo_id', 'estatus_activo', 'estatus_activo_current');
        }

    } else {

        console.log(BASE_URL_JS + "coordinadores/data");

        var coordinadoresTable = $('#coordinadoresTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": BASE_URL_JS + "coordinadores/data", // Endpoint para obtener los datos
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                }
            },
            "columns": [
                { "data": 0 }, // Columna 0: ID
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (row[1] === null || row[1] === '')
                            return `
                                <img src="${BASE_URL_JS}../assets/images/NO-IMAGE.jpg" alt="Foto de Coordinador" class="img-thumbnail" style="width: 50px; height: 50px;">
                            `;
                        else
                            return `
                                <img src="data:${row[1]}" alt="Foto de Coordinador" class="img-thumbnail" style="width: 50px; height: 50px;">
                            `;
                    }
                }, // Columna 1: Foto
                { "data": 2 }, // Columna 2: C.I./Pasaporte
                { "data": 3 }, // Columna 3: Nombre Completo
                { "data": 4 }, // Columna 4: Correo
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let actions = '<div class="flex gap-2 justify-center">';
                        
                        if (typeof COORDINADOR_PERMISSIONS !== 'undefined') {
                            if (COORDINADOR_PERMISSIONS.modificar) {
                                actions += `<a href="coordinadores/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>`;
                            }
                            if (COORDINADOR_PERMISSIONS.eliminar) {
                                actions += `<a href="coordinadores/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>`;
                            }
                        } else {
                            actions += `
                                <a href="coordinadores/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>
                                <a href="coordinadores/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>
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
        $('#coordinadoresTable').on('click', '.btn-delete', function (e) {
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
                                coordinadoresTable.ajax.reload(null, false);
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
