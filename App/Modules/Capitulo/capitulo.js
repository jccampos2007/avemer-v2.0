// app/Modules/Capitulo/Views/js/capitulo.js
console.log('capitulo.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const capituloTable = $('#capituloTable');
    const diplomadoFilterSelect = $('#diplomado_filter_id'); // El select para filtrar diplomados
    const createCapituloBtn = $('#createCapituloBtn'); // Botón para crear nuevo capítulo

    let dataTableInstance = null; // Variable para almacenar la instancia de DataTables

    // Función para inicializar o recargar DataTables
    function initializeCapituloDataTable(diplomadoId = null) {
        if (dataTableInstance) {
            dataTableInstance.destroy(); // Destruir la instancia existente
            capituloTable.find('tbody').empty(); // Limpiar el cuerpo de la tabla
        }

        if (!diplomadoId) {
            // Mostrar un mensaje si no hay diplomado seleccionado
            capituloTable.find('tbody').html('<tr><td colspan="8" class="text-center py-4 text-gray-500">Seleccione un Diplomado para ver sus Capítulos.</td></tr>');
            createCapituloBtn.addClass('opacity-50 cursor-not-allowed').attr('href', '#'); // Deshabilitar botón
            return;
        }

        // Habilitar botón de crear
        createCapituloBtn.removeClass('opacity-50 cursor-not-allowed').attr('href', `${BASE_URL_JS}capitulo/create/${diplomadoId}`);

        dataTableInstance = capituloTable.DataTable({
            "processing": true,
            "serverSide": true, // Habilitar procesamiento del lado del servidor
            "responsive": true, // Habilitar diseño responsivo
            "ajax": {
                "url": `${BASE_URL_JS}capitulo/data`, // Ruta para obtener los datos
                "type": "POST", // Usar POST para DataTables server-side
                "data": function (d) {
                    d.diplomado_id = diplomadoId; // Enviar el ID del diplomado
                },
                "error": function (xhr, error, thrown) {
                    console.error("Error en la solicitud AJAX de DataTables:", error, thrown);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert('Error al cargar los datos de capítulos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Nombre
                { "data": 3 }, // Descripción
                { "data": 4 }, // Activo
                { // Columna para Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                        <a href="capitulo/edit/${row[0]}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                        <a href="capitulo/delete/${row[0]}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
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
        capituloTable.off('click', '.delete-btn').on('click', '.delete-btn', function () { // Usar .off().on() para evitar duplicados
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                $.ajax({
                    url: `${BASE_URL_JS}capitulo/delete/${id}`,
                    method: 'POST', // Usar POST para la eliminación AJAX
                    success: function (response) {
                        // Asume que la respuesta es JSON con {success: true, message: "..."}
                        if (response.success) {
                            alert(response.message);
                            dataTableInstance.ajax.reload(); // Recargar la tabla
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

    // Llenar el select de diplomados y manejar el filtro

    if (diplomadoFilterSelect.length && typeof fillSelect === 'function') {
        const urlParams = new URLSearchParams(window.location.search);
        const initialDiplomadoId = urlParams.get('diplomado_id');

        fillSelect('diplomado_filter_id', 'diplomado', initialDiplomadoId);

        diplomadoFilterSelect.on('change', function () {
            const selectedDiplomadoId = $(this).val();
            initializeCapituloDataTable(selectedDiplomadoId);

            // Actualizar la URL para reflejar el filtro (opcional, para compartir enlaces)
            const newUrl = new URL(window.location.href);
            if (selectedDiplomadoId) {
                newUrl.searchParams.set('diplomado_id', selectedDiplomadoId);
            } else {
                newUrl.searchParams.delete('diplomado_id');
            }
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);
        });
    }


    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formCapitulo = $('#formCapitulo'); // Asume que el ID de tu formulario es 'formCapitulo'
    if (formCapitulo.length) {
        // Inicializar CKEditor 4 para el campo descripcion
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('descripcion', {
                language: 'es',
                // Puedes personalizar la barra de herramientas aquí si no quieres la 'full'
                // toolbar: [ ... ]
            });

            // Pre-llenar CKEditor 4 en modo edición
            const currentDescripcion = formCapitulo.data('descripcion');
            if (currentDescripcion) {
                CKEDITOR.on('instanceReady', function (event) {
                    if (event.editor.name === 'descripcion') {
                        event.editor.setData(currentDescripcion);
                    }
                });
            }
        } else {
            console.error('CKEDITOR no está definido. Asegúrate de que la librería de CKEditor 4 esté cargada correctamente.');
        }
    }
});
