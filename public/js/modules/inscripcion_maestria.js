// app/Modules/InscripcionMaestria/Views/js/inscripcion_maestria.js
console.log('inscripcion_maestria.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const inscripcionMaestriaTable = $('#inscripcionMaestriaTable');
    if (inscripcionMaestriaTable.length) {
        inscripcionMaestriaTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}inscripcion_maestria/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de inscripciones de maestría. Por favor, revisa la consola para más detalles.');
                    } else {
                        alert('Error al cargar los datos de inscripciones de maestría. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                { "data": 0 }, // ID (mantener en los datos para referencia, pero ocultarlo)
                { "data": 1 }, // Maestría Abierta (número)
                { "data": 2 }, // Alumno (nombre completo)
                { "data": 3 }, // Estatus de Inscripción
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="inscripcion_maestria/edit/${id}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="inscripcion_maestria/delete/${id}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
                        `;
                    }
                }
            ],
            "columnDefs": [
                {
                    "targets": [0], // Ocultar la primera columna (índice 0, que es el ID)
                    "visible": false,
                    "searchable": false
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formInscripcionMaestria = $('#formInscripcionMaestria'); // Asume que el ID de tu formulario es 'formInscripcionMaestria'
    if (formInscripcionMaestria.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('maestria_abierto_id', 'maestria_abierto', 'maestria_abierto_current', 'numero');
            fillSelect('estatus_inscripcion_id', 'estatus_inscripcion', 'estatus_inscripcion_current');
        }

        // Validación del formulario antes de enviar
        formInscripcionMaestria.on('submit', function (event) {
            const maestriaAbiertoId = $('#maestria_abierto_id').val();
            const alumnoId = $('#alumno_id').val();
            const estatusInscripcionId = $('#estatus_inscripcion_id').val();

            if (!maestriaAbiertoId || !alumnoId || !estatusInscripcionId) {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                } else {
                    alert('Por favor, complete todos los campos obligatorios.');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });

        $("#alumno_autocomplete").autocomplete({
            minLength: 3, // Iniciar la búsqueda después de 3 caracteres
            source: function (request, response) {
                // Realizar la solicitud AJAX al endpoint de búsqueda de alumnos
                $.ajax({
                    url: `${BASE_URL_JS}api/search/alumno`, // La URL de tu nuevo endpoint PHP
                    dataType: "json",
                    data: {
                        term: request.term,
                        displayColumn: 'CONCAT(primer_apellido, ", ", primer_nombre)'
                    },
                    success: function (data) {
                        response(data); // Pasar los datos al autocompletado de jQuery UI
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la búsqueda de alumnos:", status, error);
                        response([]); // Devolver un array vacío en caso de error
                    }
                });
            },
            select: function (event, ui) {
                // Cuando se selecciona un elemento de la lista
                // ui.item.id contiene el ID real del alumno
                // ui.item.value contiene el texto que se muestra en el input (nombre completo)
                $("#alumno_id").val(ui.item.id); // Guardar el ID en el campo oculto
                // El campo visible ya se actualiza automáticamente con ui.item.value
                console.log("alumno seleccionado:", ui.item.label, "ID:", ui.item.id);
            },
            change: function (event, ui) {
                console.log('ln: 101 >>> ' + event)
                if (ui.item === null) { // No se seleccionó ningún item de la lista
                    $("#alumno_id").val(""); // Limpiar el ID oculto
                    console.log("Campo de alumno limpiado o valor no válido.");
                }
            }
        });
    }
});
