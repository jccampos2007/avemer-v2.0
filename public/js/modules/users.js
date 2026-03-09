$(document).ready(function () {
    console.log("users.js cargado.");

    const formUsers = $('#form_users');
    if (formUsers.length > 0) {

        // Llenar selects con datos predefinidos
        if (typeof fillSelect === 'function') {
            fillSelect('estatus_activo_id', 'estatus_activo', 'estatus_activo_current');
        }

    } else {

        var usersTable = $('#usersTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": BASE_URL_JS + "users/data", // Endpoint para obtener los datos
                "type": "POST",
                "data": function (d) {
                    // Puedes añadir datos adicionales si es necesario
                    d.custom_param = 'some_value';
                },
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                    // Aquí puedes mostrar un mensaje de error al usuario
                    alert('Error al cargar los datos de alumnos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // Columna 0: ID
                { "data": 1 }, // Columna 2: C.I./Pasaporte
                { "data": 2 }, // Columna 3: Nombre Completo
                { "data": 3 }, // Columna 4: Usuario
                { "data": 4 }, // Columna 4: Tipo
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="users/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="users/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            }
        });

    }

});