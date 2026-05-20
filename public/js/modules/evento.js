// app/Modules/Evento/Views/js/evento.js
console.log('evento.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const eventoTable = $('#eventoTable');
    if (eventoTable.length) {
        eventoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}evento/list`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    showFlashMessage('error', 'Error al cargar los datos de eventos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Duración (nombre)
                { "data": 2 }, // Nombre
                { "data": 3 }, // Descripción
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        let actions = '<div class="flex gap-2 justify-center">';
                        
                        if (typeof EVENTO_PERMISSIONS !== 'undefined') {
                            if (EVENTO_PERMISSIONS.modificar) {
                                actions += `<a href="evento/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>`;
                            }
                            if (EVENTO_PERMISSIONS.eliminar) {
                                actions += `<a href="evento/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>`;
                            }
                        } else {
                            // Fallback por si la variable no está definida
                            actions += `
                                <a href="evento/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>
                                <a href="evento/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>
                            `;
                        }
                        
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });
        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        eventoTable.on("click", ".btn-delete", function (e) {
            e.preventDefault();
            const urlEliminar = $(this).attr("href");
            const dataTableInstance = eventoTable.DataTable();

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
                                dataTableInstance.ajax.reload(null, false);
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
    const formEvento = $('#formEvento'); // Asume que el ID de tu formulario es 'formEvento'
    if (formEvento.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('duracion_id', 'duracion', 'duracion_current', 'nombre');
        }

        // Validación del formulario antes de enviar
        formEvento.on('submit', function (event) {
            const duracionId = $('#duracion_id').val();
            const nombre = $('#nombre').val().trim();
            const descripcion = $('#descripcion').val().trim();
            const siglas = $('#siglas').val().trim();
            const costo = $('#costo').val(); // No trim() para números, pero valida si es numérico
            const inicial = $('#inicial').val(); // No trim() para números, pero valida si es numérico

            if (!duracionId || nombre === '' || descripcion === '' || siglas === '' || isNaN(costo) || isNaN(inicial)) {
                showFlashMessage('error', 'Por favor, complete todos los campos obligatorios y asegúrese de que Costo e Inicial sean números válidos.');
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
