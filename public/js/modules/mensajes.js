// app/Modules/Mensajes/Views/js/mensajes.js
// Archivo JavaScript para el módulo de mensajes
$(document).ready(function () {
    console.log("mensajes.js cargado.");

    // El ID del formulario en tu PHP es 'formmensajes'
    const formMensajes = $('#formmensajes');

    if (formMensajes.length > 0) {
        // ---------------------------------------------------
        // Lógica para la vista de FORMULARIO (Crear/Editar)
        // ---------------------------------------------------
        
        // Desactivar la validación nativa del navegador para evitar errores con campos ocultos
        formMensajes.attr('novalidate', true);

        // Inicializar CKEditor para el campo mensaje
        ClassicEditor
            .create(document.querySelector('#mensaje'), {
                language: 'es'
            })
            .then(editor => {
                window.mensajeEditor = editor;
                console.log('Editor de mensaje inicializado correctamente.');
            })
            .catch(error => {
                console.error('Error al inicializar el editor de mensaje:', error);
            });

        // Manejo del envío del formulario
        formMensajes.on('submit', function (event) {
            if (window.mensajeEditor) {
                window.mensajeEditor.updateSourceElement();
            }

            const mensajeContent = window.mensajeEditor ? window.mensajeEditor.getData() : '';
            const tituloVal = $('#titulo').val().trim();

            if (tituloVal === '' || mensajeContent.trim() === '') {
                const msg = 'Por favor, complete los campos obligatorios (Título y Mensaje).';
                if (typeof showFlashMessage === 'function') {
                    showFlashMessage('error', msg);
                } else {
                    showFlashMessage('error', msg);
                }
                
                event.preventDefault();
                return false;
            }
        });

    } else {
        // ---------------------------------------------------
        // Lógica para la vista de LISTADO (DataTables)
        // ---------------------------------------------------
        const tableElement = $('#mensajesTable');
        
        var mensajesTable = tableElement.DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ajax": {
                "url": BASE_URL_JS + "mensajes/data",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    showFlashMessage('error', 'Error al cargar los datos de mensajes.');
                }
            },
            "columns": [
                { "data": 0 }, // ID
                { "data": 1 }, // Título
                {
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function (data, type, row) {
                        // Añadimos una clase 'btn-delete' para identificar el botón de borrar
                        return `
                        <a href="mensajes/edit/${row[0]}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>
                        <a href="mensajes/delete/${row[0]}" class="btn btn-default btn-delete" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            }
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN
        // Usamos delegación de eventos porque los botones se crean dinámicamente
        tableElement.on('click', '.btn-delete', function (e) {
            e.preventDefault(); // Evita que el enlace se abra inmediatamente
            
            const urlEliminar = $(this).attr('href');
            
            // Usamos confirmación nativa. Si prefieres SweetAlert2 o un modal personalizado, 
            // solo debes cambiar esta lógica.
            if (confirm('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')) {
                window.location.href = urlEliminar;
            }
        });
    }
});