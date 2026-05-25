$(document).ready(function () {
    console.log("sede.js cargado.");

    const formSede = $('#form_sede');
    if (formSede.length > 0) {
        // Llenar select de estados
        if (typeof fillSelect === 'function') {
            // Nota: Se asume que existe un endpoint o lógica para 'estado' en el sistema
            fillSelect('estado_id', 'estado', 'estado_current');
        }
    } else {
        var sedeTable = $('#sedeTable').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Sedes',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Teléfono, Correo y Estado
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Sedes',
                    exportOptions: {
                        columns: [1, 2, 3, 4] // Exportar únicamente Nombre, Teléfono, Correo y Estado
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
                "url": BASE_URL_JS + "sede/getSedesData",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    showFlashMessage('error', 'Error al cargar los datos de sedes.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    "width": "1%",
                    "className": "actions-column",
                    render: function (data, type, row) {
                        let actions = '<div class="flex gap-2 justify-center">';
                        
                        if (typeof SEDE_PERMISSIONS !== 'undefined') {
                            if (SEDE_PERMISSIONS.modificar) {
                                actions += `<a href="${BASE_URL_JS}sede/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>`;
                            }
                            if (SEDE_PERMISSIONS.eliminar) {
                                actions += `<a href="${BASE_URL_JS}sede/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>`;
                            }
                        } else {
                            actions += `
                                <a href="${BASE_URL_JS}sede/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="${BASE_URL_JS}sede/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                            `;
                        }
                        
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        $("#sedeTable").on("click", ".btn-action-delete", function (e) {
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
                                sedeTable.ajax.reload(null, false);
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
});
