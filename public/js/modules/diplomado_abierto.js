// app/Modules/DiplomadoAbierto/Views/js/diplomado_abierto.js
console.log('diplomado_abierto.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const diplomadoAbiertoTable = $('#diplomadoAbiertoTable');
    if (diplomadoAbiertoTable.length) {
        diplomadoAbiertoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}diplomado_abierto/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de diplomados abiertos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Diplomado (nombre)
                { "data": 3 }, // Sede (nombre)
                { "data": 4 }, // Estatus (nombre)
                { "data": 5 }, // Fecha Inicio
                { "data": 6 }, // Fecha Fin
                { // Columna para Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="diplomado_abierto/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="diplomado_abierto/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
                        `;
                    }
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
    const formDiplomadoAbierto = $('#formDiplomadoAbierto'); // Asume que el ID de tu formulario es 'formDiplomadoAbierto'
    if (formDiplomadoAbierto.length) {
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
        ClassicEditor
            .create(document.querySelector('#nombre_carta'), {
                language: 'es'
            })
            .then(editor => {
                window.nombreCartaEditor = editor;
            })
            .catch(error => {
                console.error('Error al inicializar el editor de nombre_carta:', error);
            });


        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('diplomado_id', 'diplomado', 'diplomado_current');
            fillSelect('sede_id', 'sede', 'sede_current');
            fillSelect('estatus_id', 'estatus', 'estatus_current');
        }

        // Validación del formulario antes de enviar
        formDiplomadoAbierto.on('submit', function (event) {
            const numero = $('#numero').val().trim();
            const diplomadoId = $('#diplomado_id').val();
            const sedeId = $('#sede_id').val();
            const estatusId = $('#estatus_id').val();
            const fechaInicio = $('#fecha_inicio').val().trim();
            const fechaFin = $('#fecha_fin').val().trim();
            const nombreCartaContent = (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.nombre_carta) ? CKEDITOR.instances.nombre_carta.getData().trim() : '';

            // TODO validaciones
            // if (numero === '' || !diplomadoId || !sedeId || !estatusId || fechaInicio === '' || fechaFin === '' || nombreCartaContent === '') {
            //     alert('Por favor, complete todos los campos obligatorios.');
            //     event.preventDefault(); // Detiene el envío del formulario
            // }
        });
    }
});
