// app/Modules/PreinscripcionDiplomado/Views/js/preinscripcion_diplomado.js
console.log('preinscripcion_diplomado.js cargado.');

$(document).ready(function () {
    const searchAlumnoForm = $('#searchAlumnoForm');
    const alumnoDetailsSection = $('#alumnoDetails');
    const createAlumnoForm = $('#createAlumnoForm');
    const diplomadosAbiertosSection = $('#diplomadosAbiertosSection');
    const diplomadosAbiertosList = $('#diplomadosAbiertosList');
    const preinscribirBtn = $('#preinscribirBtn');
    const selectedAlumnoIdInput = $('#selectedAlumnoId'); // Campo oculto para el ID del alumno
    const selectedDiplomadoAbiertoIdInput = $('#selectedDiplomadoAbiertoId'); // Campo oculto para el ID del diplomado abierto

    let currentAlumnoId = null; // Almacena el ID del alumno seleccionado/creado

    // --- Funciones de Visibilidad ---
    function showAlumnoSearch() {
        searchAlumnoForm.show();
        alumnoDetailsSection.hide();
        createAlumnoForm.hide();
        diplomadosAbiertosSection.hide();
        $('#ci_pasaporte_search').val('');
        $('#search_result_message').text('');
        currentAlumnoId = null;
        selectedAlumnoIdInput.val('');
    }

    function showAlumnoDetails(alumno) {
        alumnoDetailsSection.show();
        $('#alumno_ci_pasaporte').text(alumno.ci_pasaporte);
        $('#alumno_nombre_completo').text(`${alumno.primer_nombre} ${alumno.segundo_nombre || ''} ${alumno.primer_apellido} ${alumno.segundo_apellido || ''}`);
        $('#alumno_correo').text(alumno.correo || 'N/A');
        $('#alumno_celular').text(alumno.tlf_celular || 'N/A');
        currentAlumnoId = alumno.id;
        selectedAlumnoIdInput.val(alumno.id);
        searchAlumnoForm.hide();
        createAlumnoForm.hide();
        loadDiplomadosAbiertos(); // Cargar diplomados una vez que el alumno está listo
        diplomadosAbiertosSection.show();
    }

    function showCreateAlumnoForm(ciPasapote = '') {
        createAlumnoForm.show();
        $('#new_ci_pasaporte').val(ciPasapote);
        searchAlumnoForm.hide();
        alumnoDetailsSection.hide();
        diplomadosAbiertosSection.hide();
        currentAlumnoId = null;
        selectedAlumnoIdInput.val('');
    }

    // --- Búsqueda de Alumno ---
    searchAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const ciPasapote = $('#ci_pasaporte_search').val().trim();
        if (ciPasapote === '') {
            $('#search_result_message').text('Por favor, ingrese un CI/Pasaporte.').removeClass('text-green-600').addClass('text-red-600');
            return;
        }

        $('#search_result_message').text('Buscando alumno...').removeClass('text-red-600').addClass('text-blue-600');

        $.ajax({
            url: `${BASE_URL_JS}preinscripcion_diplomado/search_alumno`,
            method: 'POST',
            data: { ci_pasaporte: ciPasapote, csrf_token: CSRF_TOKEN },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (response.found) {
                        $('#search_result_message').text('Alumno encontrado.').removeClass('text-red-600').addClass('text-green-600');
                        showAlumnoDetails(response.alumno);
                    } else {
                        $('#search_result_message').text(response.message).removeClass('text-green-600').addClass('text-red-600');
                        showCreateAlumnoForm(ciPasapote); // Mostrar formulario para crear
                    }
                } else {
                    $('#search_result_message').text(response.message || 'Error al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al buscar alumno:', status, error, xhr.responseText);
                $('#search_result_message').text('Error de conexión al buscar alumno.').removeClass('text-green-600').addClass('text-red-600');
            }
        });
    });

    // --- Creación de Alumno ---
    createAlumnoForm.on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize(); // Serializa todos los campos del formulario

        $.ajax({
            url: `${BASE_URL_JS}preinscripcion_diplomado/create_alumno`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2500,
                        showConfirmButton: false
                    });
                    showAlumnoDetails(response.alumno);
                    createAlumnoForm.hide();

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al crear alumno.'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al crear alumno:', status, error, xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar al servidor al intentar crear el alumno.'
                });
            }
        });
    });

    $('#cancelCreateAlumnoBtn').on('click', function () {
        showAlumnoSearch(); // Volver a la búsqueda
    });

    // --- Funcionalidad del botón 'Cambiar Alumno' ---
    $('#changeAlumnoBtn').on('click', function () {
        showAlumnoSearch(); // Vuelve a la sección de búsqueda de alumno
    });

    // --- Cargar Diplomados Abiertos ---
    function loadDiplomadosAbiertos() {
        diplomadosAbiertosList.empty().append('<p class="text-gray-500">Cargando diplomados abiertos...</p>');
        preinscribirBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

        $.ajax({
            url: `${BASE_URL_JS}preinscripcion_diplomado/get_diplomados_abiertos`,
            method: 'POST',
            data: { csrf_token: CSRF_TOKEN },
            dataType: 'json',
            success: function (response) {
                diplomadosAbiertosList.empty();
                if (response.success && response.data.length > 0) {
                    response.data.forEach(diplomado => {
                        const item = `
                            <div class="border rounded-lg p-4 mb-2 cursor-pointer hover:bg-blue-50 transition-colors duration-200" data-id="${diplomado.id}">
                                <h4 class="font-semibold text-lg text-blue-700">${diplomado.numero} - ${diplomado.diplomado_nombre}</h4>
                                <p class="text-gray-600 text-sm">Sede: ${diplomado.sede_nombre}</p>
                                <p class="text-gray-600 text-sm">Fechas: ${diplomado.fecha_inicio} al ${diplomado.fecha_fin}</p>
                                <p class="text-gray-500 text-xs">${diplomado.nombre_carta_truncated}</p>
                            </div>
                        `;
                        diplomadosAbiertosList.append(item);
                    });
                } else {
                    diplomadosAbiertosList.append('<p class="text-gray-500">No hay diplomados abiertos disponibles.</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX al cargar diplomados abiertos:', status, error, xhr.responseText);
                diplomadosAbiertosList.empty().append('<p class="text-red-600">Error al cargar diplomados abiertos.</p>');
            }
        });
    }

    // --- Selección de Diplomado Abierto ---
    diplomadosAbiertosList.on('click', 'div', function () {
        diplomadosAbiertosList.find('div').removeClass('border-blue-500 bg-blue-100').addClass('border-gray-200');
        $(this).addClass('border-blue-500 bg-blue-100');
        selectedDiplomadoAbiertoIdInput.val($(this).data('id'));
        preinscribirBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    });

    // --- Proceso de Pre-inscripción ---
    preinscribirBtn.on('click', function () {
        const alumnoId = selectedAlumnoIdInput.val();
        const diplomadoAbiertoId = selectedDiplomadoAbiertoIdInput.val();

        if (!alumnoId || !diplomadoAbiertoId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor, seleccione un alumno y un diplomado abierto.'
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Confirmar Pre-inscripción?',
            text: '¿Estás seguro de que quieres pre-inscribir a este alumno en el diplomado seleccionado?',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, Pre-inscribir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${BASE_URL_JS}preinscripcion_diplomado/create`, // Envía al mismo endpoint POST de creación
                    method: 'POST',
                    data: {
                        alumno_id: alumnoId,
                        diplomado_abierto_id: diplomadoAbiertoId,
                        csrf_token: CSRF_TOKEN
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Pre-inscripción Exitosa!',
                                text: response.message,
                                timer: 2500,
                                showConfirmButton: false
                            }).then(() => {
                                showAlumnoSearch(); // Reiniciar el formulario después de la pre-inscripción exitosa
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error AJAX al pre-inscribir:', status, error, xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar al servidor al realizar la pre-inscripción.'
                        });
                    }
                });
            }
        });
    });

    // --- Inicialización al cargar la página ---
    showAlumnoSearch(); // Mostrar la sección de búsqueda de alumno al inicio
});
