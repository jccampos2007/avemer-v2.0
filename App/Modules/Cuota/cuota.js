console.log('cuota.js cargado.');

$(document).ready(function () {
    const formCuota = $('#formCuota');
    const tipoOfertaAcademicaIdInput = $('#tipo_oferta_academica_id');
    const ofertaAcademicaSelect = $('#oferta_academica_id');
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

    let cuotasDataTable = null;
    let studentsDataTable = null;
    let currentCuotaIdForDebt = null;
    let currentCuotaMontoForDebt = null;

    function showAlert(message, type = 'error') {
        Swal.fire(type === 'success' ? 'Éxito' : 'Error', message, type);
    }

    function showCuotasListMessage(message, type = 'info') {
        cuotasListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass(`text-${type}-600`)
            .text(message);
    }

    // Cargar ofertas académicas en el select
    function loadAcademicOffers(typeId, selectedId = null) {
        ofertaAcademicaSelect.empty().append('<option value="">Cargando...</option>');
        ofertaInfoBox.addClass('hidden');
        diplomadoControlSection.addClass('hidden');
        controlInfoBox.addClass('hidden');

        $.ajax({
            url: `${BASE_URL_JS}cuota/getAcademicOffersByType`,
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
                if (selectedId) {
                    ofertaAcademicaSelect.val(selectedId).trigger('change');
                } else {
                    if (cuotasDataTable) cuotasDataTable.clear().draw();
                    showCuotasListMessage('Seleccione una oferta académica para ver las cuotas asociadas.', 'info');
                }
            },
            error: function () {
                ofertaAcademicaSelect.empty().append('<option value="">Error al cargar ofertas</option>');
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

        $.ajax({
            url: `${BASE_URL_JS}cuota/getCuotasByOfferData`,
            type: 'GET',
            data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId },
            dataType: 'json',
            success: function (response) {
                if (cuotasDataTable) cuotasDataTable.destroy();

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
                            { "data": "fecha_vencimiento" },
                            { "data": "fecha" },
                            {
                                "data": null,
                                "orderable": false,
                                "searchable": false,
                                "render": function (data, type, row) {
                                    const editBtn = `<a href="cuota/edit/${row.id}" class="btn btn-default" title="Editar"><i class="fas fa-edit fs-5 text-blue-600"></i></a>`;
                                    const deleteBtn = `<a href="cuota/delete/${row.id}" class="btn btn-default" title="Eliminar"><i class="fas fa-trash-alt fs-5 text-red-600"></i></a>`;
                                    const generado = parseInt(row.generado || 0);
                                    if (generado === 1) {
                                        return `${editBtn} ${deleteBtn} <button type="button" class="btn btn-default" title="Deuda ya generada" disabled><i class="fas fa-check-circle fs-5 text-green-600"></i></button>`;
                                    } else {
                                        return `${editBtn} ${deleteBtn} <button type="button" class="btn-generar-deuda btn btn-default" title="Generar deuda" data-cuota-id="${row.id}" data-monto="${row.monto}"><i class="fas fa-file-invoice-dollar fs-5 text-orange-500"></i></button>`;
                                    }
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
                    if (cuotasDataTable) cuotasDataTable.clear().draw();
                }
            },
            error: function () {
                showAlert('Error al cargar las cuotas.', 'error');
                if (cuotasDataTable) cuotasDataTable.clear().draw();
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
        loadAcademicOffers(tabId);
    });

    // Evento: cambio de oferta académica
    ofertaAcademicaSelect.on('change', function () {
        const selectedOfertaId = $(this).val();
        const selectedTipoOfertaId = tipoOfertaAcademicaIdInput.val();
        loadOfertaInfo(selectedTipoOfertaId, selectedOfertaId);
        loadCuotasList(selectedTipoOfertaId, selectedOfertaId);
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
                data: { diplomado_abierto_id: ofertaAcademicaSelect.val() },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        const control = response.data.find(c => c.id == selectedVal);
                        if (control) {
                            infoCapitulo.text(`${control.capitulo_numero} - ${control.capitulo_nombre}`);
                            infoCostoCapitulo.text(`$${parseFloat(control.costo || 0).toFixed(2)}`);
                            infoControlFecha.text(control.control_fecha || '—');
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
        const initialTipoOfertaId = formCuota.data('tipo-oferta-academica-id');
        const initialOfertaAcademicaId = formCuota.data('oferta-academica-id');

        const currentActiveTabButton = $(`.tab-button[data-tab-id="${initialTipoOfertaId}"]`);
        if (currentActiveTabButton.length) {
            currentActiveTabButton.trigger('click');
            if (initialOfertaAcademicaId) {
                setTimeout(() => {
                    ofertaAcademicaSelect.val(initialOfertaAcademicaId).trigger('change');
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
                        if (!isEdit) {
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

        // Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fecha_vencimiento", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F, Y",
                locale: "es",
            });
            flatpickr.localize(flatpickr.l10ns.es);
        }

        // --- Modal Generar Deuda ---

        $(document).on('click', '.btn-generar-deuda', function () {
            const cuotaId = $(this).data('cuota-id');
            const monto = $(this).data('monto');
            const tipoOfertaId = tipoOfertaAcademicaIdInput.val();
            const ofertaId = ofertaAcademicaSelect.val();

            if (!cuotaId || !monto || !tipoOfertaId || !ofertaId) {
                showAlert('Faltan datos para generar la deuda.', 'error');
                return;
            }

            currentCuotaIdForDebt = cuotaId;
            currentCuotaMontoForDebt = monto;

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
                            studentsListTable.find('tbody').empty();
                        }
                        selectAllStudentsCheckbox.prop('checked', false);
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
                                        return `<input type="checkbox" class="student-checkbox" data-alumno-id="${row.alumno_id}">`;
                                    }
                                },
                                { data: 'alumno_nombre' },
                                { data: 'alumno_apellido' }
                            ],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
                            },
                            autoWidth: false,
                            drawCallback: function () {
                                selectAllStudentsCheckbox.prop('checked', false);
                                confirmGenerateDebtBtn.prop('disabled', true);
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
            if (studentsDataTable) {
                studentsDataTable.destroy();
                studentsListTable.find('tbody').empty();
            }
            selectAllStudentsCheckbox.prop('checked', false);
        });

        // Seleccionar/Deseleccionar todos
        selectAllStudentsCheckbox.on('change', function () {
            $('.student-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Habilitar botón cuando hay al menos un checkbox marcado
        $(document).on('change', '.student-checkbox', function () {
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
                    monto_cuota: currentCuotaMontoForDebt
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        generateDebtModal.addClass('hidden');
                        loadCuotasList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaSelect.val());
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

        // Eliminar cuota
        $(document).on('click', '.delete-cuota-btn', function (e) {
            e.preventDefault();
            const cuotaId = $(this).data('id');
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrá revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${BASE_URL_JS}cuota/delete/${cuotaId}`,
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                loadCuotasList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaSelect.val());
                            } else {
                                showAlert('Error al eliminar: ' + response.message, 'error');
                            }
                        },
                        error: function () {
                            showAlert('Error al intentar eliminar la cuota.', 'error');
                        }
                    });
                }
            });
        });
    }
});
