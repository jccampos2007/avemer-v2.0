$(document).ready(function () {
    const ciudadTable = $('#ciudadTable');
    if (ciudadTable.length) {
        ciudadTable.DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ajax": {
                "url": `${BASE_URL_JS}ciudad/getData`,
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Nombre
                { "data": 2 }, // Pais ID
                { // Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        let btns = '';
                        if (CIUDAD_PERMISSIONS.modificar) {
                            btns += `<a href="${BASE_URL_JS}ciudad/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5"></i></a> `;
                        }
                        if (CIUDAD_PERMISSIONS.eliminar) {
                            btns += `<a href="${BASE_URL_JS}ciudad/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5"></i></a>`;
                        }
                        return btns;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            },
            "autoWidth": false
        });

        ciudadTable.on("click", ".btn-delete", function (e) {
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
                        success: function (response) {
                            let res = typeof response === "string" ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire("¡Eliminado!", res.message, "success");
                                ciudadTable.DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire("Error", res.message, "error");
                            }
                        },
                        error: function () {
                            Swal.fire("Error", "Ocurrió un error al procesar la solicitud.", "error");
                        }
                    });
                }
            });
        });
    }
});
