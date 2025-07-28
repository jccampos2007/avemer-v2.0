// app/Modules/Diplomado/Views/js/diplomado.js
console.log('diplomado.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const diplomadoTable = $('#diplomadoTable');
    if (diplomadoTable.length) {
        diplomadoTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}diplomado/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de diplomados. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 4 }, // Siglas
                { "data": 2 }, // Nombre
                { "data": 3 }, // Descripción
                { // Columna para Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                        <a href="diplomado/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="diplomado/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
        diplomadoTable.on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                $.ajax({
                    url: `${BASE_URL_JS}diplomado/delete/${id}`,
                    method: 'POST', // Usar POST para la eliminación AJAX
                    success: function (response) {
                        // Asume que la respuesta es JSON con {success: true, message: "..."}
                        if (response.success) {
                            alert(response.message);
                            diplomadoTable.DataTable().ajax.reload(); // Recargar la tabla
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
    const formDiplomado = $('#formDiplomado'); // Asume que el ID de tu formulario es 'formDiplomado'
    if (formDiplomado.length) {
        // Llenar selects con la función reusable 'fillSelect'
        if (typeof fillSelect === 'function') {
            fillSelect('duracion_id', 'duracion', 'duracion_current');
        }

        // Validación del formulario antes de enviar
        formDiplomado.on('submit', function (event) {
            const duracionId = $('#duracion_id').val();
            const nombre = $('#nombre').val().trim();
            const siglas = $('#siglas').val().trim();
            const costo = $('#costo').val();
            const inicial = $('#inicial').val();
            const descripcion = $('#descripcion').val();

            // if (duracionId === '' || nombre === '' || descripcion === '' || siglas === '') {
            //     alert('Por favor, complete todos los campos obligatorios y asegúrese de que Costo e Inicial sean números válidos.');
            //     event.preventDefault(); // Detiene el envío del formulario
            // }
        });
    }
});
