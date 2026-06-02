console.log('asistencia.js cargado.');

$(document).ready(function () {
    const tipoOfertaInput = $('#tipo_oferta_academica_id');
    const ofertaInput = $('#oferta_academica_nombre');
    const ofertaHidden = $('#oferta_academica_id');
    const ofertaLabelBox = $('#oferta-label-box');
    const infoOfertaLabel = $('#info-oferta-label');
    const masterIdInput = $('#master_id');
    const claseIdInput = $('#clase_id');
    const clasesSection = $('#clases-section');
    const clasesList = $('#clases-list');
    const studentsSection = $('#students-section');
    const studentsTable = $('#studentsTable');
    const studentsMessage = $('#students-message');
    const claseTitle = $('#clase-title');
    const selectAllCheckbox = $('#selectAllStudents');
    const btnSave = $('#btnSaveAsistencia');
    const saveMessage = $('#save-message');
    const formAsistencia = $('#formAsistencia');
    const csrfToken = $('input[name="csrf_token"]').val();

    let offerSourceData = [];
    let currentTipoId = null;
    let currentOfertaId = null;
    let currentMasterId = null;
    let currentClaseId = 0;
    let hasClases = false;

    function showAlert(message, type) {
        Swal.fire(type === 'success' ? 'Éxito' : 'Error', message, type);
    }

    function showStudentsMessage(msg, type) {
        studentsMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
            .addClass('text-' + type + '-600').text(msg);
    }

    function loadAcademicOffers(typeId, selectedId) {
        ofertaInput.val('');
        ofertaHidden.val('');
        ofertaLabelBox.addClass('hidden');
        clasesSection.addClass('hidden');
        studentsSection.addClass('hidden');

        $.ajax({
            url: BASE_URL_JS + 'asistencia/getAcademicOffersByType',
            type: 'GET',
            data: { type_id: typeId },
            dataType: 'json',
            success: function (response) {
                offerSourceData = response.success && response.data ? response.data : [];
                const names = offerSourceData.map(function (item) { return item.nombre; });
                ofertaInput.autocomplete('option', 'source', names);

                if (selectedId) {
                    var selected = offerSourceData.find(function (item) { return item.id == selectedId; });
                    if (selected) {
                        ofertaInput.val(selected.nombre);
                        ofertaHidden.val(selected.id);
                        initAsistencia(typeId, selected.id);
                    }
                }
            },
            error: function () {
                showAlert('Error al cargar las ofertas.', 'error');
            }
        });
    }

    function initAsistencia(tipoOfertaId, ofertaId) {
        currentTipoId = tipoOfertaId;
        currentOfertaId = ofertaId;

        ofertaLabelBox.removeClass('hidden');
        infoOfertaLabel.text(ofertaInput.val() || '—');

        $.ajax({
            url: BASE_URL_JS + 'asistencia/initAsistencia',
            type: 'GET',
            data: { tipo_oferta_id: tipoOfertaId, oferta_id: ofertaId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    currentMasterId = response.data.master_id;
                    masterIdInput.val(currentMasterId);
                    hasClases = response.data.clases && response.data.clases.length > 0;

                    if (hasClases) {
                        renderClases(response.data.clases);
                        clasesSection.removeClass('hidden');
                        studentsSection.addClass('hidden');
                        currentClaseId = 0;
                    } else {
                        clasesSection.addClass('hidden');
                        claseTitle.text('Estudiantes Inscritos');
                        currentClaseId = 0;
                        claseIdInput.val(0);
                        loadAlumnos(0);
                        studentsSection.removeClass('hidden');
                    }
                } else {
                    showAlert('Error al inicializar asistencia.', 'error');
                }
            },
            error: function () {
                showAlert('Error de conexión.', 'error');
            }
        });
    }

    function renderClases(clases) {
        clasesList.empty();

        $.each(clases, function (idx, c) {
            var asistenciaText = c.total_registrados > 0
                ? '<span class="text-green-600 font-semibold">' + c.asistentes + '/' + c.total_registrados + '</span>'
                : '<span class="text-gray-400">—</span>';

            var claseHtml = '<div class="clase-item bg-white rounded border border-yellow-300 p-3 hover:shadow cursor-pointer flex justify-between items-center" data-clase-id="' + c.id + '">' +
                '<div>' +
                    '<span class="font-bold text-gray-800">' + (c.fecha ? c.fecha.split('-').reverse().join('/') : '') + '</span>' +
                    (c.detalle ? ' <span class="text-gray-500 text-sm">— ' + $('<span>').text(c.detalle).html() + '</span>' : '') +
                    ' <span class="text-gray-400 text-xs">(' + $('<span>').text(c.docente_nombre).html() + ')</span>' +
                '</div>' +
                '<div class="text-sm">' + asistenciaText + '</div>' +
                '</div>';
            clasesList.append(claseHtml);
        });
    }

    function loadAlumnos(claseId, claseLabel) {
        currentClaseId = claseId;
        claseIdInput.val(claseId);

        if (claseLabel) {
            claseTitle.text('Estudiantes — ' + claseLabel);
        } else {
            claseTitle.text('Estudiantes Inscritos');
        }

        showStudentsMessage('Cargando estudiantes...', 'blue');
        studentsSection.removeClass('hidden');
        $('#studentsTable tbody').empty();

        var data = {
            master_id: currentMasterId,
            clase_id: claseId,
            tipo_oferta_id: currentTipoId,
            oferta_id: currentOfertaId
        };

        $.ajax({
            url: BASE_URL_JS + 'asistencia/getAlumnosAjax',
            type: 'GET',
            data: data,
            dataType: 'json',
            success: function (response) {
                studentsMessage.addClass('hidden');
                var tbody = $('#studentsTable tbody');
                tbody.empty();
                selectAllCheckbox.prop('checked', false);

                if (response.success && response.data.length > 0) {
                    $.each(response.data, function (idx, s) {
                        var checked = parseInt(s.asiste) ? 'checked' : '';
                        var estadoHtml = parseInt(s.asiste)
                            ? '<span class="text-green-600"><i class="fas fa-check-circle"></i> Presente</span>'
                            : '<span class="text-red-400"><i class="fas fa-times-circle"></i> Ausente</span>';
                        var row = '<tr>' +
                            '<td class="py-3 px-6 text-left"><input type="checkbox" class="student-checkbox" data-alumno-id="' + s.alumno_id + '" ' + checked + '></td>' +
                            '<td class="py-3 px-6 text-left">' + $('<span>').text(s.alumno_nombre).html() + '</td>' +
                            '<td class="py-3 px-6 text-left">' + $('<span>').text(s.alumno_ci).html() + '</td>' +
                            '<td class="py-3 px-6 text-center">' + estadoHtml + '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="py-4 text-center text-gray-500">No hay estudiantes inscritos en esta oferta.</td></tr>');
                }
            },
            error: function () {
                showStudentsMessage('Error al cargar estudiantes.', 'red');
            }
        });
    }

    // Tab click
    $('.tab-button').on('click', function () {
        var tabId = $(this).data('tab-id');
        tipoOfertaInput.val(tabId);

        $('.tab-button').removeClass('bg-blue-600 text-white font-bold').addClass('bg-gray-200 text-gray-700');
        $(this).addClass('bg-blue-600 text-white font-bold').removeClass('bg-gray-200 text-gray-700');

        ofertaLabelBox.addClass('hidden');
        clasesSection.addClass('hidden');
        studentsSection.addClass('hidden');
        loadAcademicOffers(tabId);
    });

    // Click on a clase
    $(document).on('click', '.clase-item', function () {
        $('.clase-item').removeClass('border-blue-500 bg-blue-50').addClass('border-yellow-300');
        $(this).addClass('border-blue-500 bg-blue-50').removeClass('border-yellow-300');
        var label = $(this).find('.font-bold').first().text() + ($(this).find('.text-gray-500').first().text() || '');
        loadAlumnos($(this).data('clase-id'), label);
    });

    // Autocomplete
    ofertaInput.autocomplete({
        source: [],
        minLength: 0,
        select: function (event, ui) {
            var item = offerSourceData.find(function (i) { return i.nombre === ui.item.value; });
            if (item) {
                $(this).val(ui.item.value);
                ofertaHidden.val(item.id);
                initAsistencia(tipoOfertaInput.val(), item.id);
            }
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) ofertaHidden.val('');
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });

    // Default first tab
    $('.tab-button[data-tab-id="1"]').trigger('click');

    // Select all
    selectAllCheckbox.on('change', function () {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Save
    formAsistencia.on('submit', function (e) {
        e.preventDefault();

        if (!currentMasterId || currentClaseId === null) {
            showAlert('Seleccione una oferta y clase.', 'error');
            return;
        }

        var observacion = $('#observacion').val().trim();
        var alumnoIds = [];
        $('.student-checkbox:checked').each(function () {
            alumnoIds.push($(this).data('alumno-id'));
        });

        btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        saveMessage.removeClass('hidden text-green-600 text-red-600').addClass('text-blue-600').text('Guardando...');

        $.ajax({
            url: BASE_URL_JS + 'asistencia/save',
            type: 'POST',
            data: {
                master_id: currentMasterId,
                clase_id: currentClaseId,
                tipo_oferta_academica_id: currentTipoId,
                oferta_id: currentOfertaId,
                observacion: observacion,
                alumno_ids: alumnoIds,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    saveMessage.removeClass('text-blue-600 text-red-600').addClass('text-green-600').text(response.message);
                    if (response.clases) {
                        renderClases(response.clases);
                    }
                    loadAlumnos(currentClaseId, currentClaseId === 0 ? null : null);
                } else {
                    showAlert('Error: ' + response.message, 'error');
                    saveMessage.removeClass('text-blue-600 text-green-600').addClass('text-red-600').text(response.message);
                }
            },
            error: function () {
                showAlert('Error al procesar la solicitud.', 'error');
                saveMessage.removeClass('text-blue-600 text-green-600').addClass('text-red-600').text('Error de conexión.');
            },
            complete: function () {
                btnSave.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar Asistencia');
                setTimeout(function () { saveMessage.addClass('hidden'); }, 4000);
            }
        });
    });
});
