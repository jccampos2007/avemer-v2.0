// app/Modules/MaestriaAbierto/Views/js/maestria_abierto.js
console.log('maestria_abierto.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const maestriaAbiertoTable = $('#maestriaAbiertoTable');
    if (maestriaAbiertoTable.length) {
        maestriaAbiertoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}maestria_abierto/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    // Usar showFlashMessage si está disponible, de lo contrario alert
                    if (typeof showFlashMessage === 'function') {
                        showFlashMessage('error', 'Error al cargar los datos de maestrías abiertas. Por favor, revisa la consola para más detalles.');
                    } else {
                        alert('Error al cargar los datos de maestrías abiertas. Por favor, revisa la consola para más detalles.');
                    }
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Maestría (nombre)
                { "data": 3 }, // Sede (nombre)
                { "data": 4 }, // Estatus (nombre)
                { "data": 5 }, // Docente (nombre completo)
                { "data": 6 }, // Fecha
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="maestria_abierto/edit/${id}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="maestria_abierto/delete/${id}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
    const formMaestriaAbierto = $('#formMaestriaAbierto'); // Asume que el ID de tu formulario es 'formMaestriaAbierto'
    if (formMaestriaAbierto.length) {
        // Inicializar Flatpickr para el campo de fecha
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            altInput: true, // Muestra una entrada alternativa formateada para el usuario
            altFormat: "d F, Y", // Formato amigable para el usuario (ej. 23 Julio, 2025)
            locale: "es", // Establece el idioma a español
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        // Inicializar CKEditor para el campo nombre_carta
        ClassicEditor
            .create(document.querySelector('#nombre_carta'), {
                language: 'es'
            })
            .then(editor => {
                window.nombreCartaEditor = editor; // Guarda la instancia del editor
                // Si estamos en modo edición, carga el contenido HTML
                const nombreCartaContent = formMaestriaAbierto.data('nombre-carta');
                if (nombreCartaContent) {
                    editor.setData(nombreCartaContent);
                }
            })
            .catch(error => {
                console.error('Error al inicializar el editor de nombre_carta:', error);
            });

        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('maestria_id', 'maestria', 'maestria_current');
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
            fillSelect('docente_id', 'docente', 'docente_current', 'CONCAT(primer_apellido, ", ", primer_nombre)');
        }

        // Validación del formulario antes de enviar
        formMaestriaAbierto.on('submit', function (event) {
            const numero = $('#numero').val().trim();
            const maestriaId = $('#maestria_id').val();
            const sedeId = $('#sede_id').val();
            const estatusId = $('#estatus_id').val();
            const docenteId = $('#docente_id').val();
            const fecha = $('#fecha').val().trim();
            const nombreCartaContent = window.nombreCartaEditor ? window.nombreCartaEditor.getData().trim() : ''; // Obtener contenido de CKEditor

            if (numero === '' || !maestriaId || !sedeId || !estatusId || !docenteId || fecha === '' || nombreCartaContent === '') {
                // Usar showFlashMessage si está disponible, de lo contrario alert
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', 'Por favor, complete todos los campos obligatorios.');
                } else {
                    alert('Por favor, complete todos los campos obligatorios.');
                }
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
