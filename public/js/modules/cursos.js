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
                    alert('Error al cargar los datos de cursos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // Columna 0: ID
                { "data": 1 }, // Columna 1: Nombre
                { "data": 2 }, // Columna 2: Número
                { "data": 3 }, // Columna 3: Horas
                { "data": 4 }, // Columna 4: Convenio
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="cursos/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="cursos/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
