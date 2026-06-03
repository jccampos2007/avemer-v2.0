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
        let mensajeEditor;
        ClassicEditor
            .create(document.querySelector('#mensaje'), {
                language: 'es',
                toolbar: CKEDITOR_TOOLBAR_OPTIONS
            })
            .then(editor => {
                mensajeEditor = editor;
                const currentMensaje = formMensajes.data('mensaje');
                if (currentMensaje) {
                    editor.setData(currentMensaje);
                }
            })
            .catch(error => {
                console.error('Error al inicializar el editor de mensaje:', error);
            });

        // Manejo del envío del formulario
        formMensajes.on('submit', function (event) {
            const mensajeContent = mensajeEditor ? mensajeEditor.getData().trim() : '';
            const tituloVal = $('#titulo').val().trim();

            if (tituloVal === '' || mensajeContent === '') {
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
            "responsive": true, // Habilitar diseño responsivo
            "dom": 'lBfrtip', // Definir ubicación de los elementos de control (B = Botones)
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span class="export-label"> Exportar a Excel</span>',
                    className: 'buttons-excel',
                    title: 'Listado de Mensajes',
                    exportOptions: {
                        columns: [1] // Exportar únicamente Título
                    },
                    action: newExportAction
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span class="export-label"> Exportar a PDF</span>',
                    className: 'buttons-pdf',
                    title: 'Listado de Mensajes',
                    exportOptions: {
                        columns: [1] // Exportar únicamente Título
                    },
                    action: newExportAction,
                    customize: function (doc) {
                        // Personalizaciones estéticas básicas para el PDF
                        doc.content[1].table.widths = ['100%'];
                        doc.styles.tableHeader.fillColor = '#1e3a8a'; // Color azul corporativo
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                }
            ],
            "ajax": {
                "url": BASE_URL_JS + "mensajes/data",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.log("Error en la solicitud AJAX de DataTables:", error, thrown);
                    showFlashMessage('error', 'Error al cargar los datos de mensajes.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // ID
                { "data": 1 }, // Título
                {
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": "1%",
                    "className": "actions-column",
                    "render": function (data, type, row) {
                        // Añadimos una clase 'btn-delete' para identificar el botón de borrar
                        return `
                        <a href="mensajes/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                        <a href="mensajes/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
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
        tableElement.on('click', '.btn-action-delete', function (e) {
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