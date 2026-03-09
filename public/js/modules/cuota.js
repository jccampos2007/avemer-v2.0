// app/Modules/Cuota/Views/js/cuota.js
console.log('cuota.js cargado.');

$(document).ready(function () {
    const formCuota = $('#formCuota');
    const tipoOfertaAcademicaIdInput = $('#tipo_oferta_academica_id');
    const ofertaAcademicaSelect = $('#oferta_academica_id');
    const cuotasListTable = $('#cuotasListTable');
    const cuotasListMessage = $('#cuotas-list-message');
    const generateDebtModal = $('#generateDebtModal');
    const closeDebtModalBtn = $('#closeDebtModal');
    const studentsListTable = $('#studentsListTable');
    const studentsListMessage = $('#students-list-message');
    const selectAllStudentsCheckbox = $('#selectAllStudents');
    const confirmGenerateDebtBtn = $('#confirmGenerateDebtBtn');

    let cuotasDataTable = null; // Variable para la instancia de DataTables
    let studentsDataTable = null; // Variable para la instancia de DataTables de alumnos
    let currentCuotaIdForDebt = null;
    let currentCuotaMontoForDebt = null;
    let currentTipoOfertaIdForDebt = null;
    let currentOfertaIdForDebt = null;

    // Log para depuración: Verificar valores iniciales de los data-atributos
    console.log('Initial Tipo Oferta ID (from form data-attribute):', formCuota.data('tipo-oferta-academica-id'));
    console.log('Initial Oferta Académica ID (from form data-attribute):', formCuota.data('oferta-academica-id'));


    // Función auxiliar para mostrar alertas, usando showFlashMessage si está disponible, o alert() por defecto.
    function showAlert(message, type = 'error') {
        if (typeof showFlashMessage === 'function') {
            showFlashMessage(type, message);
        } else {
            alert(message);
        }
    }

    // Función para mostrar mensajes en la sección de la lista de cuotas
    function showCuotasListMessage(message, type = 'info') {
        cuotasListMessage.removeClass('hidden text-green-600 text-red-600 text-blue-600')
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
                // Si ya hay una oferta académica seleccionada (ej. en edición), cargar las cuotas
                if (selectedId) {
                    loadCuotasList(typeId, selectedId);
                } else {
                    // Limpiar la tabla si no hay oferta seleccionada
                    if (cuotasDataTable) {
                        cuotasDataTable.clear().draw();
                    }
                    showCuotasListMessage('Seleccione una oferta académica para ver las cuotas asociadas.', 'info');
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

    // Función para cargar y mostrar la lista de cuotas
    function loadCuotasList(tipoOfertaId, ofertaId) {
        if (!tipoOfertaId || !ofertaId) {
            if (cuotasDataTable) {
                cuotasDataTable.clear().draw();
            }
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
                if (cuotasDataTable) {
                    cuotasDataTable.destroy(); // Destruir la instancia existente
                }

                if (response.success && response.data.length > 0) {
                    cuotasListMessage.addClass('hidden'); // Ocultar mensaje si hay datos
                    cuotasDataTable = cuotasListTable.DataTable({
                        "data": response.data,
                        "responsive": true,
                        "searching": false, // No se necesita búsqueda en esta tabla específica
                        "paging": false,    // No se necesita paginación
                        "info": false,      // No se necesita información de paginación
                        "columns": [
                            { "data": "id" },
                            { "data": "nombre" },
                            { "data": "monto" },
                            {
                                "data": "generado",
                                "render": function (data, type, row) {
                                    return data == 1 ? 'Sí' : 'No';
                                }
                            },
                            { "data": "fecha_vencimiento" },
                            { "data": "fecha" },
                            { // Columna para Acciones (Editar/Eliminar/Generar Deuda)
                                "data": null,
                                "orderable": false,
                                "searchable": false,
                                "render": function (data, type, row) {
                                    const generatedStatus = row.generado; // Asume que 'generado' viene en la respuesta
                                    let generateButton = '';
                                    if (generatedStatus == 0) { // Si no ha sido generada
                                        generateButton = `<button class="btn btn-default generate-debt-btn ml-2 text-purple-600 hover:text-purple-800"
                                            data-id="${row.id}"
                                            data-monto="${row.monto}"
                                            data-tipo-oferta-id="${tipoOfertaId}"
                                            data-oferta-id="${ofertaId}"
                                            title="Generar Deuda">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>`;
                                    } else {
                                        generateButton = `<span class="text-green-500 ml-2" title="Deuda ya generada"><i class="fas fa-check-circle"></i></span>`;
                                    }

                                    return `
                                        ${generateButton}
                                        <a href="cuota/edit/${row.id}" class="btn btn-default"><i class="fas fa-edit fs-5"></i></a>
                                        <a href="cuota/delete/${row.id}" class="btn btn-default"><i class="fas fa-trash-alt fs-5"></i></a>
                                    `;
                                }
                            }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                        },
                        "autoWidth": false
                    });
                } else {
                    showCuotasListMessage('No hay cuotas generadas para esta oferta académica.', 'info');
                    if (cuotasDataTable) {
                        cuotasDataTable.clear().draw();
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar la lista de cuotas:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                showAlert('Error al cargar las cuotas. Por favor, intente de nuevo.', 'error');
                if (cuotasDataTable) {
                    cuotasDataTable.clear().draw();
                }
            }
        });
    }

    // Función para cargar los alumnos en el modal
    function loadStudentsForDebtGeneration(cuotaId, tipoOfertaId, ofertaId, montoCuota) {
        showStudentsListMessage('Cargando alumnos...', 'info');
        studentsListTable.find('tbody').empty(); // Limpiar tabla de alumnos

        currentCuotaIdForDebt = cuotaId;
        currentCuotaMontoForDebt = montoCuota;
        currentTipoOfertaIdForDebt = tipoOfertaId;
        currentOfertaIdForDebt = ofertaId;

        $.ajax({
            url: `${BASE_URL_JS}cuota/getStudentsForDebtGeneration`,
            type: 'GET',
            data: {
                tipo_oferta_id: tipoOfertaId,
                oferta_id: ofertaId,
                cuota_id: cuotaId
            },
            dataType: 'json',
            success: function (response) {
                if (studentsDataTable) {
                    studentsDataTable.destroy(); // Destruir instancia existente
                }

                if (response.success && response.data.length > 0) {
                    studentsListMessage.addClass('hidden');
                    studentsDataTable = studentsListTable.DataTable({
                        "data": response.data,
                        "responsive": true,
                        "searching": false,
                        "paging": false,
                        "info": false,
                        "columns": [
                            {
                                "data": null,
                                "orderable": false,
                                "searchable": false,
                                "render": function (data, type, row) {
                                    return `<input type="checkbox" class="student-checkbox h-4 w-4 text-blue-600 rounded" data-alumno-id="${row.alumno_id}">`;
                                }
                            },
                            { "data": "alumno_id" },
                            {
                                "data": "alumno_nombre",
                                "render": function (data, type, row) {
                                    return `${row.alumno_nombre} ${row.alumno_apellido || ''}`; // Combina nombre y apellido
                                }
                            }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                        },
                        "autoWidth": false,
                        "createdRow": function (row, data, dataIndex) {
                            // Asegura que el checkbox "Seleccionar todos" se desmarque si hay alguna fila no seleccionada
                            selectAllStudentsCheckbox.prop('checked', false);
                        }
                    });
                    generateDebtModal.removeClass('hidden'); // Mostrar el modal
                } else {
                    showStudentsListMessage('No hay alumnos asociados a esta oferta académica.', 'info');
                    if (studentsDataTable) {
                        studentsDataTable.clear().draw();
                    }
                    generateDebtModal.removeClass('hidden'); // Mostrar el modal aunque no haya alumnos
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar alumnos para generar deuda:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                showAlert('Error al cargar los alumnos. Por favor, intente de nuevo.', 'error');
                showStudentsListMessage('Error al cargar los alumnos.', 'error');
                generateDebtModal.removeClass('hidden'); // Mostrar el modal con el mensaje de error
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
    });

    // Manejador de cambio para el select de oferta_academica_id
    ofertaAcademicaSelect.on('change', function () {
        const selectedOfertaId = $(this).val();
        const selectedTipoOfertaId = tipoOfertaAcademicaIdInput.val();
        loadCuotasList(selectedTipoOfertaId, selectedOfertaId);
    });

    // Inicializar la pestaña activa y cargar las ofertas al cargar la página
    if (formCuota.length) {
        const initialTipoOfertaId = formCuota.data('tipo-oferta-academica-id'); // Volver a obtener por si acaso
        const initialOfertaAcademicaId = formCuota.data('oferta-academica-id'); // Volver a obtener por si acaso

        const currentActiveTabButton = $(`.tab-button[data-tab-id="${initialTipoOfertaId}"]`);
        if (currentActiveTabButton.length) {
            currentActiveTabButton.trigger('click');
            // Después de cargar las opciones, pre-seleccionar si es un formulario de edición
            if (initialOfertaAcademicaId) {
                // Pequeño retraso para asegurar que las opciones se han cargado
                // La carga de la lista de cuotas se hará en el callback de loadAcademicOffers
                setTimeout(() => {
                    ofertaAcademicaSelect.val(initialOfertaAcademicaId).trigger('change');
                }, 200);
            }
        } else {
            // Si no hay un tipo pre-seleccionado, activa la primera pestaña (Curso)
            $('.tab-button[data-tab-id="1"]').trigger('click');
        }

        // Manejador de envío del formulario (AJAX)
        formCuota.on('submit', function (event) {
            event.preventDefault(); // Detener el envío normal del formulario

            const formData = $(this).serialize(); // Serializar los datos del formulario
            const actionUrl = $(this).attr('action');
            const isEdit = $(this).find('input[name="id"]').length > 0; // Verificar si es edición

            // Validaciones básicas del lado del cliente
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
                        // Recargar la lista de cuotas después de una operación exitosa
                        loadCuotasList(tipoOfertaAcademicaId, ofertaAcademicaId);

                        // Si es una nueva creación, podrías querer limpiar el formulario o redirigir
                        if (!isEdit) {
                            formCuota[0].reset(); // Limpiar el formulario
                            // Opcional: redirigir a la edición de la cuota recién creada si se devuelve el ID
                            // if (response.data && response.data.id) {
                            //     window.location.href = `${BASE_URL_JS}cuota/edit/${response.data.id}`;
                            // }
                        }
                    } else {
                        showAlert('Error: ' + response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al enviar el formulario:', error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    showAlert('Error al procesar la solicitud. Por favor, intente de nuevo.', 'error');
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

        // Manejador para el botón de eliminar cuota (delegado)
        $(document).on('click', '.delete-cuota-btn', function (e) {
            e.preventDefault();
            const cuotaId = $(this).data('id');
            if (confirm('¿Está seguro de que desea eliminar esta cuota?')) {
                $.ajax({
                    url: `${BASE_URL_JS}cuota/delete/${cuotaId}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            const currentTipoOfertaId = tipoOfertaAcademicaIdInput.val();
                            const currentOfertaId = ofertaAcademicaSelect.val();
                            loadCuotasList(currentTipoOfertaId, currentOfertaId);
                        } else {
                            showAlert('Error al eliminar: ' + response.message, 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al eliminar la cuota:', error);
                        console.error('Respuesta del servidor:', xhr.responseText);
                        showAlert('Error al intentar eliminar la cuota.', 'error');
                    }
                });
            }
        });

        // --- Lógica del Modal de Generación de Deuda ---

        // Abrir modal y cargar alumnos
        // Se adjunta el evento directamente a la tabla cuotasListTable
        cuotasListTable.on('click', '.generate-debt-btn', function () {
            console.log('Botón Generar Deuda clickeado en la tabla de cuotas.'); // Log para depuración
            const cuotaId = $(this).data('id');
            const montoCuota = $(this).data('monto');
            const tipoOfertaId = $(this).data('tipo-oferta-id');
            const ofertaId = $(this).data('oferta-id');

            loadStudentsForDebtGeneration(cuotaId, tipoOfertaId, ofertaId, montoCuota);
        });

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

            if (!currentCuotaIdForDebt || !currentCuotaMontoForDebt || !currentTipoOfertaIdForDebt || !currentOfertaIdForDebt) {
                showAlert('Error: No se pudo obtener la información de la cuota para generar la deuda.', 'error');
                return;
            }

            // Deshabilitar botón para evitar múltiples envíos
            confirmGenerateDebtBtn.prop('disabled', true).text('Generando Deuda...');

            $.ajax({
                url: `${BASE_URL_JS}cuota/generateDebt`,
                type: 'POST',
                data: {
                    cuota_id: currentCuotaIdForDebt,
                    alumno_ids: selectedAlumnoIds,
                    monto_cuota: currentCuotaMontoForDebt,
                    // También puedes enviar tipo_oferta_id y oferta_academica_id si es necesario en el backend
                    tipo_oferta_id: currentTipoOfertaIdForDebt,
                    oferta_id: currentOfertaIdForDebt
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        generateDebtModal.addClass('hidden'); // Cerrar modal
                        // Recargar la lista de cuotas para actualizar el estado 'generado'
                        loadCuotasList(tipoOfertaAcademicaIdInput.val(), ofertaAcademicaSelect.val());
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
