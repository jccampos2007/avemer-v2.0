// app/Modules/Correo/Views/js/correo.js
console.log('correo.js cargado.');

$(document).ready(function () {
    const formCorreo = $('#formCorreo');
    const tipoOfertaAcademicaIdInput = $('#tipo_oferta_academica_id');
    const ofertaAcademicaSelect = $('#oferta_academica_id');
    const mensajesSelect = $('#mensaje_id'); // Asegúrate de que este ID sea el correcto
    const correosListTable = $('#correosListTable');
    const correosListMessage = $('#correos-list-message');
    const generateDebtModal = $('#generateDebtModal');
    const closeDebtModalBtn = $('#closeDebtModal');
    const studentsListTable = $('#studentsListTable');
    const studentsListMessage = $('#students-list-message');
    const selectAllStudentsCheckbox = $('#selectAllStudents');
    const confirmGenerateDebtBtn = $('#confirmGenerateDebtBtn');

    let correosDataTable = null; // Variable para la instancia de DataTables
    let studentsDataTable = null; // Variable para la instancia de DataTables de alumnos
    let currentCorreoIdForDebt = null;
    let currentCorreoMontoForDebt = null;
    let currentTipoOfertaIdForDebt = null;
    let currentOfertaIdForDebt = null;

    // Log para depuración: Verificar valores iniciales de los data-atributos
    console.log('Initial Tipo Oferta ID (from form data-attribute):', formCorreo.data('tipo-oferta-academica-id'));
    console.log('Initial Oferta Académica ID (from form data-attribute):', formCorreo.data('oferta-academica-id'));


    // Función auxiliar para mostrar alertas, usando showFlashMessage si está disponible, o showFlashMessage('error', ) por defecto.
    function showAlert(message, type = 'error') {
        if (typeof showFlashMessage === 'function') {
            showFlashMessage(type, message);
        } else {
            showFlashMessage('error', message);
        }
    }

    // Función para mostrar mensajes en la sección de la lista de correos
    function showCorreosListMessage(message, type = 'info') {
        correosListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    // Función para mostrar mensajes en la sección de la lista de alumnos del modal
    function showStudentsListMessage(message, type = 'info') {
        studentsListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    // Función para cargar las ofertas académicas en el select
    function loadAcademicOffers(typeId, selectedId = null) {
        ofertaAcademicaSelect.empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: `${BASE_URL_JS}correo/getAcademicOffersByType`,
            type: 'GET',
            data: { type_id: typeId },
            dataType: 'json',
            success: function (response) {
                ofertaAcademicaSelect.empty();
                ofertaAcademicaSelect.append('<option value="">Seleccione una Oferta Académica</option>');
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        const isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        ofertaAcademicaSelect.append(`<option value="${item.id}" ${isSelected}>${item.nombre}</option>`);
                    });
                } else {
                    ofertaAcademicaSelect.append('<option value="">No hay ofertas disponibles para este tipo</option>');
                }
                // Si ya hay una oferta académica seleccionada (ej. en edición), cargar las correos
                if (selectedId) {
                    loadCorreosList(typeId, selectedId);
                } else {
                    // Limpiar la tabla si no hay oferta seleccionada
                    if (correosDataTable) {
                        correosDataTable.clear().draw();
                    }
                    showCorreosListMessage('Seleccione una oferta académica para ver las correos asociadas.', 'info');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar ofertas académicas:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                ofertaAcademicaSelect.empty().append('<option value="">Error al cargar ofertas</option>');
                showAlert('Error al cargar las ofertas académicas.', 'error');
            }
        });
    }

        // Función para cargar los mensajes en el select
    function loadMensajesOffers(selectedId = null) {        
        mensajesSelect.empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: `${BASE_URL_JS}correo/getMensajes`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                
                mensajesSelect.empty();
                mensajesSelect.append('<option value="">Seleccione un Mensaje</option>');
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        const isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        
                        mensajesSelect.append(`<option value="${item.id}" ${isSelected}>${item.titulo}</option>`);
                    });
                } else {
                    mensajesSelect.append('<option value="">No hay mensajes disponibles</option>');
                }
                
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar ofertas académicas:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                ofertaAcademicaSelect.empty().append('<option value="">Error al cargar ofertas</option>');
                showAlert('Error al cargar las ofertas académicas.', 'error');
            }
        });
    }

    // Función para cargar y mostrar la lista de correos
    function loadCorreosList(tipoOfertaId, ofertaId) {
        if (!tipoOfertaId || !ofertaId) {
            if (correosDataTable) {
                correosDataTable.clear().draw();
            }
            showCorreosListMessage('Seleccione una oferta académica para ver las correos asociadas.', 'info');
            return;
        }

        showCorreosListMessage('Cargando correos...', 'info');

        $.ajax({
            url: `${BASE_URL_JS}correo/getCorreosByOfferData`,
            type: 'GET',
            data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId },
            dataType: 'json',
            success: function (response) {
                if (correosDataTable) {
                    correosDataTable.destroy(); // Destruir la instancia existente
                }

                if (response.success && response.data.length > 0) {
                    correosListMessage.addClass('hidden'); // Ocultar mensaje si hay datos
                    correosDataTable = correosListTable.DataTable({
                        "data": response.data,
                        "responsive": false,
                        "searching": false, // No se necesita búsqueda en esta tabla específica
                        "paging": false,    // No se necesita paginación
                        "info": false,      // No se necesita información de paginación
                        "columns": [
                                {
                                "data": null,
                                "orderable": false,
                                "searchable": false,
                                "render": function (data, type, row) {
                                    return `<input type="checkbox" class="correo-checkbox" value="${row.correo}" checked>`;
                                }
                            },
                            { "data": "correo" },
                            { "data": "ci_pasapote" },
                            { "data": "nombre" },
                            { "data": "nombre_oferta" }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                        },
                        "autoWidth": false
                    });
                } else {
                    showCorreosListMessage('No hay correos generadas para esta oferta académica.', 'info');
                    if (correosDataTable) {
                        correosDataTable.clear().draw();
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar la lista de correos:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                showAlert('Error al cargar las correos. Por favor, intente de nuevo.', 'error');
                if (correosDataTable) {
                    correosDataTable.clear().draw();
                }
            }
        });
    }

    // Manejador de clic para las pestañas
    $('.tab-button').on('click', function () {
        const tabId = $(this).data('tab-id');

        // Actualizar el valor del input oculto
        tipoOfertaAcademicaIdInput.val(tabId);

        // Actualizar clases de las pestañas para el estilo activo
        $('.tab-button').removeClass('bg-blue-600 text-white font-bold').addClass('bg-gray-200 text-gray-700');
        $(this).addClass('bg-blue-600 text-white font-bold').removeClass('bg-gray-200 text-gray-700');

        // Cargar las ofertas académicas para la pestaña seleccionada
        loadAcademicOffers(tabId);
        loadMensajesOffers();
    });

    // Manejador de cambio para el select de oferta_academica_id
    ofertaAcademicaSelect.on('change', function () {
        const selectedOfertaId = $(this).val();
        const selectedTipoOfertaId = tipoOfertaAcademicaIdInput.val();
        loadCorreosList(selectedTipoOfertaId, selectedOfertaId);
    });

    // Inicializar la pestaña activa y cargar las ofertas al cargar la página
    if (formCorreo.length) {
        const initialTipoOfertaId = formCorreo.data('tipo-oferta-academica-id'); // Volver a obtener por si acaso
        const initialOfertaAcademicaId = formCorreo.data('oferta-academica-id'); // Volver a obtener por si acaso

        const currentActiveTabButton = $(`.tab-button[data-tab-id="${initialTipoOfertaId}"]`);
        if (currentActiveTabButton.length) {
            currentActiveTabButton.trigger('click');
            // Después de cargar las opciones, pre-seleccionar si es un formulario de edición
            if (initialOfertaAcademicaId) {
                // Pequeño retraso para asegurar que las opciones se han cargado
                // La carga de la lista de correos se hará en el callback de loadAcademicOffers
                setTimeout(() => {
                    ofertaAcademicaSelect.val(initialOfertaAcademicaId).trigger('change');
                }, 200);
            }
        } else {
            // Si no hay un tipo pre-seleccionado, activa la primera pestaña (Curso)
            $('.tab-button[data-tab-id="1"]').trigger('click');
        }

        $('#sendCheckedEmailsBtn').on('click', function () {
            const correosSeleccionados = $('.correo-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            // console.log(correosSeleccionados);
            
            if (correosSeleccionados.length === 0) {
                showAlert('Por favor selecciona al menos un correo.', 'info');
                return;
            }

            const mensajeId = $('#mensaje_id').val();
            if (!mensajeId) {
                showAlert('Selecciona un mensaje antes de enviar.', 'error');
                return;
            }

            $.ajax({
                url: `${BASE_URL_JS}correo/sendChecked`,
                type: 'POST',
                data: {
                    correos: correosSeleccionados,
                    mensaje_id: mensajeId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                    } else {
                        showAlert('Error: ' + response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al enviar correos:', error);
                    showAlert('Error al enviar los correos. Intenta de nuevo.', 'error');
                }
            });
        });

        // Inicializar Flatpickr para los campos de fecha
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fecha_vencimiento", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F, Y",
                locale: "es",
            });
            flatpickr.localize(flatpickr.l10ns.es);
        } else {
            console.warn("Flatpickr no está cargado. Asegúrate de incluir su CDN.");
        }

        // Manejador para el botón de eliminar correo (delegado)
        $(document).on('click', '.delete-correo-btn', function (e) {
            e.preventDefault();
            const correoId = $(this).data('id');
            if (confirm('¿Está seguro de que desea eliminar esta correo?')) {
                $.ajax({
                    url: `${BASE_URL_JS}correo/delete/${correoId}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            const currentTipoOfertaId = tipoOfertaAcademicaIdInput.val();
                            const currentOfertaId = ofertaAcademicaSelect.val();
                            loadCorreosList(currentTipoOfertaId, currentOfertaId);
                        } else {
                            showAlert('Error al eliminar: ' + response.message, 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al eliminar la correo:', error);
                        console.error('Respuesta del servidor:', xhr.responseText);
                        showAlert('Error al intentar eliminar la correo.', 'error');
                    }
                });
            }
        });

        // --- Lógica del Modal de Generación de Deuda ---

        // Cerrar modal
        closeDebtModalBtn.on('click', function () {
            generateDebtModal.addClass('hidden');
            if (studentsDataTable) {
                studentsDataTable.destroy(); // Destruir la instancia de DataTables de alumnos al cerrar
                studentsListTable.find('tbody').empty(); // Limpiar el cuerpo de la tabla
            }
            selectAllStudentsCheckbox.prop('checked', false); // Desmarcar "Seleccionar todos"
        });

        // Seleccionar/Deseleccionar todos los alumnos
        selectAllStudentsCheckbox.on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox').prop('checked', isChecked);
        });

        // Manejador para el botón de confirmar generación de deuda
        confirmGenerateDebtBtn.on('click', function () {
            const selectedAlumnoIds = [];
            $('.student-checkbox:checked').each(function () {
                selectedAlumnoIds.push($(this).data('alumno-id'));
            });

            if (selectedAlumnoIds.length === 0) {
                showAlert('Por favor, seleccione al menos un alumno para generar la deuda.', 'info');
                return;
            }

            if (!currentCorreoIdForDebt || !currentCorreoMontoForDebt || !currentTipoOfertaIdForDebt || !currentOfertaIdForDebt) {
                showAlert('Error: No se pudo obtener la información de la correo para generar la deuda.', 'error');
                return;
            }

            // Deshabilitar botón para evitar múltiples envíos
            confirmGenerateDebtBtn.prop('disabled', true).text('Generando Deuda...');

            $.ajax({
                url: `${BASE_URL_JS}correo/generateDebt`,
                type: 'POST',
                data: {
                    correo_id: currentCorreoIdForDebt,
                    alumno_ids: selectedAlumnoIds,
                    monto_correo: currentCorreoMontoForDebt,
                    // También puedes enviar tipo_oferta_id y oferta_academica_id si es necesario en el backend
                    tipo_oferta_id: currentTipoOfertaIdForDebt,
                    oferta_id: currentOfertaIdForDebt
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        generateDebtModal.addClass('hidden'); // Cerrar modal
                        // Recargar la lista de correos para actualizar el estado 'generado'
                        loadCorreosList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaSelect.val());
                    } else {
                        showAlert('Error al generar deuda: ' + response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al generar la deuda:', error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    showAlert('Error al procesar la solicitud de generación de deuda.', 'error');
                },
                complete: function () {
                    confirmGenerateDebtBtn.prop('disabled', false).text('Generar Deuda Seleccionados'); // Habilitar botón
                }
            });
        });
    }
});
