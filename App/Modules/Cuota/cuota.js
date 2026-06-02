console.log('cuota.js cargado.');

$(document).ready(function () {
    const formCuota = $('#formCuota');
    const tipoOfertaAcademicaIdInput = $('#tipo_oferta_academica_id');
    const ofertaAcademicaInput = $('#oferta_academica_nombre');
    const ofertaAcademicaHidden = $('#oferta_academica_id');
    const ofertaInfoBox = $('#oferta-info-box');
    const infoCosto = $('#info-costo');
    const infoInicial = $('#info-inicial');
    const infoOfertaLabel = $('#info-oferta-label');
    const diplomadoControlSection = $('#diplomado-control-section');
    const diplomadoControlSelect = $('#diplomado_control_id');
    const controlInfoBox = $('#control-info-box');
    const infoCapitulo = $('#info-capitulo');
    const infoCostoCapitulo = $('#info-costo-capitulo');
    const infoControlFecha = $('#info-control-fecha');
    const diplomadoControlWarning = $('#diplomado-control-warning');
    const btnIrControles = $('#btn-ir-controles');
    const cuotasListTable = $('#cuotasListTable');
    const cuotasListMessage = $('#cuotas-list-message');
    const generateDebtModal = $('#generateDebtModal');
    const closeDebtModal = $('#closeDebtModal');
    const closeDebtModalBtn = $('#closeDebtModalBtn');
    const studentsListTable = $('#studentsListTable');
    const studentsListMessage = $('#students-list-message');
    const selectAllStudentsCheckbox = $('#selectAllStudents');
    const confirmGenerateDebtBtn = $('#confirmGenerateDebtBtn');
    const debtOfferInfo = $('#debt-offer-info');
    const debtOfertaLabel = $('#debt-oferta-label');
    const debtCuotaLabel = $('#debt-cuota-label');
    const csrfToken = $('input[name="csrf_token"]').val();

    let cuotasDataTable = null;
    let studentsDataTable = null;
    let currentCuotaIdForDebt = null;
    let currentCuotaMontoForDebt = null;
    let currentCuotaNombreForDebt = null;
    let offerSourceData = [];
    let isInlineEdit = false;

    function showAlert(message, type = 'error') {
        Swal.fire(type === 'success' ? 'Éxito' : 'Error', message, type);
    }

    function showCuotasListMessage(message, type = 'info') {
        cuotasListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    function resetFormToCreate() {
        isInlineEdit = false;
        $('input[name="id"]').remove();
        formCuota.attr('action', `${BASE_URL_JS}cuota/create`);
        $('#nombre, #monto').val('');
        if (typeof flatpickr !== 'undefined') {
            const fp = document.querySelector('#fecha_vencimiento')?._flatpickr;
            if (fp) fp.clear();
        } else {
            $('#fecha_vencimiento').val('');
        }
    }

    // Cargar ofertas académicas en el autocomplete
    function loadAcademicOffers(typeId, selectedId = null) {
        ofertaAcademicaInput.val('');
        ofertaAcademicaHidden.val('');
        ofertaInfoBox.addClass('hidden');
        diplomadoControlSection.addClass('hidden');
        controlInfoBox.addClass('hidden');

        $.ajax({
            url: `${BASE_URL_JS}cuota/getAcademicOffersByType`,
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
                        loadOfertaInfo(typeId, selected.id);
                        loadCuotasList(typeId, selected.id);
                    }
                } else {
                    if (cuotasDataTable) cuotasDataTable.clear().draw();
                    showCuotasListMessage('Seleccione una oferta académica para ver las cuotas asociadas.', 'info');
                }
            },
            error: function () {
                showAlert('Error al cargar las ofertas académicas.', 'error');
            }
        });
    }

    // Cargar información de la oferta (costo, inicial)
    function loadOfertaInfo(tipoOfertaId, ofertaId) {
        if (!tipoOfertaId || !ofertaId) {
            ofertaInfoBox.addClass('hidden');
            return;
        }

        $.ajax({
            url: `${BASE_URL_JS}cuota/getOfertaInfoAjax`,
            type: 'GET',
            data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    infoCosto.text(`$${parseFloat(response.data.costo || 0).toFixed(2)}`);
                    infoInicial.text(`$${parseFloat(response.data.inicial || 0).toFixed(2)}`);
                    infoOfertaLabel.text(response.data.oferta_label || '—');
                    ofertaInfoBox.removeClass('hidden');

                    // Si es diplomado, cargar controles
                    if (tipoOfertaId == 2) {
                        loadDiplomadoControles(ofertaId);
                    } else {
                        diplomadoControlSection.addClass('hidden');
                        controlInfoBox.addClass('hidden');
                    }
                } else {
                    ofertaInfoBox.addClass('hidden');
                }
            },
            error: function () {
                ofertaInfoBox.addClass('hidden');
            }
        });
    }

    // Cargar controles de diplomado
    function loadDiplomadoControles(diplomadoAbiertoId) {
        if (!diplomadoAbiertoId) {
            diplomadoControlSection.addClass('hidden');
            return;
        }

        const selectedId = formCuota.data('diplomado-control-id');

        $.ajax({
            url: `${BASE_URL_JS}cuota/getDiplomadoControlesAjax`,
            type: 'GET',
            data: { diplomado_abierto_id: diplomadoAbiertoId },
            dataType: 'json',
            success: function (response) {
                diplomadoControlSelect.empty().append('<option value="">Seleccione un control</option>');
                controlInfoBox.addClass('hidden');
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        const isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        diplomadoControlSelect.append(`<option value="${item.id}" ${isSelected}>${item.capitulo_numero} - ${item.capitulo_nombre}</option>`);
                    });
                    diplomadoControlSelect.prop('disabled', false);
                    diplomadoControlWarning.addClass('hidden');
                    if (selectedId) {
                        diplomadoControlSelect.val(selectedId).trigger('change');
                    }
                } else {
                    diplomadoControlSelect.prop('disabled', true);
                    diplomadoControlWarning.addClass('hidden');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin controles',
                        text: 'Este diplomado no tiene controles configurados.',
                        showCancelButton: true,
                        confirmButtonColor: '#d97706',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ir a configurar controles',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(BASE_URL_JS + 'diplomadocontrol/edit/' + diplomadoAbiertoId, '_blank');
                        }
                    });
                }
                diplomadoControlSection.removeClass('hidden');
            },
            error: function () {
                diplomadoControlSelect.empty().append('<option value="">Error al cargar controles</option>');
                diplomadoControlSelect.prop('disabled', true);
                controlInfoBox.addClass('hidden');
                diplomadoControlSection.removeClass('hidden');
            }
        });
    }

    // Cargar lista de cuotas
    function loadCuotasList(tipoOfertaId, ofertaId) {
        if (!tipoOfertaId || !ofertaId) {
            if (cuotasDataTable) cuotasDataTable.clear().draw();
            showCuotasListMessage('Seleccione una oferta académica para ver las cuotas asociadas.', 'info');
            return;
        }

        showCuotasListMessage('Cargando cuotas...', 'info');
        if (cuotasDataTable) cuotasDataTable.clear().draw();

        $.ajax({
            url: `${BASE_URL_JS}cuota/getCuotasByOfferData`,
            type: 'GET',
            data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId },
            dataType: 'json',
            success: function (response) {
                if (cuotasDataTable) {
                    cuotasDataTable.destroy();
                    cuotasDataTable = null;
                }

                if (response.success && response.data.length > 0) {
                    cuotasListMessage.addClass('hidden');
                    cuotasDataTable = cuotasListTable.DataTable({
                        "data": response.data,
                        "responsive": true,
                        "searching": false,
                        "paging": false,
                        "info": false,
                        "columns": [
                            { "data": "id" },
                            { "data": "nombre" },
                            { "data": "monto" },
                            {
                                "data": null,
                                "render": function (data, type, row) {
                                    if (row.capitulo_nombre) {
                                        return `${row.capitulo_numero || ''} ${row.capitulo_nombre}`;
                                    }
                                    return '—';
                                }
                            },
                            {
                                "data": "fecha_vencimiento",
                                "render": function (data, type) {
                                    if (type === 'display' && data) {
                                        return data.split('-').reverse().join('/');
                                    }
                                    return data || '';
                                }
                            },
                            {
                                "data": "fecha",
                                "render": function (data, type) {
                                    if (type === 'display' && data) {
                                        return data.split('-').reverse().join('/');
                                    }
                                    return data || '';
                                }
                            },
                            {
                                "data": null,
                                "orderable": false,
                                "searchable": false,
                                "width": "1%",
                                "className": "actions-column",
                                "render": function (data, type, row) {
                                    const editBtn = `<button type="button" class="btn-action btn-action-edit-inline" title="Editar" data-cuota-id="${row.id}"><i class="fas fa-edit"></i></button>`;
                                    return `<div class="flex gap-2 justify-center">${editBtn}<button type="button" class="btn-action btn-action-generar btn-generar-deuda" title="Generar deuda" data-cuota-id="${row.id}" data-nombre="${row.nombre}" data-monto="${row.monto}"><i class="fas fa-file-invoice-dollar"></i></button></div>`;
                                }
                            }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                        },
                        "autoWidth": false
                    });
                } else {
                    showCuotasListMessage('No hay cuotas creadas para esta oferta académica.', 'info');
                }
            },
            error: function () {
                showAlert('Error al cargar las cuotas.', 'error');
            }
        });
    }

    // Evento: clic en pestañas
    $('.tab-button').on('click', function () {
        const tabId = $(this).data('tab-id');
        tipoOfertaAcademicaIdInput.val(tabId);

        $('.tab-button').removeClass('bg-blue-600 text-white font-bold').addClass('bg-gray-200 text-gray-700');
        $(this).addClass('bg-blue-600 text-white font-bold').removeClass('bg-gray-200 text-gray-700');

        ofertaInfoBox.addClass('hidden');
        diplomadoControlSection.addClass('hidden');
        controlInfoBox.addClass('hidden');
        if (isInlineEdit) resetFormToCreate();
        loadAcademicOffers(tabId);
    });

    // Evento: cambio de control de diplomado
    diplomadoControlSelect.on('change', function () {
        const selectedOption = $(this).find('option:selected');
        const selectedText = selectedOption.text();
        const selectedVal = $(this).val();

        if (selectedVal) {
            // Buscar el costo del control seleccionado
            const options = $(this).find('option');
            for (let i = 0; i < options.length; i++) {
                if (options[i].value == selectedVal) {
                    const parts = selectedText.split(' - ');
                    infoCapitulo.text(parts.slice(1).join(' - ') || selectedText);
                    break;
                }
            }
            // Obtener costo via API
            $.ajax({
                url: `${BASE_URL_JS}cuota/getDiplomadoControlesAjax`,
                type: 'GET',
                data: { diplomado_abierto_id: ofertaAcademicaHidden.val() },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        const control = response.data.find(c => c.id == selectedVal);
                        if (control) {
                            infoCapitulo.text(`${control.capitulo_numero} - ${control.capitulo_nombre}`);
                            infoCostoCapitulo.text(`$${parseFloat(control.costo || 0).toFixed(2)}`);
                            const rawFecha = control.control_fecha || '';
                            infoControlFecha.text(rawFecha ? rawFecha.split('-').reverse().join('/') : '—');
                            controlInfoBox.removeClass('hidden');
                        }
                    }
                }
            });
        } else {
            controlInfoBox.addClass('hidden');
        }
    });

    // Inicialización
    if (formCuota.length) {
        // Inicialización del autocomplete para oferta académica
        ofertaAcademicaInput.autocomplete({
            source: [],
            minLength: 0,
            select: function (event, ui) {
                const item = offerSourceData.find(i => i.nombre === ui.item.value);
                if (item) {
                    $(this).val(ui.item.value);
                    ofertaAcademicaHidden.val(item.id);
                    const tipoId = tipoOfertaAcademicaIdInput.val();
                    loadOfertaInfo(tipoId, item.id);
                    loadCuotasList(tipoId, item.id);
                }
                return false;
            },
            change: function (event, ui) {
                if (!ui.item) ofertaAcademicaHidden.val('');
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });

        const initialTipoOfertaId = formCuota.data('tipo-oferta-academica-id');
        const initialOfertaAcademicaId = formCuota.data('oferta-academica-id');

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

        // Envío del formulario por AJAX
        formCuota.on('submit', function (event) {
            event.preventDefault();

            const formData = $(this).serialize();
            const actionUrl = $(this).attr('action');
            const isEdit = $(this).find('input[name="id"]').length > 0;

            const nombre = $('#nombre').val().trim();
            const monto = $('#monto').val().trim();
            const ofertaAcademicaId = $('#oferta_academica_id').val();
            const tipoOfertaAcademicaId = $('#tipo_oferta_academica_id').val();
            const fechaVencimiento = $('#fecha_vencimiento').val().trim();

            if (nombre === '' || monto === '' || ofertaAcademicaId === '' || tipoOfertaAcademicaId === '' || fechaVencimiento === '') {
                showAlert('Por favor, complete todos los campos obligatorios.', 'error');
                return;
            }

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        loadCuotasList(tipoOfertaAcademicaId, ofertaAcademicaId);
                        if (isInlineEdit) {
                            resetFormToCreate();
                        } else if (!isEdit) {
                            $('#nombre, #monto').val('');
                            if (typeof flatpickr !== 'undefined') {
                                const fp = document.querySelector('#fecha_vencimiento')?._flatpickr;
                                if (fp) fp.clear();
                            } else {
                                $('#fecha_vencimiento').val('');
                            }
                        }
                        if (tipoOfertaAcademicaId == 2 && ofertaAcademicaId) {
                            loadDiplomadoControles(ofertaAcademicaId);
                        }
                        loadOfertaInfo(tipoOfertaAcademicaId, ofertaAcademicaId);
                    } else {
                        showAlert('Error: ' + response.message, 'error');
                    }
                },
                error: function () {
                    showAlert('Error al procesar la solicitud.', 'error');
                }
            });
        });

        // Editar cuota inline (carga datos en el formulario)
        $(document).on('click', '.btn-action-edit-inline', function () {
            const tr = $(this).closest('tr');
            const row = cuotasDataTable ? cuotasDataTable.row(tr).data() : null;
            if (!row) return;

            $('#nombre').val(row.nombre);
            $('#monto').val(row.monto);

            if (typeof flatpickr !== 'undefined') {
                const fp = document.querySelector('#fecha_vencimiento')?._flatpickr;
                if (fp) fp.setDate(row.fecha_vencimiento);
            } else {
                $('#fecha_vencimiento').val(row.fecha_vencimiento);
            }

            if (!$('input[name="id"]').length) {
                formCuota.append(`<input type="hidden" name="id" value="${row.id}">`);
            } else {
                $('input[name="id"]').val(row.id);
            }

            formCuota.attr('action', `${BASE_URL_JS}cuota/edit/${row.id}`);
            isInlineEdit = true;

            $('html, body').animate({ scrollTop: formCuota.offset().top - 20 }, 500);
        });

        // Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fecha_vencimiento", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                locale: "es",
            });
            flatpickr.localize(flatpickr.l10ns.es);
        }

        // --- Modal Generar Deuda ---

        $(document).on('click', '.btn-generar-deuda', function () {
            const cuotaId = $(this).data('cuota-id');
            const nombre = $(this).data('nombre');
            const monto = $(this).data('monto');
            const tipoOfertaId = tipoOfertaAcademicaIdInput.val();
            const ofertaId = ofertaAcademicaHidden.val();

            if (!cuotaId || !monto || !tipoOfertaId || !ofertaId) {
                showAlert('Faltan datos para generar la deuda.', 'error');
                return;
            }

            currentCuotaIdForDebt = cuotaId;
            currentCuotaMontoForDebt = monto;
            currentCuotaNombreForDebt = nombre;

            $('#students-list-message').addClass('hidden');
            confirmGenerateDebtBtn.prop('disabled', true).text('Generar Deuda Seleccionados');

            $.ajax({
                url: `${BASE_URL_JS}cuota/getStudentsForDebtGeneration`,
                type: 'GET',
                data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId, cuota_id: cuotaId },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data && response.data.length > 0) {
                    if (studentsDataTable) {
                        studentsDataTable.destroy();
                        studentsDataTable = null;
                        studentsListTable.find('tbody').empty();
                    }
                    selectAllStudentsCheckbox.prop('checked', false);

                    debtOfertaLabel.text(response.oferta_label || '—');
                        debtCuotaLabel.text(currentCuotaNombreForDebt || '—');
                        debtOfferInfo.removeClass('hidden');

                        studentsDataTable = studentsListTable.DataTable({
                            data: response.data,
                            responsive: true,
                            searching: false,
                            paging: false,
                            info: false,
                            columns: [
                                {
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        const tieneDeuda = parseInt(row.tiene_deuda || 0);
                                        if (tieneDeuda) {
                                            return `<input type="checkbox" class="student-checkbox" data-alumno-id="${row.alumno_id}" disabled>`;
                                        }
                                        return `<input type="checkbox" class="student-checkbox" data-alumno-id="${row.alumno_id}" checked>`;
                                    }
                                },
                                { data: 'alumno_nombre_completo' },
                                { data: 'alumno_ci' },
                                {
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    className: 'text-center',
                                    render: function (data, type, row) {
                                        const tieneDeuda = parseInt(row.tiene_deuda || 0);
                                        return tieneDeuda ? '<span class="text-green-600 font-semibold text-xs">✓ Generada</span>' : '<span class="text-gray-400 text-xs">—</span>';
                                    }
                                },
                                {
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    className: 'text-center font-semibold',
                                    render: function () {
                                        return `$${parseFloat(currentCuotaMontoForDebt || 0).toFixed(2)}`;
                                    }
                                }
                            ],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
                            },
                            autoWidth: false,
                            drawCallback: function () {
                                selectAllStudentsCheckbox.prop('checked', false);
                                const anyCheckable = $('.student-checkbox:not(:disabled)').length > 0;
                                confirmGenerateDebtBtn.prop('disabled', !anyCheckable);
                            }
                        });
                        generateDebtModal.removeClass('hidden');
                    } else {
                        showAlert('No hay alumnos inscritos en esta oferta.', 'info');
                    }
                },
                error: function () {
                    showAlert('Error al cargar los alumnos.', 'error');
                }
            });
        });

        // Cerrar modal
        closeDebtModal.add(closeDebtModalBtn).on('click', function () {
            generateDebtModal.addClass('hidden');
            debtOfferInfo.addClass('hidden');
            if (studentsDataTable) {
                studentsDataTable.destroy();
                studentsDataTable = null;
                studentsListTable.find('tbody').empty();
            }
            selectAllStudentsCheckbox.prop('checked', false);
        });

        // Seleccionar/Deseleccionar todos (solo afecta alumnos sin deuda)
        selectAllStudentsCheckbox.on('change', function () {
            const checked = $(this).is(':checked');
            $('.student-checkbox').not(':disabled').prop('checked', checked);
            const anyChecked = $('.student-checkbox:checked').length > 0;
            confirmGenerateDebtBtn.prop('disabled', !anyChecked);
        });

        // Habilitar botón cuando hay al menos un checkbox marcado
        $(document).on('click', '.student-checkbox', function () {
            const anyChecked = $('.student-checkbox:checked').length > 0;
            confirmGenerateDebtBtn.prop('disabled', !anyChecked);
        });

        // Confirmar generación de deuda
        confirmGenerateDebtBtn.on('click', function () {
            const selectedAlumnoIds = [];
            $('.student-checkbox:checked').each(function () {
                selectedAlumnoIds.push($(this).data('alumno-id'));
            });

            if (selectedAlumnoIds.length === 0) {
                showAlert('Seleccione al menos un alumno.', 'info');
                return;
            }

            confirmGenerateDebtBtn.prop('disabled', true).text('Generando Deuda...');

            $.ajax({
                url: `${BASE_URL_JS}cuota/generateDebt`,
                type: 'POST',
                data: {
                    cuota_id: currentCuotaIdForDebt,
                    alumno_ids: selectedAlumnoIds,
                    monto_cuota: currentCuotaMontoForDebt,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        generateDebtModal.addClass('hidden');
                        loadCuotasList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaHidden.val());
                    } else {
                        showAlert('Error: ' + response.message, 'error');
                    }
                },
                error: function () {
                    showAlert('Error al procesar la generación de deuda.', 'error');
                },
                complete: function () {
                    confirmGenerateDebtBtn.prop('disabled', false).text('Generar Deuda Seleccionados');
                }
            });
        });

    }
});
