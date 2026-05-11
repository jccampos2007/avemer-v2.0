// app/Modules/Maestria/Views/js/maestria.js
console.log('maestria.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const maestriaTable = $('#maestriaTable');
    if (maestriaTable.length) {
        maestriaTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}maestria/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías. Por favor, revisa la consola para más detalles.');
                    } else {
                        alert('Error al cargar los datos de maestrías. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Nombre
                { "data": 2 }, // Número
                { "data": 3 }, // Duracion
                { "data": 4 }, // Convenio
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="maestria/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="maestria/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
                        `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });

        // Manejador para el botón de eliminar
        maestriaTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            // Usar showConfirmationDialog si está disponible, de lo contrario confirm
            if (typeof showConfirmationDialog === 'function') {
                showConfirmationDialog(
                    '¿Estás seguro de que quieres eliminar este registro de Maestría?',
                    function () { // Callback de Confirmar
                        $.ajax({
                            url: `${BASE_URL_JS}maestria/delete/${id}`,
                            method: 'POST', // Usar POST para la eliminación AJAX
                            success: function (response) {
                                if (response.success) {
                                    showFlashMessage('success', response.message);
                                    maestriaTable.DataTable().ajax.reload(); // Recargar la tabla
                                } else {
                                    showFlashMessage('error', response.message || 'Error desconocido al eliminar el registro.');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error al eliminar el registro:', status, error);
                                showFlashMessage('error', 'Error de conexión al eliminar el registro.');
                            }
                        });
                    },
                    function () { // Callback de Cancelar (opcional)
                        showFlashMessage('info', 'Eliminación de Maestría cancelada.');
                    }
                );
            } else {
                // Fallback a confirm si showConfirmationDialog no está definido
                if (confirm('¿Estás seguro de que quieres eliminar este registro de Maestría?')) {
                    $.ajax({
                        url: `${BASE_URL_JS}maestria/delete/${id}`,
                        method: 'POST',
                        success: function (response) {
                            if (response.success) {
                                alert(response.message);
                                maestriaTable.DataTable().ajax.reload();
                            } else {
                                alert(response.message || 'Error desconocido al eliminar el registro.');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error al eliminar el registro:', status, error);
                            alert('Error al eliminar el registro.');
                        }
                    });
                }
            }
        });
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formMaestria = $('#formMaestria'); // Asume que el ID de tu formulario es 'formMaestria'
    if (formMaestria.length) {

        if (typeof fillSelect === 'function') {
            fillSelect('duracion_id', 'duracion', 'duracion_current');
        }

        // Validación del formulario antes de enviar
        formMaestria.on('submit', function (event) {
            const nombre = $('#nombre').val().trim();
            const horas = $('#horas').val().trim();

            if (nombre === '' || horas === '') {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete los campos obligatorios (Nombre y Horas).');
                } else {
                    alert('Por favor, complete los campos obligatorios (Nombre y Horas).');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
