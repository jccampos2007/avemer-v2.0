// app/Modules/Capitulo/Views/js/capitulo.js
console.log('capitulo.js cargado.');

$(document).ready(function () {
    // ---------------------------------------------------
    // Lógica para la vista de LISTADO (DataTables)
    // ---------------------------------------------------
    const capituloTable = $('#capituloTable');
    const createCapituloBtn = $('#createCapituloBtn');

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
        createCapituloBtn.removeClass('opacity-50 cursor-not-allowed').attr('href', `${BASE_URL_JS}capitulo/create`);

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
                    showFlashMessage('error', 'Error al cargar los datos de capítulos. Por favor, revisa la consola para más detalles.');
                }
            },
            "columns": [
                {
                    data: 0,
                    visible: false,
                    searchable: false
                }, // ID
                { "data": 1 }, // Número
                { "data": 2 }, // Nombre
                { "data": 4 }, // Activo
                { // Columna para Acciones
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "width": "1%",
                    "className": "actions-column",
                    "render": function (data, type, row) {
                        const id = row[0]; // El ID está en la primera columna (índice 0)
                        return `
                        <a href="capitulo/edit/${row[0]}" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                        <a href="capitulo/delete/${row[0]}" class="btn-action btn-action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                    `;
                    }
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "autoWidth": false
        });

        // MANEJADOR DE ELIMINACIÓN CON CONFIRMACIÓN (SweetAlert2)
        capituloTable.off("click", ".btn-action-delete").on("click", ".btn-action-delete", function (e) {
            e.preventDefault();
            const urlEliminar = $(this).attr("href");

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: urlEliminar,
                        type: "GET",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        success: function (response) {
                            let res = typeof response === "string" ? JSON.parse(response) : response;
                            if (res.success) {
                                Swal.fire(
                                    "¡Eliminado!",
                                    res.message,
                                    "success"
                                );
                                dataTableInstance.ajax.reload(null, false);
                            } else {
                                Swal.fire(
                                    "Error",
                                    res.message,
                                    "error"
                                );
                            }
                        },
                        error: function (xhr) {
                            Swal.fire(
                                "Error",
                                "Ocurrió un error al procesar la solicitud.",
                                "error"
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    }

    if ($('#diplomado_filter_autocomplete').length) {
        setupAutocomplete('diplomado_filter_autocomplete', 'diplomado_filter_id', 'diplomado', 3, {
            displayColumn: "CONCAT(siglas, ' - ', nombre)"
        });

        var urlParams = new URLSearchParams(window.location.search);
        var initialDiplomadoId = urlParams.get('diplomado_id');
        if (initialDiplomadoId) {
            $.ajax({
                url: BASE_URL_JS + 'api/search/diplomado',
                dataType: "json",
                data: { term: initialDiplomadoId, displayColumn: "CONCAT(siglas, ' - ', nombre)" },
                success: function (data) {
                    if (data && data.length > 0) {
                        $('#diplomado_filter_autocomplete').val(data[0].label);
                        $('#diplomado_filter_id').val(initialDiplomadoId);
                        initializeCapituloDataTable(initialDiplomadoId);
                    }
                }
            });
        }

        $('#diplomado_filter_autocomplete').on('autocompleteselect', function (event, ui) {
            initializeCapituloDataTable(ui.item.id);
            var newUrl = new URL(window.location.href);
            newUrl.searchParams.set('diplomado_id', ui.item.id);
            window.history.pushState({ path: newUrl.href }, '', newUrl.href);
        });

        $('#diplomado_filter_autocomplete').on('autocompletechange', function (event, ui) {
            if (!ui.item) {
                initializeCapituloDataTable(null);
                var newUrl = new URL(window.location.href);
                newUrl.searchParams.delete('diplomado_id');
                window.history.pushState({ path: newUrl.href }, '', newUrl.href);
            }
        });
    }

    // ---------------------------------------------------
    // Lógica para la vista de FORMULARIO (Crear/Editar)
    // ---------------------------------------------------
    const formCapitulo = $('#formCapitulo');
    if (formCapitulo.length) {
        // Inicializar autocomplete para seleccionar diplomado
        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete('diplomado_autocomplete', 'diplomado_id', 'diplomado', 3, {
                displayColumn: "CONCAT(siglas, ' - ', nombre)"
            });
        }

        // Enviar formulario via AJAX solo en modo creación (sin hidden id)
        if (!$('input[name="id"]', formCapitulo).length) {
        formCapitulo.on('submit', function (e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#numero').val('');
                        $('#nombre').val('');
                        $('#descripcion').val('');
                        $('#orden').val('');
                        $('#activo').prop('checked', true);
                        $('#diplomado_autocomplete').val('');
                        $('#diplomado_id').val('');

                        const diplomadoId = response.data ? response.data.diplomado_id : null;
                        if (diplomadoId && typeof initializeCapituloDataTable === 'function') {
                            initializeCapituloDataTable(diplomadoId);
                        }

                        showFlashMessage('success', response.message);
                    } else {
                        showFlashMessage('error', response.message);
                    }
                },
                error: function (xhr) {
                    let msg = 'Error al procesar la solicitud.';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.message) msg = res.message;
                    } catch (e) {}
                    showFlashMessage('error', msg);
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Guardar Capítulo');
                }
            });
        });

        // Sincronizar autocomplete del formulario con la lista
        $('#diplomado_autocomplete').on('autocompleteselect', function (event, ui) {
            if (typeof initializeCapituloDataTable === 'function') {
                initializeCapituloDataTable(ui.item.id);
            }
        });
        }
    }
});
