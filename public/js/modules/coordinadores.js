// php_mvc_app/app/Modules/Coordinadores/Views/js/coordinadores.js
// Archivo JavaScript para el módulo de coordinadores
$(document).ready(function () {
    console.log("coordinadores.js cargado.");

    const formCoordinadores = $('#form_coordinadores');
    if (formCoordinadores.length > 0) {

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


        // Autocomplete for Profesión/Oficio, Estado, Nacionalidad
        function setupAutocomplete(inputId, hiddenId, endpoint) {
            $('#' + inputId).autocomplete({
                minLength: 2,
                source: function (request, response) {
                    $.ajax({
                        url: `${BASE_URL_JS}api/search/${endpoint}`,
                        dataType: "json",
                        data: { term: request.term },
                        success: function (data) { response(data); },
                        error: function () { response([]); }
                    });
                },
                select: function (event, ui) {
                    $('#' + hiddenId).val(ui.item.id);
                    $(this).val(ui.item.label);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) { $('#' + hiddenId).val(''); }
                }
            });
        }
        setupAutocomplete('profesion_oficio_autocomplete', 'profesion_oficio_id', 'profesion_oficio');
        setupAutocomplete('estado_autocomplete', 'estado_id', 'estado');
        setupAutocomplete('nacionalidad_autocomplete', 'nacionalidad_id', 'nacionalidad');

    } else {

        console.log(BASE_URL_JS + "coordinadores/data");

        var coordinadoresTable = $('#coordinadoresTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-2"></i> Exportar a Excel',
                    className: 'buttons-excel',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-2"></i> Exportar a PDF',
                    className: 'buttons-pdf',
                    title: 'Listado de Alumnos',
                    exportOptions: {
                        columns: [2, 3, 4] // Exportar únicamente C.I., Nombre y Correo
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
                "url": BASE_URL_JS + "coordinadores/data", // Endpoint para obtener los datos
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // Columna 0: ID
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (row[1] === null || row[1] === '')
                            return `
                                <img src="${BASE_URL_JS}image/default-avatar.png" alt="Foto de Coordinador" class="img-thumbnail" style="width: 50px; height: 50px;">
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
