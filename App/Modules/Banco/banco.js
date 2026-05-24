$(document).ready(function () {
    console.log("banco.js cargado.");

    const formBanco = $('#form_banco');
    if (formBanco.length === 0) {
        var bancoTable = $('#bancoTable').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Bancos',
                    exportOptions: {
                        columns: [1] // Exportar únicamente Nombre del Banco
                    },
                    action: newExportAction // Interceptar acción por defecto (Llamada global)
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Bancos',
                    exportOptions: {
                        columns: [1] // Exportar únicamente Nombre del Banco
                    },
                    action: newExportAction, // Interceptar acción por defecto (Llamada global)
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['100%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "banco/getBancosData",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    showFlashMessage('error', 'Error al cargar los datos de bancos.');
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
                    data: null,
                    orderable: false,
                    searchable: false,
                    "width": "1%",
                    "className": "actions-column",
                    render: function (data, type, row) {
                        return `
                        <a href="${BASE_URL_JS}banco/edit/${row[0]}" class="btn-action btn-action-edit"><i class="fas fa-edit"></i></a>
                        <a href="${BASE_URL_JS}banco/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        $("#bancoTable").on("click", ".btn-action-delete", function (e) {
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
                                bancoTable.ajax.reload(null, false);
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
