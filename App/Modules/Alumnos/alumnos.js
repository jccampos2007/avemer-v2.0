// php_mvc_app/app/Modules/Alumnos/Views/js/alumnos.js
// Archivo JavaScript para el módulo de alumnos
$(document).ready(function () {
    console.log("alumnos.js cargado.");

    const formAlumnos = $('#form_alumnos');
    if (formAlumnos.length > 0) {

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

        $("#usuario_autocomplete").autocomplete({
            minLength: 3, // Iniciar la búsqueda después de 3 caracteres
            source: function (request, response) {
                // Realizar la solicitud AJAX al endpoint de búsqueda de usuarios
                $.ajax({
                    url: `${BASE_URL_JS}api/users/search`, // La URL de tu nuevo endpoint PHP
                    dataType: "json",
                    data: {
                        term: request.term // El término de búsqueda que el usuario ha escrito
                    },
                    success: function (data) {
                        response(data); // Pasar los datos al autocompletado de jQuery UI
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la búsqueda de usuarios:", status, error);
                        response([]); // Devolver un array vacío en caso de error
                    }
                });
            },
            select: function (event, ui) {
                // Cuando se selecciona un elemento de la lista
                // ui.item.id contiene el ID real del usuario
                // ui.item.value contiene el texto que se muestra en el input (nombre completo)
                $("#usuario_id").val(ui.item.id); // Guardar el ID en el campo oculto
                // El campo visible ya se actualiza automáticamente con ui.item.value
                console.log("Usuario seleccionado:", ui.item.label, "ID:", ui.item.id);
            },
            change: function (event, ui) {
                // Este evento se dispara cuando el valor del input cambia y no se selecciona un item de la lista.
                // Si el usuario borra el texto o escribe algo que no coincide con un item,
                // debemos limpiar el campo oculto para evitar enviar un ID incorrecto.
                if (ui.item === null) { // No se seleccionó ningún item de la lista
                    $("#usuario_id").val(""); // Limpiar el ID oculto
                    console.log("Campo de usuario limpiado o valor no válido.");
                }
            }
        });

    } else {

        console.log(BASE_URL_JS + "alumnos/data");

        var alumnosTable = $('#alumnosTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": BASE_URL_JS + "alumnos/data", // Endpoint para obtener los datos
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
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (row[1] === null || row[1] === '')
                            return `
                                <img src="${BASE_URL_JS}../assets/images/NO-IMAGE.jpg" alt="Foto de Alumno" class="img-thumbnail" style="width: 50px; height: 50px;">
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
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="alumnos/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="alumnos/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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

