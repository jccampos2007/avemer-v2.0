// app/Modules/CursoControl/Views/js/curso_control.js
console.log('curso_control.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const cursoControlTable = $('#cursoControlTable');
    if (cursoControlTable.length) {
        cursoControlTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}curso_control/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de control de cursos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Taller Control (número)
                { "data": 2 }, // Docente (nombre completo)
                { "data": 3 }, // Tema
                { "data": 4 }, // Fecha
                { // Columna de Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                            <a href="curso_control/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                            <a href="curso_control/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
        cursoControlTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                $.ajax({
                    url: `${BASE_URL_JS}curso_control/delete/${id}`,
                    method: 'POST', // Usar POST para la eliminación AJAX
                    success: function (response) {
                        // Asume que la respuesta es JSON con {success: true, message: "..."}
                        if (response.success) {
                            alert(response.message);
                            cursoControlTable.DataTable().ajax.reload(); // Recargar la tabla
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
    const formCursoControl = $('#formCursoControl'); // Asume que el ID de tu formulario es 'formCursoControl'
    if (formCursoControl.length) {
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

        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('curso_abierto_id', 'curso_abierto', formCursoControl.data('curso-abierto-id'), 'numero', 'estatus_id');
            fillSelect('docente_id', 'docente', formCursoControl.data('docente-id'), 'CONCAT(primer_apellido, ", ", primer_nombre)', 'estatus_activo_id');
        }
    }
});
