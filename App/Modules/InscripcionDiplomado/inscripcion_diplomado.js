// app/Modules/InscripcionDiplomado/Views/js/inscripcion_diplomado.js
console.log('inscripcion_diplomado.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const inscripcionDiplomadoTable = $('#inscripcionDiplomadoTable');
    if (inscripcionDiplomadoTable.length) {
        inscripcionDiplomadoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}inscripcion_diplomado/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de inscripciones de diplomado. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Número de Diplomado Abierto
                { "data": 2 }, // Nombre completo del Alumno
                { "data": 3 }, // Estatus de Inscripción
                { // Columna para Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="inscripcion_diplomado/edit/${id}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="inscripcion_diplomado/delete/${id}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
        inscripcionDiplomadoTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                $.ajax({
                    url: `${BASE_URL_JS}inscripcion_diplomado/delete/${id}`,
                    method: 'POST', // Usar POST para la eliminación AJAX
                    success: function (response) {
                        // Asume que la respuesta es JSON con {success: true, message: "..."}
                        if (response.success) {
                            alert(response.message);
                            inscripcionDiplomadoTable.DataTable().ajax.reload(); // Recargar la tabla
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
        });
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formInscripcionDiplomado = $('#formInscripcionDiplomado'); // Asume que el ID de tu formulario es 'formInscripcionDiplomado'
    if (formInscripcionDiplomado.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('diplomado_abierto_id', 'diplomado_abierto', 'diplomado_abierto_current', 'numero');
            fillSelect('estatus_inscripcion_id', 'estatus_inscripcion', 'estatus_inscripcion_current');
        }

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
