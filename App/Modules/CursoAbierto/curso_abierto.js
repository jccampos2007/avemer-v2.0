// php_mvc_app/app/Modules/CursoAbierto/Views/js/curso_abierto.js
// Archivo JavaScript para el módulo de cursos abiertos
$(document).ready(function () {
    console.log("curso_abierto.js cargado.");

    const formCursosAbiertos = $('#form_cursos_abiertos');
    if (formCursosAbiertos.length > 0) {

        flatpickr("#fecha", {
            dateFormat: "Y-m-d", // Formato de fecha deseado (YYYY-MM-DD)
            altInput: true, // Muestra una entrada alternativa formateada para el usuario
            altFormat: "d F, Y", // Formato amigable para el usuario (ej. 23 Julio, 2025)
            locale: "es", // Establece el idioma a español
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        if (typeof fillSelect === 'function') {
            fillSelect('curso_id', 'curso', 'curso_current');
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
            fillSelect('docente_id', 'docente', 'docente_current', 'CONCAT(primer_apellido, \', \', primer_nombre)');
        }

        // Inicializar CKEditor para el campo nombre_carta
        ClassicEditor
            .create(document.querySelector('#nombre_carta'), {
                language: 'es'
            })
            .then(editor => {
                console.log('Editor de nombre_carta inicializado', editor);
            })
            .catch(error => {
                console.error('Error al inicializar el editor de nombre_carta:', error);
            });

    } else {
        console.log(BASE_URL_JS + "cursos_abiertos/data");

        var cursosAbiertosTable = $('#cursosAbiertosTable').DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": BASE_URL_JS + "cursos_abiertos/data", // Endpoint para obtener los datos
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                    // Aquí puedes mostrar un mensaje de error al usuario
                    alert('Error al cargar los datos de cursos abiertos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // Columna 0: ID
                { "data": 1 }, // Columna 1: Número
                { "data": 2 }, // Columna 2: Curso ID (debería ser el nombre del curso)
                { "data": 3 }, // Columna 3: Sede ID (debería ser el nombre de la sede)
                { "data": 4 }, // Columna 4: Estatus ID (debería ser el nombre del estatus)
                { "data": 5 }, // Columna 5: Docente ID (debería ser el nombre del docente/coordinador)
                { "data": 6 }, // Columna 6: Fecha
                { "data": 7 }, // Columna 7: Nombre Carta
                { "data": 8 }, // Columna 8: Convenio
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="cursos_abiertos/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="cursos_abiertos/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
