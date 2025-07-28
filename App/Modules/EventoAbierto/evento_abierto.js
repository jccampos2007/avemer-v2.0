// app/Modules/EventoAbierto/Views/js/evento_abierto.js
console.log('evento_abierto.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const eventoAbiertoTable = $('#eventoAbiertoTable');
    if (eventoAbiertoTable.length) {
        eventoAbiertoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}evento_abierto/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de eventos abiertos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Evento (nombre)
                { "data": 3 }, // Sede (nombre)
                { "data": 4 }, // Estatus (nombre)
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="evento_abierto/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="evento_abierto/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
        eventoAbiertoTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                $.ajax({
                    url: `${BASE_URL_JS}evento_abierto/delete/${id}`,
                    method: 'POST', // Usar POST para la eliminación AJAX
                    success: function (response) {
                        // Asume que la respuesta es JSON con {success: true, message: "..."}
                        if (response.success) {
                            alert(response.message);
                            eventoAbiertoTable.DataTable().ajax.reload(); // Recargar la tabla
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
    const formEventoAbierto = $('#formEventoAbierto'); // Asume que el ID de tu formulario es 'formEventoAbierto'
    if (formEventoAbierto.length) {
        // Inicializar Flatpickr para los campos de fecha
        flatpickr("#fecha_inicio", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F, Y",
            locale: "es",
        });
        flatpickr("#fecha_fin", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F, Y",
            locale: "es",
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        // Inicializar CKEditor para el campo nombre_carta
        let nombreCartaEditor; // Variable para almacenar la instancia del editor
        ClassicEditor
            .create(document.querySelector('#nombre_carta'), {
                language: 'es',
                toolbar: CKEDITOR_TOOLBAR_OPTIONS
            })
            .then(editor => {
                nombreCartaEditor = editor;
                // Si estamos en modo edición, pre-llenar CKEditor
                const currentNombreCarta = formEventoAbierto.data('nombre-carta');
                if (currentNombreCarta) {
                    editor.setData(currentNombreCarta);
                }
            })
            .catch(error => {
                console.error('Error al inicializar el editor de nombre_carta:', error);
            });

        if (typeof fillSelect === 'function') {
            fillSelect('evento_id', 'evento', 'evento_current');
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
        }

        // Validación del formulario antes de enviar
        formEventoAbierto.on('submit', function (event) {
            const numero = $('#numero').val().trim();
            const eventoId = $('#evento_id').val();
            const sedeId = $('#sede_id').val();
            const estatusId = $('#estatus_id').val();
            const fechaInicio = $('#fecha_inicio').val().trim();
            const fechaFin = $('#fecha_fin').val().trim();
            const nombreCartaContent = nombreCartaEditor ? nombreCartaEditor.getData().trim() : ''; // Obtener contenido de CKEditor

            if (numero === '' || !eventoId || !sedeId || !estatusId || fechaInicio === '' || fechaFin === '' || nombreCartaContent === '') {
                alert('Por favor, complete todos los campos obligatorios.');
                event.preventDefault(); // Detiene el envío del formulario
            }
        });
    }
});
