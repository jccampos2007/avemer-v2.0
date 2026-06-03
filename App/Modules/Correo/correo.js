console.log('correo.js cargado.');

$(document).ready(function () {
    const formCorreo = $('#formCorreo');
    const tipoOfertaAcademicaIdInput = $('#tipo_oferta_academica_id');
    const ofertaAcademicaInput = $('#oferta_academica_nombre');
    const ofertaAcademicaHidden = $('#oferta_academica_id');
    const mensajesSelect = $('#mensaje_id');
    const correosListTable = $('#correosListTable');
    const correosListMessage = $('#correos-list-message');
    const generateDebtModal = $('#generateDebtModal');
    const closeDebtModalBtn = $('#closeDebtModal');
    const studentsListTable = $('#studentsListTable');
    const studentsListMessage = $('#students-list-message');
    const selectAllStudentsCheckbox = $('#selectAllStudents');
    const confirmGenerateDebtBtn = $('#confirmGenerateDebtBtn');

    let correosDataTable = null;
    let studentsDataTable = null;
    let currentCorreoIdForDebt = null;
    let currentCorreoMontoForDebt = null;
    let currentTipoOfertaIdForDebt = null;
    let currentOfertaIdForDebt = null;
    let offerSourceData = [];

    console.log('Initial Tipo Oferta ID (from form data-attribute):', formCorreo.data('tipo-oferta-academica-id'));
    console.log('Initial Oferta Académica ID (from form data-attribute):', formCorreo.data('oferta-academica-id'));

    function showAlert(message, type = 'error') {
        if (typeof showFlashMessage === 'function') {
            showFlashMessage(type, message);
        } else {
            showFlashMessage('error', message);
        }
    }

    function showCorreosListMessage(message, type = 'info') {
        correosListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    function showStudentsListMessage(message, type = 'info') {
        studentsListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    function loadAcademicOffers(typeId, selectedId = null) {
        ofertaAcademicaInput.val('');
        ofertaAcademicaHidden.val('');

        $.ajax({
            url: `${BASE_URL_JS}correo/getAcademicOffersByType`,
            type: 'GET',
            data: { type_id: typeId },
            dataType: 'json',
            success: function (response) {
                offerSourceData = response.success && response.data ? response.data : [];
                const names = offerSourceData.map(item => item.nombre);
                ofertaAcademicaInput.autocomplete('option', 'source', names);

                if (selectedId) {
                    const selected = offerSourceData.find(item => item.id == selectedId);
                    if (selected) {
                        ofertaAcademicaInput.val(selected.nombre);
                        ofertaAcademicaHidden.val(selected.id);
                        loadCorreosList(typeId, selected.id);
                    }
                } else {
                    if (correosDataTable) {
                        correosDataTable.clear().draw();
                    }
                    showCorreosListMessage('Seleccione una oferta académica para ver las correos asociadas.', 'info');
                }
            },
            error: function () {
                showAlert('Error al cargar las ofertas académicas.', 'error');
            }
        });
    }

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
            error: function () {
                mensajesSelect.empty().append('<option value="">Error al cargar mensajes</option>');
                showAlert('Error al cargar los mensajes.', 'error');
            }
        });
    }

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
                    correosDataTable.destroy();
                }

                if (response.success && response.data.length > 0) {
                    correosListMessage.addClass('hidden');
                    correosDataTable = correosListTable.DataTable({
                        "data": response.data,
                        "responsive": false,
                        "searching": false,
                        "paging": false,
                        "info": false,
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
                            { "data": "ci_pasaporte" },
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
            error: function () {
                showAlert('Error al cargar las correos. Por favor, intente de nuevo.', 'error');
                if (correosDataTable) {
                    correosDataTable.clear().draw();
                }
            }
        });
    }

    $('.tab-button').on('click', function () {
        const tabId = $(this).data('tab-id');

        tipoOfertaAcademicaIdInput.val(tabId);

        $('.tab-button').removeClass('bg-blue-600 text-white font-bold').addClass('bg-gray-200 text-gray-700');
        $(this).addClass('bg-blue-600 text-white font-bold').removeClass('bg-gray-200 text-gray-700');

        loadAcademicOffers(tabId);
        loadMensajesOffers();
    });

    if (formCorreo.length) {
        ofertaAcademicaInput.autocomplete({
            source: [],
            minLength: 0,
            select: function (event, ui) {
                const item = offerSourceData.find(i => i.nombre === ui.item.value);
                if (item) {
                    $(this).val(ui.item.value);
                    ofertaAcademicaHidden.val(item.id);
                    const tipoId = tipoOfertaAcademicaIdInput.val();
                    loadCorreosList(tipoId, item.id);
                }
                return false;
            },
            change: function (event, ui) {
                if (!ui.item) ofertaAcademicaHidden.val('');
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });

        const initialTipoOfertaId = formCorreo.data('tipo-oferta-academica-id');
        const initialOfertaAcademicaId = formCorreo.data('oferta-academica-id');

        const currentActiveTabButton = $(`.tab-button[data-tab-id="${initialTipoOfertaId}"]`);
        if (currentActiveTabButton.length) {
            currentActiveTabButton.trigger('click');
            if (initialOfertaAcademicaId) {
                setTimeout(() => {
                    loadAcademicOffers(initialTipoOfertaId, initialOfertaAcademicaId);
                }, 300);
            }
        } else {
            $('.tab-button[data-tab-id="1"]').trigger('click');
        }

        $('#sendCheckedEmailsBtn').on('click', function () {
            const correosSeleccionados = $('.correo-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

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
                error: function () {
                    showAlert('Error al enviar correos. Intenta de nuevo.', 'error');
                }
            });
        });

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
                            const currentOfertaId = ofertaAcademicaHidden.val();
                            loadCorreosList(currentTipoOfertaId, currentOfertaId);
                        } else {
                            showAlert('Error al eliminar: ' + response.message, 'error');
                        }
                    },
                    error: function () {
                        showAlert('Error al intentar eliminar la correo.', 'error');
                    }
                });
            }
        });

        // --- Lógica del Modal de Generación de Deuda ---

        closeDebtModalBtn.on('click', function () {
            generateDebtModal.addClass('hidden');
            if (studentsDataTable) {
                studentsDataTable.destroy();
                studentsListTable.find('tbody').empty();
            }
            selectAllStudentsCheckbox.prop('checked', false);
        });

        selectAllStudentsCheckbox.on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox').prop('checked', isChecked);
        });

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

            confirmGenerateDebtBtn.prop('disabled', true).text('Generando Deuda...');

            $.ajax({
                url: `${BASE_URL_JS}correo/generateDebt`,
                type: 'POST',
                data: {
                    correo_id: currentCorreoIdForDebt,
                    alumno_ids: selectedAlumnoIds,
                    monto_correo: currentCorreoMontoForDebt,
                    tipo_oferta_id: currentTipoOfertaIdForDebt,
                    oferta_id: currentOfertaIdForDebt
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        generateDebtModal.addClass('hidden');
                        loadCorreosList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaHidden.val());
                    } else {
                        showAlert('Error al generar deuda: ' + response.message, 'error');
                    }
                },
                error: function () {
                    showAlert('Error al procesar la solicitud de generación de deuda.', 'error');
                },
                complete: function () {
                    confirmGenerateDebtBtn.prop('disabled', false).text('Generar Deuda Seleccionados');
                }
            });
        });
    }
});
